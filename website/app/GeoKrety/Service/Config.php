<?php

namespace GeoKrety\Service;

class Config {
    public function __construct() {
        // TODO REMOVE THAT
        define('LANGUAGE', 'en');

        // SITE CONFIG
        define('GK_SITE_BASE_SERVER_URL', $_ENV['GK_SITE_BASE_SERVER_URL'] ?? 'https://geokrety.org');
        define('GK_SITE_EMAIL', $_ENV['GK_SITE_EMAIL'] ?? 'geokrety@gmail.com');
        define('GK_SITE_ADMINISTRATORS', explode(',', $_ENV['GK_SITE_ADMINISTRATORS'] ?? '1,26422,35313'));
        define('GK_SITE_TRACKING_CODE_LENGTH', $_ENV['GK_SITE_TRACKING_CODE_LENGTH'] ?? 6);
        define('GK_SITE_EMAIL_ACTIVATION_CODE_LENGTH', $_ENV['GK_SITE_EMAIL_ACTIVATION_CODE_LENGTH'] ?? 42);
        define('GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY', $_ENV['GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY'] ?? 5);
        define('GK_SITE_SECID_CODE_LENGTH', $_ENV['GK_SITE_SECID_CODE_LENGTH'] ?? 128);

        // Environment
        define('GK_ENVIRONMENT', $_ENV['GK_ENVIRONMENT'] ?? 'dev');
        define('GK_IS_PRODUCTION', GK_ENVIRONMENT === 'prod');
        define('GK_DEBUG', isset($_ENV['GK_DEBUG']) && filter_var($_ENV['GK_DEBUG'], FILTER_VALIDATE_BOOLEAN));
        define('GK_F3_DEBUG', $_ENV['GK_DEBUG'] ?? 1);

        // DATABASE config
        define('GK_DB_DSN', 'mysql:host=db;port=3306;dbname=geokrety;charset=utf8mb4');
        // define('GK_DB_DSN', $_ENV['GK_DB_DSN'] ?? 'mysql:host=db;port=3306;dbname=geokrety;charset=utf8mb4');
        define('GK_DB_USER', $_ENV['GK_DB_USER'] ?? 'geokrety');
        define('GK_DB_PASSWORD', $_ENV['GK_DB_PASSWORD'] ?? 'geokrety');

        // SMTP config
        define('GK_SMTP_HOST', $_ENV['GK_SMTP_HOST'] ?? 'smtp.gmail.com');
        define('GK_SMTP_PORT', $_ENV['GK_SMTP_PORT'] ?? 465);
        define('GK_SMTP_SCHEME', $_ENV['GK_SMTP_SCHEME'] ?? 'SSL');
        define('GK_SMTP_USER', $_ENV['GK_SMTP_USER'] ?? '');
        define('GK_SMTP_PASSWORD', $_ENV['GK_SMTP_PASSWORD'] ?? '');

        // HASHING seeds
        define('GK_PASSWORD_HASH_ROTATION', $_ENV['GK_PASSWORD_HASH_ROTATION'] ?? 8);
        define('GK_PASSWORD_HASH', $_ENV['GK_PASSWORD_HASH'] ?? 'geokrety');
        define('GK_PASSWORD_SEED', $_ENV['GK_PASSWORD_SEED'] ?? 'rand_string');

        // F3
        define('GK_F3_TMP', $_ENV['GK_F3_TMP'] ?? '/tmp/f3/');
        define('GK_F3_CACHE', $_ENV['GK_F3_CACHE'] ?? 'redis=redis:6379');

        // Smarty
        define('GK_SMARTY_TEMPLATES_DIR', $_ENV['GK_SMARTY_TEMPLATES_DIR'] ?? './app-templates/smarty/');
        define('GK_SMARTY_PLUGINS_DIR', $_ENV['GK_SMARTY_PLUGINS_DIR'] ?? './app-templates/smarty/plugins/');
        define('GK_SMARTY_COMPILE_DIR', $_ENV['GK_SMARTY_COMPILE_DIR'] ?? '/tmp/smarty/compile/');
        define('GK_SMARTY_CACHE_DIR', $_ENV['GK_SMARTY_CACHE_DIR'] ?? '/tmp/smarty/cache/');

        // Home coordinates
        define('GK_USER_OBSERVATION_AREA_MAX_KM', $_ENV['GK_USER_OBSERVATION_AREA_MAX_KM'] ?? 10);

        // HTMLPurifier
        define('GK_HTMLPURIFIER_CACHE_DIR', $_ENV['GK_HTMLPURIFIER_CACHE_DIR'] ?? '/tmp/htmlpurifier/cache/');

        // map api url
        define('GK_MAP_URL', $_ENV['GK_MAP_URL'] ?? 'https://api.geokretymap.org');
        define('GK_MAP_DEFAULT_PARAMS', $_ENV['GK_MAP_DEFAULT_PARAMS'] ?? '#2/42.941/2.109/1/1/0/0/90/');

        // PAGINATION LIMITS
        define('GK_PAGINATION_NEWS', $_ENV['GK_PAGINATION_NEWS'] ?? 2);
        define('GK_PAGINATION_GEOKRET_MOVES', $_ENV['GK_PAGINATION_GEOKRET_MOVES'] ?? 10);

        // TTL LIMITS
        define('GK_SITE_CACHE_TTL_WAYPOINT', $_ENV['GK_SITE_CACHE_TTL_WAYPOINT'] ?? 3600);
        define('GK_SITE_CACHE_TTL_STATS', $_ENV['GK_SITE_CACHE_TTL_STATS'] ?? 600);
        define('GK_SITE_CACHE_TTL_LATEST_NEWS', $_ENV['GK_SITE_CACHE_TTL_LATEST_NEWS'] ?? 1800);
        define('GK_SITE_CACHE_TTL_USER_MOVED_GEOKRET', $_ENV['GK_SITE_CACHE_TTL_USER_MOVED_GEOKRET'] ?? 1800);

        // API LIMITS
        define('GK_API_EXPORT_LIMIT_DAYS', $_ENV['GK_API_EXPORT_LIMIT_DAYS'] ?? 10);

        // CDN
        define('GK_CDN_SERVER_URL', $_ENV['GK_CDN_SERVER_URL'] ?? 'https://cdn.geokrety.org');

        define('GK_CDN_CSS_URL', $_ENV['GK_CDN_CSS_URL'] ?? GK_CDN_SERVER_URL.'/css');
        // define('GK_CDN_JS_URL', $_ENV['GK_CDN_IMAGES_URL'] ?? GK_CDN_SERVER_URL.'/images');
        define('GK_CDN_IMAGES_URL', $_ENV['GK_CDN_IMAGES_URL'] ?? GK_CDN_SERVER_URL.'/images');
        define('GK_CDN_ICONS_URL', $_ENV['GK_CDN_ICONS_URL'] ?? GK_CDN_IMAGES_URL.'/icons');

        // CDN LIBRARIES
        define('GK_CDN_LIBRARIES_URL', $_ENV['GK_CDN_LIBRARIES_URL'] ?? GK_CDN_SERVER_URL.'/libraries');

        define('GK_CDN_LIBRARIES_PARSLEY_CSS_URL', $_ENV['GK_CDN_LIBRARIES_PARSLEY_CSS_URL'] ?? GK_CDN_LIBRARIES_URL.'/parsley.js/2.9.1/parsley.css');
        define('GK_CDN_LIBRARIES_PARSLEY_JS_URL', $_ENV['GK_CDN_LIBRARIES_PARSLEY_JS_URL'] ?? GK_CDN_LIBRARIES_URL.'/parsley.js/2.9.1/parsley.js');
        define('GK_CDN_LIBRARIES_PARSLEY_BOOTSTRAP3_JS_URL', $_ENV['GK_CDN_LIBRARIES_PARSLEY_BOOTSTRAP3_JS_URL'] ?? GK_CDN_LIBRARIES_URL.'/parsley.js/parsleyjs-bootstrap3.js');

        define('GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL', $_ENV['GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL'] ?? GK_CDN_LIBRARIES_URL.'/inscrybmde/1.11.6/inscrybmde.min.css');
        define('GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL', $_ENV['GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL'] ?? GK_CDN_LIBRARIES_URL.'/inscrybmde/1.11.6/inscrybmde.min.js');

        define('GK_CDN_LIBRARIES_PRISM_CSS_URL', $_ENV['GK_CDN_LIBRARIES_PRISM_CSS_URL'] ?? GK_CDN_LIBRARIES_URL.'/prism/1.16.0/prism.min.css');
        define('GK_CDN_LIBRARIES_PRISM_JS_URL', $_ENV['GK_CDN_LIBRARIES_PRISM_JS_URL'] ?? GK_CDN_LIBRARIES_URL.'/prism/1.16.0/prism.min.js');
        define('GK_CDN_LIBRARIES_PRISM_PHP_JS_URL', $_ENV['GK_CDN_LIBRARIES_PRISM_PHP_JS_URL'] ?? GK_CDN_LIBRARIES_URL.'/prism/1.16.0/prism-php.min.js');
        define('GK_CDN_LIBRARIES_MARKUP_TEMPLATING_JS_URL', $_ENV['GK_CDN_LIBRARIES_MARKUP_TEMPLATING_JS_URL'] ?? GK_CDN_LIBRARIES_URL.'/prism/1.16.0/prism-markup-templating.min.js');

        define('GK_CDN_LEAFLET_JS', $_ENV['GK_CDN_LEAFLET_JS'] ?? GK_CDN_LIBRARIES_URL.'/leaflet/1.4.0/leaflet.js');
        define('GK_CDN_LEAFLET_CSS', $_ENV['GK_CDN_LEAFLET_CSS'] ?? GK_CDN_LIBRARIES_URL.'/leaflet/1.4.0/leaflet.css');
        define('GK_CDN_LEAFLET_CENTERCROSS_JS', $_ENV['GK_CDN_LEAFLET_CENTERCROSS_JS'] ?? GK_CDN_LIBRARIES_URL.'/Leaflet.CenterCross/0.0.8/leaflet.CenterCross.js');
        define('GK_CDN_LEAFLET_AJAX_JS', $_ENV['GK_CDN_LEAFLET_AJAX_JS'] ?? GK_CDN_LIBRARIES_URL.'/leaflet-ajax/2.1.0/leaflet.ajax.min.js');
        define('GK_CDN_LEAFLET_MARKERCLUSTER_JS', $_ENV['GK_CDN_LEAFLET_MARKERCLUSTER_JS'] ?? GK_CDN_LIBRARIES_URL.'/Leaflet.markercluster/1.4.1/leaflet.markercluster.js');
        define('GK_CDN_LEAFLET_MARKERCLUSTER_CSS', $_ENV['GK_CDN_LEAFLET_MARKERCLUSTER_CSS'] ?? GK_CDN_LIBRARIES_URL.'/Leaflet.markercluster/1.4.1/MarkerCluster.css');
        define('GK_CDN_LEAFLET_MARKERCLUSTER_DEFAULT_CSS', $_ENV['GK_CDN_LEAFLET_MARKERCLUSTER_DEFAULT_CSS'] ?? GK_CDN_LIBRARIES_URL.'/Leaflet.markercluster/1.4.1/MarkerCluster.Default.css');
        define('GK_CDN_LEAFLET_GEOKRETYFILTER_JS', $_ENV['GK_CDN_LEAFLET_GEOKRETYFILTER_JS'] ?? GK_CDN_LIBRARIES_URL.'/Leaflet.geokretyfilter/leaflet.Control.GeoKretyFilter.js');
        define('GK_CDN_LEAFLET_GEOKRETYFILTER_CSS', $_ENV['GK_CDN_LEAFLET_GEOKRETYFILTER_CSS'] ?? GK_CDN_LIBRARIES_URL.'/Leaflet.geokretyfilter/leaflet.Control.GeoKretyFilter.css');
        define('GK_CDN_LEAFLET_PLUGIN_BING_JS', $_ENV['GK_CDN_LEAFLET_PLUGIN_BING_JS'] ?? GK_CDN_LIBRARIES_URL.'/leaflet-bing/Bing.js');
        define('GK_CDN_LEAFLET_NOUISLIDER_JS', $_ENV['GK_CDN_LEAFLET_NOUISLIDER_JS'] ?? GK_CDN_LIBRARIES_URL.'/noUiSlider/8.1.0/nouislider.min.js');
        define('GK_CDN_LEAFLET_NOUISLIDER_CSS', $_ENV['GK_CDN_LEAFLET_NOUISLIDER_CSS'] ?? GK_CDN_LIBRARIES_URL.'/noUiSlider/8.1.0/nouislider.min.css');
        define('GK_CDN_LEAFLET_SPIN_JS', $_ENV['GK_CDN_LEAFLET_SPIN_JS'] ?? GK_CDN_LIBRARIES_URL.'/leaflet.spin.js/leaflet.spin.js');
        define('GK_CDN_LEAFLET_FULLSCREEN_JS', $_ENV['GK_CDN_LEAFLET_FULLSCREEN_JS'] ?? GK_CDN_LIBRARIES_URL.'/leaflet-fullscreen/v0.0.4/Leaflet.fullscreen.min.js');
        define('GK_CDN_LEAFLET_FULLSCREEN_CSS', $_ENV['GK_CDN_LEAFLET_FULLSCREEN_CSS'] ?? GK_CDN_LIBRARIES_URL.'/leaflet-fullscreen/v0.0.4/leaflet.fullscreen.css');

        define('GK_CDN_SPIN_JS', $_ENV['GK_CDN_SPIN_JS'] ?? GK_CDN_LIBRARIES_URL.'/spin.js/2.3.2/spin.min.js');

        define('GK_CDN_ZXCVBN_JS', $_ENV['GK_CDN_ZXCVBN_JS'] ?? GK_CDN_LIBRARIES_URL.'/zxcvbn/4.4.2/zxcvbn.min.js');
        define('GK_CDN_STRENGTHIFY_JS', $_ENV['GK_CDN_STRENGTHIFY_JS'] ?? GK_CDN_LIBRARIES_URL.'/strengthify/0.5.8/jquery.strengthify.min.js');
        define('GK_CDN_STRENGTHIFY_CSS', $_ENV['GK_CDN_STRENGTHIFY_CSS'] ?? GK_CDN_LIBRARIES_URL.'/strengthify/0.5.8/strengthify.min.css');
    }

    public static function printEnvironements() {
        $text = '';
        foreach ($_ENV as $key => $value) {
            $text .= "* **$key** -> $value\n";
        }
        echo \Markdown::instance()->convert($text);
    }
}
