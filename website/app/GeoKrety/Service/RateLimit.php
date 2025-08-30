<?php

namespace GeoKrety\Service;

use GeoKrety\Service\Xml\Error;
use PalePurple\RateLimit\Adapter\Redis as RedisAdapter;
use PalePurple\RateLimit\RateLimit as RateLimiter;
use Sugar\Event;

/**
 * Exception thrown if a rate limit is reached.
 */
class RateLimitExceeded extends \Exception {
}

class RateLimit {
    private const RATE_KEY_PATTERN = '%s__%s__';
    private const RATE_KEY = 'RATE_LIMIT_API';
    private const SECID_UID_CACHE_TTL = 300;
    private const SECID_UID_CACHE_PREFIX = 'rl:secid2uid:';

    private string $name;
    private string $counting_key;
    private ?int $user_id;
    private RateLimiter $rate_limiter;

    private int $effective_limit = 0;
    private int $effective_period = 0;

    /**
     * Count requests and report error as simple string.
     *
     * @param string          $name Limit name
     * @param int|string|null $key  User identifier (userId|secid|IP|null)
     */
    public static function check_rate_limit_raw(string $name, int|string|null $key = null): void {
        try {
            self::incr($name, $key);
        } catch (RateLimitExceeded $e) {
            echo _('Rate limit exceeded');
            exit;
        }
    }

    /**
     * Count requests and report error as GKXML Error.
     *
     * @param string          $name Limit name
     * @param int|string|null $key  User identifier (userId|secid|IP|null)
     */
    public static function check_rate_limit_xml(string $name, int|string|null $key = null): void {
        try {
            self::incr($name, $key);
        } catch (RateLimitExceeded $e) {
            Error::buildError(false, [_('Rate limit exceeded')]);
            exit;
        }
    }

    /**
     * @param string          $name Limit name
     * @param int|string|null $key  User identifier (userId|secid|IP|null)
     *
     * @throws RateLimitExceeded
     */
    public static function incr(string $name, int|string|null $key = null): void {
        // Allow bypass the rate limiter
        if (self::allow_bypass()) {
            return;
        }

        // Skip rate limit if Redis is not available
        try {
            $rateLimit = new RateLimit($name, $key);
        } catch (StorageException $e) {
            [$limit, $period] = \GK_RATE_LIMITS_DEFAULT[$name] ?? [null, null];
            Event::instance()->emit('rate-limit.skip', [
                'name' => $name,
                'limit' => $limit,
                'period' => $period,
            ]);

            return;
        }

        $rateLimit->check();
    }

    /**
     * @throws StorageException
     */
    public function __construct(string $name, int|string|null $key = null) {
        $this->name = $name;
        $this->user_id = self::inferUserId($key);
        $this->counting_key = self::deriveCountingKey($key);

        // Resolve plan by user level
        [$limit, $period] = RateLimitPolicy::resolve($name, $this->user_id);
        $this->effective_limit = (int) $limit;
        $this->effective_period = (int) $period;

        $adapter = new RedisAdapter(Redis::instance()->getRedis());
        $this->rate_limiter = new RateLimiter(
            $this->get_rate_limit_key_base(),
            $this->effective_limit,
            $this->effective_period,
            $adapter
        );
    }

    public static function normalizeKey(string $k): string {
        return strtr($k, [':' => '_']);
    }

    private static function secidCacheKey(string $secid): string {
        return self::SECID_UID_CACHE_PREFIX.self::normalizeKey($secid);
    }

