<?php

namespace GeoKrety\Service;

use GeoKrety\Model\User;
use GeoKrety\Service\Xml\Error;
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
    private const PREFIX_UID = 'uid_';
    private const PREFIX_IP = 'ip_';
    public const KEY_REGEX = '/^(?:uid_\d+|ip_.*)$/';

    private string $name;
    private int $effective_limit;
    private int $effective_period;
    private string $counting_key;
    private ?int $user_id;
    private RateLimiter $rate_limiter;

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
     * Count requests and, on limit, emit an image error (PNG|SVG) with 429.
     *
     * @param string          $name Limit name
     * @param int|string|null $key  Identifier (userId|secid|IP|null)
     */
    public static function check_rate_limit_image(string $name, int|string|null $key = null): void {
        try {
            self::incr($name, $key);
        } catch (RateLimitExceeded $e) {
            header('Content-Type: image/png');
            echo self::renderRateLimitImage();
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

    private static function allow_bypass(): bool {
        $f3 = \Base::instance();

        return $f3->exists('GET.rate_limits_bypass')
            && $f3->get('GET.rate_limits_bypass') === GK_RATE_LIMITS_BYPASS;
    }

    /**
     * @throws RateLimitExceeded
     */
    public function check(): void {
        if (!$this->rate_limiter->check($this->counting_key)) {
            Event::instance()->emit('rate-limit.exceeded', $this->get_context());
            register_shutdown_function('GeoKrety\Model\AuditPost::AmendAuditPostWithErrors', 'Rate limit exceeded');
            http_response_code(429);
            header('Cache-Control: private, no-store, must-revalidate');
            throw new RateLimitExceeded();
        }
        Event::instance()->emit('rate-limit.success', $this->get_context());
    }

    /**
     * @throws StorageException
     */
    public function __construct(string $name, int|string|null $key = null) {
        $this->name = $name;
        $this->user_id = null;
        $this->counting_key = $key ?? '';
        if (!\preg_match(self::KEY_REGEX, $key ?? '')) {
            $this->user_id = self::inferUserId($key);
            $this->counting_key = self::deriveCountingKey($this->user_id);
        }

        // Resolve plan by user level
        [$this->effective_limit, $this->effective_period] = RateLimitPolicy::resolve($this->name, $this->user_id);

        $this->rate_limiter = new RateLimiter(
            self::get_rate_limit_key_base($this->name),
            $this->effective_limit,
            $this->effective_period,
            RedisAdapter::instance()->getAdapter(),
        );
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

        $user = User::get_user_by_secid($key);
        if ($user) {
            return $user->id;
        }

        return null;
    }

    /**
     * What key do we count against in Redis?
     * - null        => use IP
     * - int userId  => "uid_<id>" (distinct namespace).
     */
    private static function deriveCountingKey(int|string|null $user_id): string {
        if (!is_null($user_id) && \preg_match(self::KEY_REGEX, $user_id ?? '')) {
            return $user_id;
        }
        $prefix = self::PREFIX_IP;
        $key = \Base::instance()->get('IP') ?: 'cli';
        if (!is_null($user_id) && is_int($user_id)) {
            $prefix = self::PREFIX_UID;
            $key = (string) $user_id;
        }

        return $prefix.$key;
    }

    private static function get_rate_limit_key_base(string $name): string {
        return sprintf(self::RATE_KEY_PATTERN, self::RATE_KEY, $name);
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
        foreach ($rawKeys as $key) {
            $normKey = self::deriveCountingKey($key);
            if ($normKey !== null) {
                $normKeys[] = $normKey;
            }
        }

        $adapter = RedisAdapter::instance()->getAdapter();
        $limitNames = $limitNames ?? array_keys(GK_RATE_LIMITS_DEFAULT);
        $resp = [];

        foreach ($limitNames as $limitName) {
            $base = self::get_rate_limit_key_base($limitName);

            foreach ($normKeys as $normKey) {
                $userId = null;
                if (preg_match('/^uid_(\d+)$/', $normKey, $um) !== 0) {
                    $userId = $um[1];
                }
                [$limit, $period, $tier] = RateLimitPolicy::resolve($limitName, $userId);
                $rateLimiter = new RateLimiter($base, $limit, $period, $adapter);
                $allowance = $rateLimiter->getAllowance($normKey);
                $used = \max(0, $limit - $allowance);
                if ($used === 0) {
                    $rateLimiter->purge($normKey);
                }
                $resp[$limitName][$normKey] = ['usage' => $used, 'limit' => $limit, 'tier' => $tier];
            }
        }

        return $resp;
    }

    /**
     * @throws StorageException
     */
    public static function get_rates_limits_usage_detailed(string $query = '*'): array {
        $pattern = self::get_rate_limit_key_base($query).'*_allow';
        $redis = Redis::instance()->getRedis();
        $adapter = RedisAdapter::instance()->getAdapter();

        // Reuse limiters by [limitName][effectiveLimit] to avoid re-instantiation
        $resp = [];
        $it = null;

        do {
            $keys = $redis->scan($it, $pattern, 1000);
            if ($keys === false) {
                continue;
            }

            foreach ($keys as $key) {
                if (preg_match('/^'.self::RATE_KEY.'__(.*)___(.*)_allow$/', $key, $matches) === 0) {
                    continue;
                }
                [, $limitName, $userKey] = $matches;
                $userId = null;
                if (preg_match('/^uid_(\d+)$/', $userKey, $um) !== 0) {
                    $userId = $um[1];
                }
                [$limit, $period, $tier] = RateLimitPolicy::resolve($limitName, $userId);

                $rateLimiter = new RateLimiter(
                    self::get_rate_limit_key_base($limitName),
                    $limit,
                    $period,
                    $adapter);

                $allowance = $rateLimiter->getAllowance($userKey);
                $used = \max(0, $limit - $allowance);
                if ($used === 0) {
                    $rateLimiter->purge($userKey);
                }
                if ($used < 2) {
                    continue;
                }

                $resp[$limitName][$userKey] = [
                    'used' => $used,
                    'left' => $allowance,
                    'limit' => $limit,
                    'period' => $period,
                    'tier' => $tier,
                ];
            }
        } while ($it !== 0);

        return $resp;
    }

    public function reset(): void {
        $this->rate_limiter->purge($this->counting_key);
    }

    public static function resetAll(): void {
        $redis = Redis::instance()->getRedis();
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
    public static function purge(string $query = '*'): array {
        $redis = Redis::instance()->getRedis();
        $adapter = RedisAdapter::instance()->getAdapter();
        $allKeys = $redis->keys(sprintf('%s__%s*_allow', self::RATE_KEY, $query));
        $response = [];
        foreach ($allKeys as $key) {
            if (preg_match('/^'.self::RATE_KEY.'__(.*)___(.*)_allow$/', $key, $matches) === 0) {
                continue;
            }
            [, $limitName, $userKey] = $matches;
            $userId = null;
            if (preg_match('/^uid_(\d+)$/', $userKey, $um) !== 0) {
                $userId = $um[1];
            }
            [$limit, $period] = RateLimitPolicy::resolve($limitName, $userId);

            $rateLimiter = new RateLimiter(
                self::get_rate_limit_key_base($limitName),
                $limit,
                $period,
                $adapter);

            $counting_key = self::deriveCountingKey($userKey);
            if ($rateLimiter->getAllowance($counting_key) >= $limit) {
                $rateLimiter->purge($userKey);
            }
        }

        return $response;
    }

    /**
     * Render a "rate limit exceeded" image (PNG or SVG).
     *
     * @return string Binary image (PNG) or UTF-8 string (SVG)
     */
    private static function renderRateLimitImage(): string {
        $docUrl = GK_SITE_BASE_SERVER_URL.'/help/api#apiratelimit';

        $title = _('Rate limit exceeded (HTTP 429)');

        $lines = [];
        $lines[] = _('You sent too many requests in a short time.');
        $lines[] = _('To protect the service for everyone, requests are temporarily limited.');
        $lines[] = _('Please slow down and try again shortly.');
        $lines[] = sprintf(_('More info: %s'), $docUrl);
        $lines[] = sprintf(_('Enjoying GeoKrety? Consider supporting us %s'), 'geokrety.org');

        $wrap = function (string $text, int $width) {
            // crude wrap for bitmap font (width≈8 px per char for font 3)
            $maxChars = max(10, (int) floor($width / 8));
            $out = [];
            foreach (explode("\n", $text) as $line) {
                $out = array_merge($out, explode("\n", wordwrap($line, $maxChars, "\n", true)));
            }

            return $out;
        };

        $w = 920;
        $pad = 16;
        $titleFont = 5;  // built-in GD font
        $bodyFont = 3;
        $lineH = 18;

        // Wrap body lines
        $wrapped = [];
        foreach ($lines as $line) {
            $wrapped = array_merge($wrapped, $wrap($line, $w - 2 * $pad));
        }

        $h = $pad + imagefontheight($titleFont) + $pad + (count($wrapped) * $lineH) + $pad;
        $im = imagecreatetruecolor($w, $h);
        $bg = imagecolorallocate($im, 247, 247, 247);
        $fg = imagecolorallocate($im, 51, 51, 51);
        $hi = imagecolorallocate($im, 176, 0, 32); // title red
        imagefilledrectangle($im, 0, 0, $w, $h, $bg);

        // Title
        $tx = $pad;
        $ty = $pad;
        imagestring($im, $titleFont, $tx, $ty, $title, $hi);

        // Body
        $y = $ty + imagefontheight($titleFont) + $pad;
        foreach ($wrapped as $line) {
            imagestring($im, $bodyFont, $pad, $y, $line, $fg);
            $y += $lineH;
        }

        ob_start();
        imagepng($im);
        imagedestroy($im);

        return (string) ob_get_clean();
    }
}
