<?php

namespace GeoKrety\Service;

use Exception;
use GeoKrety\Service\Xml\Error;
use Prefab;
use Sugar\Event;

/**
 * Exception thrown if a rate limit is reached.
 */
class RateLimitExceeded extends Exception {
}

class RateLimit extends Prefab {
    private const RATE_KEY = 'RATE_LIMIT_API';

    /**
     * Count requests and report error as simple string.
     *
     * @param string      $name Limit name
     * @param string|null $key  User identifier
     *
     * @return void
     */
    public static function check_rate_limit_raw(string $name, ?string $key = null) {
        try {
            self::incr($name, $key);
        } catch (RateLimitExceeded $e) {
            register_shutdown_function('GeoKrety\Model\AuditPost::AmendAuditPostWithErrors', 'Rate limit exceeded');
            echo _('Rate limit exceeded');
            http_response_code(429);
            exit();
        }
    }

    /**
     * Count requests and report error as GKXML Error.
     *
     * @param string      $name Limit name
     * @param string|null $key  User identifier
     *
     * @return void
     */
    public static function check_rate_limit_xml(string $name, ?string $key = null) {
        try {
            self::incr($name, $key);
        } catch (RateLimitExceeded $e) {
            register_shutdown_function('GeoKrety\Model\AuditPost::AmendAuditPostWithErrors', 'Rate limit exceeded');
            Error::buildError(false, [_('Rate limit exceeded')]);
            http_response_code(429);
            exit();
        }
    }

    /**
     * @param string      $name Limit name
     * @param string|null $key  User identifier
     */
    public static function reset(string $name, string $key) {
        $redis = Redis::instance();
        $redis->ensureOpenConnection();
        $key = sprintf('%s__%s__%s', self::RATE_KEY, $name, $key);
        $redis->del($key);
    }

    public static function resetAll() {
        $redis = Redis::instance();
        $redis->ensureOpenConnection();
        $allKeys = $redis->keys(sprintf('%s__*', self::RATE_KEY));
        foreach ($allKeys as $key) {
            $redis->del($key);
        }
    }

    /**
     * @param string      $name Limit name
     * @param string|null $key  User identifier
     *
     * @throws \GeoKrety\Service\RateLimitExceeded
     */
    public static function incr(string $name, ?string $key = null) {
        $f3 = \Base::instance();
        if ($f3->exists('GET.rate_limits_bypass') && $f3->get('GET.rate_limits_bypass') === GK_RATE_LIMITS_BYPASS) {
            return;
        }
        /** @var \GeoKrety\Service\Redis $redis */
        $redis = Redis::instance();
        try {
            $redis->ensureOpenConnection();
        } catch (StorageException $e) {
            // Let users pass if redis is failing
            // TODO log error, notify admin?
            Event::instance()->emit('rate-limit.skip', [
                'name' => $name,
                'total_user_calls' => '?',
                'limit' => GK_RATE_LIMITS[$name][0],
                'period' => GK_RATE_LIMITS[$name][1],
                ]);

            return;
        }

        $rate_key = sprintf('%s__%s__', self::RATE_KEY, $name);
        if (!is_null($key)) {
            $rate_key .= $key;
        } else {
            $rate_key .= \Base::instance()->get('IP');
        }
        $total_user_calls = 1;
        if (!$redis->exists($rate_key)) {
            $redis->set($rate_key, 1);
            $redis->expire($rate_key, GK_RATE_LIMITS[$name][1]);
        } else {
            $total_user_calls = $redis->get($rate_key);
            if ($total_user_calls >= GK_RATE_LIMITS[$name][0]) {
                Event::instance()->emit('rate-limit.exceeded', [
                    'name' => $name,
                    'total_user_calls' => $total_user_calls,
                    'limit' => GK_RATE_LIMITS[$name][0],
                    'period' => GK_RATE_LIMITS[$name][1],
                    ]);
                throw new RateLimitExceeded();
            }
            $redis->incr($rate_key);
        }
        Event::instance()->emit('rate-limit.success', [
            'name' => $name,
            'total_user_calls' => $total_user_calls,
            'limit' => GK_RATE_LIMITS[$name][0],
            'period' => GK_RATE_LIMITS[$name][1],
            ]);
    }

    /**
     * @throws \GeoKrety\Service\StorageException
     */
    public static function get_rates_limits_usage(string $query = '*'): array {
        /** @var \GeoKrety\Service\Redis $redis */
        $redis = Redis::instance();
        $redis->ensureOpenConnection();
        $allKeys = $redis->keys(sprintf('%s__%s', self::RATE_KEY, $query));
        $response = [];
        foreach ($allKeys as $key) {
            $val = $redis->get($key);
            if (preg_match('/^'.self::RATE_KEY.'__(.*)__(.*)$/', $key, $matches) === false or !array_key_exists($matches[1], GK_RATE_LIMITS)) {
                $redis->del($key);
                continue;
            }
            $response[$matches[1]][$matches[2]] = $val;
        }

        return $response;
    }
}
