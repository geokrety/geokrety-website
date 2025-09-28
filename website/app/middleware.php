<?php

use GeoKrety\Service\SecurityHeaders;

/**
 * Helper function for common security + CORS operations
 * Reduces code duplication across middleware handlers.
 */
function applySecurityWithCors(bool $withCredentials = false): void {
    SecurityHeaders::instance()->applyAll();
    SecurityHeaders::instance()->applyCorsHeaders();
    if ($withCredentials) {
        SecurityHeaders::instance()->applyCorsCredentialsHeaders();
    }
}

// =================================================================
// API Routes with CORS Support
// =================================================================

// General API routes - basic CORS support
Middleware::instance()->before('GET|HEAD|POST|PUT|OPTIONS /api/*', function () {
    applySecurityWithCors();
});

// GKT API routes - basic CORS support
Middleware::instance()->before('GET|HEAD|POST|PUT|OPTIONS /gkt/*', function () {
    applySecurityWithCors();
});

// =================================================================
// Specific Endpoints Requiring CORS Credentials
// =================================================================

// GKT inventory endpoints - require credentials for geocaching.com integration
Middleware::instance()->before('GET /gkt/v3/inventory', function () {
    applySecurityWithCors(true);
});

Middleware::instance()->before('GET /gkt/inventory_v3.php', function () {
    applySecurityWithCors(true);
});

// =================================================================
// Global Security Headers Fallback
// =================================================================

// Apply security headers to all other requests (non-API routes)
Middleware::instance()->before('GET|HEAD|POST|PUT|OPTIONS /*', function () {
    SecurityHeaders::instance()->applyAll();
});

Middleware::instance()->run();
