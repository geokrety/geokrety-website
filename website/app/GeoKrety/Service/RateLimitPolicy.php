<?php

namespace GeoKrety\Service;

use GeoKrety\Model\RateLimitOverride;

/**
 * Resolve effective [limit, period] from GK_RATE_LIMITS_LEVELS by user level.
 * Level 0 is the default for anonymous users.
 */
final class RateLimitPolicy {
    private const USER_LEVEL_CACHE_TTL = 300; // seconds
    private const USER_LEVEL_CACHE_KEY = 'rl:user-level:%d';

    /**
     * Return [limit, period] for a limiter and a (possibly null) user id.
     *
     * @param string   $limitName e.g. 'API_V1_EXPORT'
     * @param int|null $userId    null => anonymous (level 0)
     *
     * @return array{0:int,1:int}
     */
    public static function resolve(string $limitName, ?int $userId): array {
        $level = self::getUserLevel($userId);

        // pick plan set for that level, fallback to defaults
        $plans = \GK_RATE_LIMITS_LEVELS[$level] ?? \GK_RATE_LIMITS_DEFAULT;

        return [(int) $plans[$limitName][0], (int) $plans[$limitName][1]];
    }

    /**
     * Get active level for a user (0 for anonymous), with a small Redis cache.
     */
    private static function getUserLevel(?int $userId): int {
        if ($userId === null) {
            return 0;
        }

        $cacheKey = \sprintf(self::USER_LEVEL_CACHE_KEY, $userId);
        try {
            $client = Redis::instance()->getRedis();
            $cached = $client->get($cacheKey);
            if ($cached !== false && $cached !== null) {
                return (int) $cached;
            }
        } catch (\Throwable) {
            // ignore cache errors
        }

        $level = RateLimitOverride::activeLevelForUser($userId);

        try {
            if (isset($client)) {
                $client->setex($cacheKey, self::USER_LEVEL_CACHE_TTL, (string) $level);
            }
        } catch (\Throwable) {
            // ignore
        }

        return $level;
    }

    public static function purgeAllUserLevelCache(): void {
        $redis = Redis::instance();
        $redis->ensureOpenConnection();
        $client = $redis->getRedis();

        $it = null;
        do {
            // SCAN cursor iteration
            $keys = $client->scan($it, 'rl:user-level:*', 1000);
            if ($keys === false || empty($keys)) {
                continue;
            }

            // best-effort, non-blocking if available
            try {
                if (method_exists($client, 'unlink')) {
                    $client->unlink($keys);
                } else {
                    $client->del($keys);
                }
            } catch (\Throwable $e) {
                // very old phpredis: fall back to per-key delete
                foreach ($keys as $k) {
                    try {
                        $client->del($k);
                    } catch (\Throwable) {
                    }
                }
            }
        } while ($it !== 0);
    }
}