    private static function redisClientOrNull() {
        try {
            return Redis::instance()->getRedis();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function getUserIdFromCache(string $secid): ?int {
        $redis = self::redisClientOrNull();
        if (!$redis) {
            return null;
        }
        $cached = $redis->get(self::secidCacheKey($secid));

        return ($cached !== false && $cached !== null) ? (int) $cached : null;
    }

    private static function getUserIdFromDb(string $secid): ?int {
        try {
            $userModel = new \GeoKrety\Model\User();
            $userModel->load(['_secid_hash = public.digest(?, \'sha256\')', $secid]);

            return $userModel->dry() ? null : (int) $userModel->id;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function cacheUserId(string $secid, int $uid): void {
        $redis = self::redisClientOrNull();
        if ($redis) {
            $redis->setex(self::secidCacheKey($secid), self::SECID_UID_CACHE_TTL, (string) $uid);
        }
    }

    /**
     * Resolve user id from $key:
     * - int           => user id
     * - 128-char str  => secid -> lookup cached -> DB
     * - else          => null (anonymous/IP-only).
     */
    public static function inferUserId($key): ?int {
        if (\is_int($key)) {
            return $key;
        }
        if (!\is_string($key)) {
            return null;
        }

        $secid = \trim($key);
        if (\strlen($secid) !== (int) \GK_SITE_SECID_CODE_LENGTH) {
            return null;
        }

        $uid = self::getUserIdFromCache($secid);
        if ($uid !== null) {
            return $uid;
        }

        $uid = self::getUserIdFromDb($secid);
        if ($uid !== null) {
            self::cacheUserId($secid, $uid);
        }

        return $uid;
    }

    /**
     * What key do we count against in Redis?
     * - null        => use IP
     * - int userId  => "uid_<id>" (distinct namespace)
     * - string      => normalize as-is (secid or custom string).
     */
    private static function deriveCountingKey($key): string {
        $ip = \Base::instance()->get('IP') ?: 'cli';
        if ($key === null) {
            return self::normalizeKey($ip);
        }
        if (\is_int($key)) {
            return 'uid_'.(string) $key;
        }
        if (\is_string($key) && $key !== '') {
            return self::normalizeKey($key);
        }

        return self::normalizeKey($ip);
    }

    /**
     * @throws RateLimitExceeded
     */
    public function check(): void {
        if (!$this->rate_limiter->check($this->counting_key)) {
            Event::instance()->emit('rate-limit.exceeded', $this->get_context());
            register_shutdown_function('GeoKrety\Model\AuditPost::AmendAuditPostWithErrors', 'Rate limit exceeded');
            http_response_code(429);
            throw new RateLimitExceeded();
        }
        Event::instance()->emit('rate-limit.success', $this->get_context());
    }

    private function get_context(): array {
        $allow = (int) $this->rate_limiter->getAllowance($this->counting_key);

        return [
            'name' => $this->name,
            'key' => $this->counting_key,
            'user_id' => $this->user_id,
            'total_user_calls' => max(0, $this->effective_limit - $allow),
            'remaining_attempts' => $allow,
            'limit' => $this->effective_limit,
            'period' => $this->effective_period,
        ];
    }

    private function get_rate_limit_key_base(): string {
        return sprintf(self::RATE_KEY_PATTERN, self::RATE_KEY, $this->name);
    }

    private static function allow_bypass(): bool {
        $f3 = \Base::instance();

        return $f3->exists('GET.rate_limits_bypass')
            && $f3->get('GET.rate_limits_bypass') === GK_RATE_LIMITS_BYPASS;
    }

    /**
     * Return usage (tokens consumed) for one or more identities across all (or some) limits.
     * Never scans Redis; directly asks the limiter for allowance.
     *
     * @param string[]      $rawKeys    e.g. ['192.168.0.10', 'secid123']
     * @param string[]|null $limitNames null => all limits in GK_RATE_LIMITS_DEFAULT
     *
     * @return array{string: array{string:int}} [limitName => [normKey => used]]
     *
     * @throws StorageException
     */
    public static function get_usage_for_identities(array $rawKeys, ?array $limitNames = null): array {
        if (empty($rawKeys)) {
            return [];
        }

        // normalize once (same rule as check())
        $normKeys = [];
        foreach ($rawKeys as $k) {
            $normKeys[] = strtr($k, [':' => '_']);
        }

        $redis = Redis::instance();
        $redis->ensureOpenConnection();
        $adapter = new RedisAdapter($redis->getRedis());

        $names = $limitNames ?? array_keys(GK_RATE_LIMITS_DEFAULT);
        $resp = [];

        foreach ($names as $name) {
            $cfg = GK_RATE_LIMITS_DEFAULT[$name] ?? null;
            if (!is_array($cfg) || count($cfg) < 2) {
                continue;
            }
            [$limit, $period] = $cfg;

            $base = sprintf('%s__%s__', self::RATE_KEY, $name);
            $rl = new RateLimiter($base, (int) $limit, (int) $period, $adapter);

            foreach ($normKeys as $nk) {
                $allow = (int) $rl->getAllowance($nk);
                $resp[$name][$nk] = max(0, (int) $limit - $allow);
            }
        }

        return $resp;
    }

    /**
     * @throws StorageException
     */
    public static function get_rates_limits_usage(string $query = '*'): array {
        $redis = Redis::instance();
        $redis->ensureOpenConnection();

        $client = $redis->getRedis();
        $pattern = sprintf('%s__%s', self::RATE_KEY, $query);
        $adapter = new RedisAdapter($client);
        $limiters = [];
        $resp = [];
        $it = null;

        do {
            $keys = $client->scan($it, $pattern, 1000);
            if ($keys === false) {
                continue;
            }

            foreach ($keys as $full) {
                if (!preg_match('/^'.preg_quote(self::RATE_KEY, '/').'__(.+?)___(.*)_allow$/', $full, $m)) {
                    continue;
                }
                [, $limitName, $userKey] = $m;

                $cfg = GK_RATE_LIMITS_DEFAULT[$limitName] ?? null;
                if (!is_array($cfg) || count($cfg) < 2) {
                    continue;
                }
                [$limit, $period] = $cfg;

                if (!isset($limiters[$limitName])) {
                    $base = self::RATE_KEY."__{$limitName}__";
                    $limiters[$limitName] = new RateLimiter($base, (int) $limit, (int) $period, $adapter);
                }

                $allow = (int) $limiters[$limitName]->getAllowance($userKey);
                $resp[$limitName][$userKey] = max(0, (int) $limit - $allow);
            }
        } while ($it !== 0);

        return $resp;
    }

    public function reset(): void {
        $this->rate_limiter->purge($this->counting_key);
    }

    public static function resetAll(): void {
        $redis = Redis::instance();
        $redis->ensureOpenConnection();
        $allKeys = $redis->keys(sprintf('%s__*', self::RATE_KEY));
        foreach ($allKeys as $key) {
            $redis->del($key);
        }
    }

    /**
     * Purge idle rate-limit keys (those with full allowance).
     *
     * @throws StorageException
     */
    public static function purge(): void {
        $redis = Redis::instance();
        $redis->ensureOpenConnection();
        $client = $redis->getRedis();
        $adapter = new RedisAdapter($client);

        $iterator = null;
        $pattern = sprintf('%s__*', self::RATE_KEY);
        $regex = '/^'.preg_quote(self::RATE_KEY, '/').'__(.+?)___(.*)_allow$/';
        $limitersByName = [];

        do {
            $keys = $client->scan($iterator, $pattern, 1000);
            if ($keys === false) {
                continue;
            }

            foreach ($keys as $key) {
                if (!preg_match($regex, $key, $matches)) {
                    continue;
                }
                [, $limitName, $userKey] = $matches;

                $config = GK_RATE_LIMITS_DEFAULT[$limitName] ?? null;
                if (!is_array($config) || count($config) < 2) {
                    continue;
                }
                [$limit, $period] = $config;

                if (!isset($limitersByName[$limitName])) {
                    $baseKey = sprintf('%s__%s__', self::RATE_KEY, $limitName);
                    $limitersByName[$limitName] = new RateLimiter($baseKey, (int) $limit, (int) $period, $adapter);
                }

                $rateLimiter = $limitersByName[$limitName];

                // Purge idle bucket
                if ($rateLimiter->getAllowance($userKey) >= (int) $limit) {
                    $rateLimiter->purge($userKey);
                }

                // Always drop the user-level cache if the key is uid_<id>
                if (preg_match('/^uid_(\d+)$/', $userKey, $um)) {
                    try {
                        $client->del(RateLimitPolicy::cacheKeyForUserLevel((int) $um[1]));
                    } catch (\Throwable $e) {
                        // best-effort; ignore cache errors
                    }
                }
            }
        } while ($iterator !== 0);
        RateLimitPolicy::purgeAllUserLevelCache();
    }
}
