<?php

namespace GeoKrety\Service;

class Config {

    public function __construct() {
        // TODO REMOVE THAT
        define('IS_LOGGED_IN', $_ENV['IS_LOGGED_IN'] ?? True);
        define('CURRENT_USER', 26422);
        define('LANGUAGE', 'en');

        // Environment
        define('GK_ENVIRONMENT', $_ENV['GK_ENVIRONMENT'] ?? 'dev');
        define('GK_IS_PRODUCTION', GK_ENVIRONMENT === 'prod');
        define('GK_DEBUG', isset($_ENV['GK_DEBUG']) && filter_var($_ENV['GK_DEBUG'], FILTER_VALIDATE_BOOLEAN));

        // DATABASE config
        define('GK_DB_DSN', 'mysql:host=db;port=3306;dbname=geokrety;charset=utf8mb4');
        // define('GK_DB_DSN', $_ENV['GK_DB_DSN'] ?? 'mysql:host=db;port=3306;dbname=geokrety;charset=utf8mb4');
        define('GK_DB_USER', $_ENV['GK_DB_USER'] ?? 'geokrety');
        define('GK_DB_PASSWORD', $_ENV['GK_DB_PASSWORD'] ?? 'geokrety');

        // F3
        define('GK_F3_TMP', $_ENV['GK_F3_TMP'] ?? '/tmp/f3/');
        define('GK_F3_CACHE', $_ENV['GK_F3_CACHE'] ?? 'redis=redis:6379');

        // Smarty
        define('GK_SMARTY_TEMPLATES_DIR', $_ENV['GK_SMARTY_TEMPLATES_DIR'] ?? './app-templates/smarty/');
        define('GK_SMARTY_PLUGINS_DIR', $_ENV['GK_SMARTY_PLUGINS_DIR'] ?? './app-templates/smarty/plugins/');
        define('GK_SMARTY_COMPILE_DIR', $_ENV['GK_SMARTY_COMPILE_DIR'] ?? '/tmp/smarty/compile/');
        define('GK_SMARTY_CACHE_DIR', $_ENV['GK_SMARTY_CACHE_DIR'] ?? '/tmp/smarty/cache/');

        // HTMLPurifier
        define('GK_HTMLPURIFIER_CACHE_DIR', $_ENV['GK_HTMLPURIFIER_CACHE_DIR'] ?? '/tmp/htmlpurifier/cache/');

        // map api url
        define('GK_MAP_URL', $_ENV['GK_MAP_URL'] ?? 'https://api.geokretymap.org');
        define('GK_MAP_DEFAULT_PARAMS', $_ENV['GK_MAP_DEFAULT_PARAMS'] ?? '#2/42.941/2.109/1/1/0/0/90/');

        // SOME LIMITS
        define('GK_SITE_STATS_CACHE_TTL', $_ENV['GK_SITE_STATS_CACHE_TTL'] ?? 600);
        define('GK_SITE_LATEST_NEWS_CACHE_TTL', $_ENV['GK_SITE_LATEST_NEWS_CACHE_TTL'] ?? 1800);

        // CDN
        define('GK_CDN_SERVER_URL', $_ENV['GK_CDN_SERVER_URL'] ?? 'https://cdn.geokrety.org');

        define('GK_CDN_CSS_URL', $_ENV['GK_CDN_CSS_URL'] ?? GK_CDN_SERVER_URL.'/css');
        // define('GK_CDN_JS_URL', $_ENV['GK_CDN_IMAGES_URL'] ?? GK_CDN_SERVER_URL.'/images');
        define('GK_CDN_IMAGES_URL', $_ENV['GK_CDN_IMAGES_URL'] ?? GK_CDN_SERVER_URL.'/images');
        define('GK_CDN_ICONS_URL', $_ENV['GK_CDN_ICONS_URL'] ?? GK_CDN_IMAGES_URL.'/icons');

        // CDN LIBRARIES
        define('GK_CDN_LIBRARIES_URL', $_ENV['GK_CDN_LIBRARIES_URL'] ?? GK_CDN_SERVER_URL.'/libraries');

        define('GK_CDN_LIBRARIES_PARSLEY_BOOTSTRAP3_JS_URL', $_ENV['GK_CDN_LIBRARIES_PARSLEY_BOOTSTRAP3_JS_URL'] ?? GK_CDN_LIBRARIES_URL.'/parsley.js/parsleyjs-bootstrap3.js');
        define('GK_CDN_LIBRARIES_PARSLEY_JS_URL', $_ENV['GK_CDN_LIBRARIES_PARSLEY_JS_URL'] ?? GK_CDN_LIBRARIES_URL.'/parsley.js/2.9.1/parsley.js');
        define('GK_CDN_LIBRARIES_PARSLEY_CSS_URL', $_ENV['GK_CDN_LIBRARIES_PARSLEY_CSS_URL'] ?? GK_CDN_LIBRARIES_URL.'/parsley.js/2.9.1/parsley.css');

        define('GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL', $_ENV['GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL'] ?? GK_CDN_LIBRARIES_URL.'/inscrybmde/1.11.6/inscrybmde.min.js');
        define('GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL', $_ENV['GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL'] ?? GK_CDN_LIBRARIES_URL.'/inscrybmde/1.11.6/inscrybmde.min.css');

    }

    public static function printEnvironements() {
        $text = '';
        foreach ($_ENV as $key => $value) {
            $text .= "* **$key** -> $value\n";
        }
        echo \Markdown::instance()->convert($text);
    }
}
