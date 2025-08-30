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
                'limit' => null,
                'period' => null,
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

    private function get_rate_limit_key($key): string {
        if (is_null($key)) {
            return \Base::instance()->get('IP');
        }

        return $key;
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
     * @throws StorageException
     */
    public static function get_rates_limits_usage(string $query = '*'): array {
        /** @var Redis $redis */
        $redis = Redis::instance();
        $redis->ensureOpenConnection();
        $allKeys = $redis->keys(sprintf('%s__%s', self::RATE_KEY, $query));
        $response = [];
        foreach ($allKeys as $key) {
            if (preg_match('/^'.self::RATE_KEY.'__(.*)__:(.*):allow$/', $key, $matches) === 0) {
                continue;
            }
            $adapter = new RedisAdapter($redis->getRedis());
            $key = self::RATE_KEY."__{$matches[1]}__";
            $rateLimit = new RateLimiter($key, GK_RATE_LIMITS[$matches[1]][0], GK_RATE_LIMITS[$matches[1]][1], $adapter);
            $response[$matches[1]][$matches[2]] = GK_RATE_LIMITS[$matches[1]][0] - $rateLimit->getAllowance($matches[2]);
        }

        return $response;
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
     * @throws StorageException
     */
    public static function purge(): void {
        $query = '*';
        /** @var Redis $redis */
        $redis = Redis::instance();
        $redis->ensureOpenConnection();
        $allKeys = $redis->keys(sprintf('%s__%s', self::RATE_KEY, $query));
        foreach ($allKeys as $key) {
            if (preg_match('/^'.self::RATE_KEY.'__(.*)__:(.*):allow$/', $key, $matches) === 0) {
                continue;
            }
            $adapter = new RedisAdapter($redis->getRedis());
            $key = self::RATE_KEY."__{$matches[1]}__";
            $rateLimit = new RateLimiter($key, GK_RATE_LIMITS[$matches[1]][0], GK_RATE_LIMITS[$matches[1]][1], $adapter);
            if (GK_RATE_LIMITS[$matches[1]][0] <= $rateLimit->getAllowance($matches[2])) {
                $rateLimit->purge($matches[2]);
            }
        }
    }
}
