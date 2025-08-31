<?php

namespace GeoKrety\Service;

use GeoKrety\Model\RateLimitOverride;

/**
 * Resolve effective [limit, period] from GK_RATE_LIMITS_DEFAULT + RATE_LIMIT_LEVEL_MULTIPLIER per user tier.
 */
final class RateLimitPolicy {
    private const USER_LEVEL_CACHE_TTL = 300; // seconds
    private const USER_LEVEL_CACHE_KEY = 'rl:user-level:%d';

    /**
     * Return [limit, period] for a limiter and a (possibly null) user id.
     *
     * @param string   $limitName e.g. 'API_V1_EXPORT'
     * @param int|null $userId    null => anonymous
     *
     * @return array{0:int,1:int,2:string} [limit, period, tier]
     */
    public static function resolve(string $limitName, ?int $userId): array {
        [$baseLimit, $period] = \GK_RATE_LIMITS_DEFAULT[$limitName] ?? [60, 60];

        // Determine tier and multiplier
        $tier = self::getUserTier($userId);
        $multiplier = \RATE_LIMIT_LEVEL_MULTIPLIER[$tier];

        // Apply multiplier to the limit only; period stays unchanged
        $effectiveLimit = (int) floor($baseLimit * $multiplier);

        return [$effectiveLimit, $period, $tier];
    }

    public static function getUserTier(?int $userId): string {
        $level = self::getUserLevel($userId);

        return RATE_LIMIT_LEVEL_TO_TIER[$level] ?? RATE_LIMIT_LEVEL_ANONYMOUS;
    }

    /**
     * Get active numeric level for a user (0 for anonymous), with a small Redis cache.
     */
    private static function getUserLevel(?int $userId): int {
        if ($userId === null) {
            return 0;
        }

        $cacheKey = \sprintf(self::USER_LEVEL_CACHE_KEY, $userId);
        try {
            $client = Redis::instance()->getRedis();
            $cached = $client->get($cacheKey);
            if ($cached !== false && !is_null($cached)) {
                return (int) $cached;
            }
        } catch (\Throwable) {
            // ignore cache errors
        }

        // Source of truth: your overrides table/model (returns an int level)
        $level = RateLimitOverride::activeLevelForUser($userId);

        // Best-effort cache
        try {
            if (isset($client)) {
                $client->setex($cacheKey, self::USER_LEVEL_CACHE_TTL, (string) $level);
            }
        } catch (\Throwable) {
            // ignore
        }

        return (int) $level;
    }

    public static function purgeAllUserLevelCache(): void {
        $redis = Redis::instance();
        $redis->ensureOpenConnection();
        $client = $redis->getRedis();

        $it = null;
        do {
            $keys = $client->scan($it, 'rl:user-level:*', 1000);
            if ($keys === false || empty($keys)) {
                continue;
            }

            try {
                if (method_exists($client, 'unlink')) {
                    $client->unlink($keys);
                } else {
                    $client->del($keys);
                }
            } catch (\Throwable $e) {
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
