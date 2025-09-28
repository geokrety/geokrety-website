<?php

namespace GeoKrety\Service;

/**
 * Comprehensive Security Headers Service.
 *
 * Handles ALL security headers in one clean place:
 * - CSP with nonce support (moved from Assets)
 * - HSTS, X-Frame-Options, Permissions-Policy, etc.
 * - Environment-aware configuration
 * - Owns and generates nonce internally
 */
class SecurityHeaders extends \Prefab {
    private string $nonce;

    // CORS allowed origins
    private const ALLOWED_ORIGINS = [
        'http://www.geocaching.com',
        'https://www.geocaching.com',
    ];

    public function __construct() {
        // SecurityHeaders owns nonce generation - single source of truth
        $this->nonce = (new \Delatbabel\ApiSecurity\Generators\Nonce())->getNonce();
    }

    /**
     * Apply all security headers globally.
     */
    public function applyAll(): void {
        $this->applyCSP();
        $this->applySecurityHeaders();
    }

    /**
     * Get the nonce for use by Assets and templates.
     */
    public function getNonce(): string {
        return $this->nonce;
    }

    /**
     * Content Security Policy with nonce support
     * Simplified and unified (removed unnecessary inline-translation complexity).
     */
    private function applyCSP(): void {
        $isInlineTranslation = \Multilang::instance()->current === 'inline-translation';

        // Base CSP directives
        $csp = [
            sprintf('script-src \'nonce-%s\' \'strict-dynamic\'%s', $this->nonce, $isInlineTranslation ? ' \'unsafe-hashes\'' : ''),
            sprintf(
                'img-src \'self\' data: blob: %s %s https://www.gstatic.com/recaptcha/ https://tile.openstreetmap.org https://seccdn.libravatar.org/avatar/ https://cdn.geokrety.org%s',
                GK_CDN_SERVER_URL,
                GK_MINIO_SERVER_URL_EXTERNAL,
                $isInlineTranslation ? ' https://cdn.crowdin.com/jipt/images/ https://crowdin-static.downloads.crowdin.com/avatar/ https://crowdin-static.cf-downloads.crowdin.com/avatar/' : ''
            ),
            'frame-src https://www.google.com/ https://www.youtube.com/'.($isInlineTranslation ? ' https://crowdin.com/' : ''),
            sprintf('style-src \'self\' \'nonce-%s\'', $this->nonce),
            sprintf(
                'style-src-elem \'self\' \'unsafe-inline\' %s%s',
                GK_CDN_SERVER_URL,
                $isInlineTranslation ? ' https://cdn.crowdin.com/jipt/jipt.css https://fonts.googleapis.com/css' : ''
            ),
            'style-src-attr \'self\' \'unsafe-inline\'',
            sprintf(
                'connect-src \'self\' %s%s',
                GK_MINIO_SERVER_URL_EXTERNAL,
                $isInlineTranslation ? ' https://crowdin.com/api/v2/jipt/cookie https://crowdin.com/api/v2/jipt/project/geokrety https://crowdin.com/api/v2/jipt/project/geokrety/strings' : ''
            ),
        ];

        // Add script-src-attr for inline translation event handlers
        if ($isInlineTranslation) {
            $csp[] = "script-src-attr 'unsafe-inline' 'unsafe-hashes' 'sha256-47mKTaMaEn1L3m5DAz9muidMqw636xxw7EFAK/YnPdg='";
        }

        // Add reCAPTCHA support
        $recaptchaHosts = 'https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/';
        $workerSrc = "'self' blob: $recaptchaHosts";
        $childSrc = "'self' blob: $recaptchaHosts";

        // Add Crowdin worker support for inline translation
        if ($isInlineTranslation) {
            $workerSrc .= ' https://crowdin.com';
            $childSrc .= ' https://crowdin.com';
        }

        $csp[] = "worker-src $workerSrc";
        $csp[] = "child-src $childSrc";

        // Additional security directives
        $csp[] = "object-src 'none'";
        $csp[] = "base-uri 'self'";
        $csp[] = "form-action 'self'";
        $csp[] = "frame-ancestors 'none'";

        // Upgrade insecure requests in production
        if (!defined('GK_DEVEL') || !GK_DEVEL) {
            $csp[] = 'upgrade-insecure-requests';
        }

        header('Content-Security-Policy: '.implode('; ', $csp));
    }

