<?php

const ALLOWED_ORIGINS = [
    'http://www.geocaching.com',
    'https://www.geocaching.com',
];

function addCorsHeaders(Base $f3) {
    $origin = $f3->get('HEADERS.Origin');
    if (in_array($origin, ALLOWED_ORIGINS)) {
        $f3->copy('HEADERS.Origin', 'CORS.origin');
    }
}

function addCorsAllowcredentialHeaders(Base $f3) {
    $origin = $f3->get('HEADERS.Origin');
    if (in_array($origin, ALLOWED_ORIGINS)) {
        $f3->set('CORS.credentials', true);
    }
}

// enable the CORS settings only for your API routes:
\Middleware::instance()->before('GET|HEAD|POST|PUT|OPTIONS /api/*', function (Base $f3) {
    addCorsHeaders($f3);
});
\Middleware::instance()->before('GET|HEAD|POST|PUT|OPTIONS /gkt/*', function (Base $f3) {
    addCorsHeaders($f3);
});
\Middleware::instance()->before('GET /gkt/inventory_v3.php', function (Base $f3) {
    addCorsHeaders($f3);
    addCorsAllowcredentialHeaders($f3);
});
\Middleware::instance()->before('GET /gkt/v3/inventory', function (Base $f3) {
    addCorsHeaders($f3);
    addCorsAllowcredentialHeaders($f3);
});

\Middleware::instance()->run();
