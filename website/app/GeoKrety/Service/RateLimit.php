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

    private string $name;
    private ?string $key;
    private RateLimiter $rate_limiter;

    /**
     * Count requests and report error as simple string.
     *
     * @param string      $name Limit name
     * @param string|null $key  User identifier
     */
    public static function check_rate_limit_raw(string $name, ?string $key = null): void {
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
     * @param string      $name Limit name
     * @param string|null $key  User identifier
     */
    public static function check_rate_limit_xml(string $name, ?string $key = null): void {
        try {
            self::incr($name, $key);
        } catch (RateLimitExceeded $e) {
            Error::buildError(false, [_('Rate limit exceeded')]);
            exit;
        }
    }

    /**
     * @param string      $name Limit name
     * @param string|null $key  User identifier
     *
     * @throws RateLimitExceeded
     */
    public static function incr(string $name, ?string $key = null): void {
        // Allow bypass the rate limiter
        if (self::allow_bypass()) {
            return;
        }

        // Skip rate limit if Redis is not available
        try {
            $rateLimit = new RateLimit($name, $key);
        } catch (StorageException $e) {
            Event::instance()->emit('rate-limit.skip', [
                'name' => $name,
                'limit' => GK_RATE_LIMITS[$name][0] ?? null,
                'period' => GK_RATE_LIMITS[$name][1] ?? null,
            ]);

            return;
        }

        $rateLimit->check();
    }

    /**
     * @throws StorageException
     */
    public function __construct(string $name, ?string $key = null) {
        $this->name = $name;
        $this->key = $this->get_rate_limit_key($key);

        $adapter = new RedisAdapter(Redis::instance()->getRedis());

        $this->rate_limiter = new RateLimiter(
            $this->get_rate_limit_key_base(),
            $this->get_max_requests(),
            $this->get_period(),
            $adapter);
    }

    /**
     * @throws RateLimitExceeded
     */
    public function check(): void {
        if (!$this->rate_limiter->check($this->key)) {
            Event::instance()->emit('rate-limit.exceeded', $this->get_context());
            register_shutdown_function('GeoKrety\Model\AuditPost::AmendAuditPostWithErrors', 'Rate limit exceeded');
            http_response_code(429);
            throw new RateLimitExceeded();
        }
        Event::instance()->emit('rate-limit.success', $this->get_context());
    }

    private function get_context(): array {
        return [
            'name' => $this->name,
            'total_user_calls' => $this->get_max_requests() - $this->rate_limiter->getAllowance($this->key),
            'remaining_attempts' => $this->rate_limiter->getAllowance($this->key),
            'limit' => $this->get_max_requests(),
            'period' => $this->get_period(),
        ];
    }

    private function get_rate_limit_key_base(): string {
        return sprintf(self::RATE_KEY_PATTERN, self::RATE_KEY, $this->name);
    }

    private function get_rate_limit_key(?string $key): string {
        if (is_null($key)) {
            $key = \Base::instance()->get('IP');
        }

        return strtr($key, [':' => '_']);
    }

    private function get_max_requests(): int {
        return GK_RATE_LIMITS[$this->name][0];
    }

    private function get_period(): int {
        return GK_RATE_LIMITS[$this->name][1];
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
     * @param string[]|null $limitNames null => all limits in GK_RATE_LIMITS
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

        $names = $limitNames ?? array_keys(GK_RATE_LIMITS);
        $resp = [];

        foreach ($names as $name) {
            $cfg = GK_RATE_LIMITS[$name] ?? null;
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

                $cfg = GK_RATE_LIMITS[$limitName] ?? null;
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
        $this->rate_limiter->purge($this->key);
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

                $config = GK_RATE_LIMITS[$limitName] ?? null;
                if (!is_array($config) || count($config) < 2) {
                    continue;
                }
                [$limit, $period] = $config;

                if (!isset($limitersByName[$limitName])) {
                    $baseKey = sprintf('%s__%s__', self::RATE_KEY, $limitName);
                    $limitersByName[$limitName] = new RateLimiter($baseKey, (int) $limit, (int) $period, $adapter);
                }

                $rateLimiter = $limitersByName[$limitName];
                if ($rateLimiter->getAllowance($userKey) >= (int) $limit) {
                    $rateLimiter->purge($userKey);
                }
            }
        } while ($iterator !== 0);
    }
}