    /**
     * Apply all other security headers.
     */
    private function applySecurityHeaders(): void {
        $f3 = \Base::instance();
        $headers = [
            // Prevent MIME type sniffing
            'X-Content-Type-Options' => 'nosniff',

            // Clickjacking protection
            'X-Frame-Options' => 'DENY',

            // XSS protection for legacy browsers
            'X-XSS-Protection' => '1; mode=block',

            // Referrer policy
            'Referrer-Policy' => 'strict-origin-when-cross-origin',

            // Permissions policy - restrict browser APIs
            'Permissions-Policy' => $this->buildPermissionsPolicy(),

            // Cross-Origin policies
            'Cross-Origin-Embedder-Policy' => 'unsafe-none',
            'Cross-Origin-Opener-Policy' => 'same-origin-allow-popups',
            'Cross-Origin-Resource-Policy' => 'same-site',
        ];

        // HSTS only in production
        if (!defined('GK_DEVEL') || !GK_DEVEL) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }

        // Cache control for non-static assets
        if (!$this->isStaticAsset($f3->get('PATH'))) {
            $headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
            $headers['Pragma'] = 'no-cache';
            $headers['Expires'] = '0';
        }

        foreach ($headers as $name => $value) {
            header($name.': '.$value);
        }
    }

    /**
     * Permissions Policy - restrict powerful browser features.
     */
    private function buildPermissionsPolicy(): string {
        return implode(', ', [
            'accelerometer=()',
            'ambient-light-sensor=()',
            'autoplay=()',
            'battery=()',
            'camera=()',
            'display-capture=()',
            'document-domain=()',
            'encrypted-media=()',
            'fullscreen=(self)',
            'geolocation=(self)', // Essential for geocaching
            'gyroscope=()',
            'magnetometer=()',
            'microphone=()',
            'midi=()',
            'payment=()',
            'picture-in-picture=()',
            'screen-wake-lock=()',
            'usb=()',
            'web-share=(self)',
            'xr-spatial-tracking=()',
        ]);
    }

    /**
     * Check if path is static asset.
     */
    private function isStaticAsset(string $path): bool {
        $staticPaths = ['/assets/', '/app-ui/', '/gkt/', '/errors/'];
        $staticExtensions = ['.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.svg', '.woff', '.woff2', '.ttf', '.eot', '.ico', '.pdf'];

        foreach ($staticPaths as $staticPath) {
            if (str_starts_with($path, $staticPath)) {
                return true;
            }
        }

        foreach ($staticExtensions as $ext) {
            if (str_ends_with(strtolower($path), $ext)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply strict headers for sensitive pages (auth, admin).
     */
    public function applyStrictHeaders(): void {
        header('X-Frame-Options: DENY');
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cross-Origin-Opener-Policy: same-origin');
    }

    /**
     * Apply CORS headers for allowed origins.
     */
    public function applyCorsHeaders(): void {
        $f3 = \Base::instance();
        $origin = $f3->get('HEADERS.Origin');
        if (in_array($origin, self::ALLOWED_ORIGINS)) {
            $f3->copy('HEADERS.Origin', 'CORS.origin');
        }
    }

    /**
     * Apply CORS credentials headers for allowed origins.
     */
    public function applyCorsCredentialsHeaders(): void {
        $f3 = \Base::instance();
        $origin = $f3->get('HEADERS.Origin');
        if (in_array($origin, self::ALLOWED_ORIGINS)) {
            $f3->set('CORS.credentials', true);
        }
    }
}
