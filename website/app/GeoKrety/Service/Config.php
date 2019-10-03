<?php

namespace GeoKrety\Service;

class Config extends \Prefab {
    public function __construct() {
        // SITE CONFIG
        define('GK_SITE_BASE_SERVER_URL', getenv('GK_SITE_BASE_SERVER_URL') ?: 'https://geokrety.org');
        define('GK_SITE_ADMINISTRATORS', explode(',', getenv('GK_SITE_ADMINISTRATORS') ?: '1,26422,35313'));
        define('GK_SITE_TRACKING_CODE_LENGTH', getenv('GK_SITE_TRACKING_CODE_LENGTH') ?: 6);
        define('GK_SITE_ACCOUNT_ACTIVATION_CODE_LENGTH', getenv('GK_SITE_ACCOUNT_ACTIVATION_CODE_LENGTH') ?: 42);
        define('GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY', getenv('GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY') ?: 15);
        define('GK_SITE_EMAIL_ACTIVATION_CODE_LENGTH', getenv('GK_SITE_EMAIL_ACTIVATION_CODE_LENGTH') ?: 42);
        define('GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY', getenv('GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY') ?: 5);
        define('GK_SITE_EMAIL_REVERT_CODE_DAYS_VALIDITY', getenv('GK_SITE_EMAIL_REVERT_CODE_DAYS_VALIDITY') ?: 31);
        define('GK_SITE_PASSWORD_RECOVERY_CODE_LENGTH', getenv('GK_SITE_PASSWORD_RECOVERY_CODE_LENGTH') ?: 42);
        define('GK_SITE_PASSWORD_RECOVERY_CODE_DAYS_VALIDITY', getenv('GK_SITE_PASSWORD_RECOVERY_CODE_DAYS_VALIDITY') ?: 1);
        define('GK_SITE_SECID_CODE_LENGTH', getenv('GK_SITE_SECID_CODE_LENGTH') ?: 128);
        define('GK_SITE_MAIL_TOKEN_LENGTH', getenv('GK_SITE_MAIL_TOKEN_LENGTH') ?: 10);
        define('GK_SITE_OWNER_CODE_LENGTH', getenv('GK_SITE_OWNER_CODE_LENGTH') ?: 6);
        define('GK_SITE_USERNAME_MIN_LENGTH', getenv('GK_SITE_USERNAME_MIN_LENGTH') ?: 3);
        define('GK_SITE_USERNAME_MAX_LENGTH', getenv('GK_SITE_USERNAME_MAX_LENGTH') ?: 80);
        define('GK_SITE_USER_PASSWORD_MIN_LENGTH', getenv('GK_SITE_USER_PASSWORD_MIN_LENGTH') ?: 6);

        // SITE EMAIL From
        define('GK_SITE_EMAIL', getenv('GK_SITE_EMAIL') ?: 'geokrety@gmail.com');
        define('GK_SITE_EMAIL_SUPPORT', getenv('GK_SITE_EMAIL_SUPPORT') ?: GK_SITE_EMAIL);
        define('GK_SITE_EMAIL_REGISTRATION', getenv('GK_SITE_EMAIL_REGISTRATION') ?: GK_SITE_EMAIL);
        define('GK_SITE_EMAIL_MESSAGE_CENTER', getenv('GK_SITE_EMAIL_MESSAGE_CENTER') ?: GK_SITE_EMAIL);

        // SENTRY CONFIG
        define('GK_SENTRY_DSN', getenv('GK_SENTRY_DSN') ?: null);
        define('GK_SENTRY_ENV', getenv('GK_SENTRY_ENV') ?: 'dev');

        // Minio
        define('GK_MINIO_SERVER_URL', getenv('GK_MINIO_SERVER_URL') ?: 'http://minio:9000');
        define('GK_MINIO_SERVER_URL_EXTERNAL', getenv('GK_MINIO_SERVER_URL_EXTERNAL') ?: GK_MINIO_SERVER_URL);
        define('GK_MINIO_ACCESS_KEY', getenv('GK_MINIO_ACCESS_KEY') ?: null);
        define('GK_MINIO_SECRET_KEY', getenv('GK_MINIO_SECRET_KEY') ?: null);
        define('GK_BUCKET_STATPIC_NAME', getenv('GK_BUCKET_STATPIC_NAME') ?: 'statpic');

        // Environment
        define('GK_INSTANCE_NAME', getenv('GK_INSTANCE_NAME') ?: 'dev');
        define('GK_ENVIRONMENT', getenv('GK_ENVIRONMENT') ?: 'dev');
        define('GK_DEPLOY_DATE', getenv('GK_DEPLOY_DATE') ?: 'unknown');
        define('GK_IS_PRODUCTION', GK_ENVIRONMENT === 'prod');
        if (GK_IS_PRODUCTION) {
            define('GK_DEBUG', false);
            define('GK_F3_DEBUG', false);
        } else {
            define('GK_DEBUG', getenv('GK_DEBUG') ? filter_var(getenv('GK_DEBUG'), FILTER_VALIDATE_BOOLEAN) : false);
            define('GK_F3_DEBUG', getenv('GK_F3_DEBUG') ?: true);
        }
        define('GK_APP_NAME', getenv('GK_APP_NAME') ?: 'www');
        define('GK_APP_VERSION', getenv('GIT_COMMIT') ?: 'undef');
        define('GK_EMAIL_SUBJECT_PREFIX', getenv('GK_EMAIL_SUBJECT_PREFIX') ?: '[GeoKrety] ');
        define('GK_BOT_USERNAME', getenv('GK_BOT_USERNAME') ?: 'GeoKrety Bot ');

        // DATABASE config
        define('GK_DB_DSN', getenv('GK_DB_DSN') ?: 'mysql:host=db;port=3306;dbname=geokrety;charset=utf8mb4');
        define('GK_DB_USER', getenv('GK_DB_USER') ?: 'geokrety');
        define('GK_DB_PASSWORD', getenv('GK_DB_PASSWORD') ?: 'geokrety');

        // SMTP config
        define('GK_SMTP_HOST', getenv('GK_SMTP_HOST') ?: null);
        define('GK_SMTP_PORT', getenv('GK_SMTP_PORT') ?: 465);
        define('GK_SMTP_SCHEME', getenv('GK_SMTP_SCHEME') ?: 'SSL');
        define('GK_SMTP_USER', getenv('GK_SMTP_USER') ?: '');
        define('GK_SMTP_PASSWORD', getenv('GK_SMTP_PASSWORD') ?: '');

        // HASHING seeds
        define('GK_PASSWORD_MIN_HASH_ROTATION', 10);
        define('GK_PASSWORD_MAX_HASH_ROTATION', 99);
        define('GK_PASSWORD_HASH_ROTATION', getenv('GK_PASSWORD_HASH_ROTATION') ?: 11);
        define('GK_PASSWORD_HASH', getenv('GK_PASSWORD_HASH') ?: 'geokrety');
        define('GK_PASSWORD_SEED', getenv('GK_PASSWORD_SEED') ?: 'rand_string');

        // F3
        define('GK_F3_UI', getenv('GK_F3_UI') ?: '../app-ui/');
        define('GK_F3_TMP', getenv('GK_F3_TMP') ?: '/tmp/f3/');
        define('GK_F3_CACHE', getenv('GK_F3_CACHE') ?: 'redis=redis:6379');
        define('GK_GETTEXT_BINDTEXTDOMAIN_PATH', getenv('GK_GETTEXT_BINDTEXTDOMAIN_PATH') ?: '../app/languages');

        // Smarty
        define('GK_SMARTY_TEMPLATES_DIR', getenv('GK_SMARTY_TEMPLATES_DIR') ?: '../app-templates/smarty/');
        define('GK_SMARTY_FOUNDATION_TEMPLATES_DIR', getenv('GK_SMARTY_FOUNDATION_TEMPLATES_DIR') ?: '../app-templates/foundation-emails/dist/');
        define('GK_SMARTY_PLUGINS_DIR', getenv('GK_SMARTY_PLUGINS_DIR') ?: '../app-templates/smarty/plugins/');
        define('GK_SMARTY_COMPILE_DIR', getenv('GK_SMARTY_COMPILE_DIR') ?: '/tmp/smarty/compile/');
        define('GK_SMARTY_CACHE_DIR', getenv('GK_SMARTY_CACHE_DIR') ?: '/tmp/smarty/cache/');

        // Phinx
        define('GK_DB_MIGRATIONS_DIR', getenv('GK_DB_MIGRATIONS_DIR') ?: './db/migrations/');
        define('GK_DB_SEEDS_DIR', getenv('GK_DB_SEEDS_DIR') ?: './db/seeds/');

        // HTMLPurifier
        define('GK_HTMLPURIFIER_CACHE_DIR', getenv('GK_HTMLPURIFIER_CACHE_DIR') ?: '/tmp/htmlpurifier/cache/');

        // Home coordinates
        define('GK_USER_OBSERVATION_AREA_MAX_KM', getenv('GK_USER_OBSERVATION_AREA_MAX_KM') ?: 10);

        // Statpic banner
        define('GK_USER_STATPIC_TEMPLATE_COUNT', getenv('GK_USER_STATPIC_TEMPLATE_COUNT') ?: 9);
        define('GK_USER_STATPIC_FONT', getenv('GK_USER_STATPIC_FONT') ?: 'RobotoCondensed-Regular.ttf');

        // google api
        define('GK_GOOGLE_RECAPTCHA_PUBLIC_KEY', getenv('GK_GOOGLE_RECAPTCHA_PUBLIC_KEY') ?: false);
        define('GK_GOOGLE_RECAPTCHA_SECRET_KEY', getenv('GK_GOOGLE_RECAPTCHA_SECRET_KEY') ?: false);
        define('GK_GOOGLE_RECAPTCHA_JS_URL', getenv('GK_GOOGLE_RECAPTCHA_JS_URL') ?: 'https://www.google.com/recaptcha/api.js');

        // go2geo url
        define('GK_SERVICE_GO2GEO_URL', getenv('GK_SERVICE_GO2GEO_URL') ?: 'https://geokrety.org/go2geo/?wpt=%s');

        // map api url
        define('GK_MAP_URL', getenv('GK_MAP_URL') ?: 'https://api.geokretymap.org');
        define('GK_MAP_DEFAULT_ZOOM', getenv('GK_MAP_DEFAULT_ZOOM') ?: 4);
        define('GK_MAP_DEFAULT_ZOOM_USER_HOME', getenv('GK_MAP_DEFAULT_ZOOM_USER_HOME') ?: 8);
        define('GK_MAP_DEFAULT_LAT', getenv('GK_MAP_DEFAULT_LAT') ?: 42.941);
        define('GK_MAP_DEFAULT_LON', getenv('GK_MAP_DEFAULT_LON') ?: 2.109);

        // Home LATEST COUNTS
        define('GK_HOME_COUNT_NEWS', getenv('GK_HOME_COUNT_NEWS') ?: 3);
        define('GK_HOME_COUNT_MOVES', getenv('GK_HOME_COUNT_MOVES') ?: 10);
        define('GK_HOME_COUNT_RECENT_GEOKRETY', getenv('GK_HOME_COUNT_RECENT_GEOKRETY') ?: 10);

        // PAGINATION LIMITS
        define('GK_PAGINATION_NEWS', getenv('GK_PAGINATION_NEWS') ?: 2);
        define('GK_PAGINATION_GEOKRET_MOVES', getenv('GK_PAGINATION_GEOKRET_MOVES') ?: 10);
        define('GK_PAGINATION_USER_INVENTORY', getenv('GK_PAGINATION_USER_INVENTORY') ?: 10);
        define('GK_PAGINATION_USER_OWNED_GEOKRETY', getenv('GK_PAGINATION_USER_OWNED_GEOKRETY') ?: 10);
        define('GK_PAGINATION_USER_WATCHED_GEOKRETY', getenv('GK_PAGINATION_USER_WATCHED_GEOKRETY') ?: 10);
        define('GK_PAGINATION_USER_RECENT_MOVES', getenv('GK_PAGINATION_USER_RECENT_MOVES') ?: 10);
        define('GK_PAGINATION_USER_OWNED_GEOKRETY_RECENT_MOVES', getenv('GK_PAGINATION_USER_OWNED_GEOKRETY_RECENT_MOVES') ?: 10);

        // TTL LIMITS
        define('GK_SITE_CACHE_TTL_WAYPOINT', getenv('GK_SITE_CACHE_TTL_WAYPOINT') ?: 3600);
        define('GK_SITE_CACHE_TTL_STATS', getenv('GK_SITE_CACHE_TTL_STATS') ?: 600);
        define('GK_SITE_CACHE_TTL_LATEST_NEWS', getenv('GK_SITE_CACHE_TTL_LATEST_NEWS') ?: 60);
        define('GK_SITE_CACHE_TTL_LATEST_MOVED_GEOKRETY', getenv('GK_SITE_CACHE_TTL_LATEST_MOVED_GEOKRETY') ?: 60);
        define('GK_SITE_CACHE_TTL_LATEST_GEOKRETY', getenv('GK_SITE_CACHE_TTL_LATEST_GEOKRETY') ?: 60);

        // API LIMITS
        define('GK_API_EXPORT_LIMIT_DAYS', getenv('GK_API_EXPORT_LIMIT_DAYS') ?: 10);

        // ITEMS LIMITS
        define('GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS', getenv('GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS') ?: 10);
        define('GK_CHECK_WAYPOINT_MIN_LENGTH', getenv('GK_CHECK_WAYPOINT_MIN_LENGTH') ?: 4);
        define('GK_CHECK_WAYPOINT_MAX_LENGTH', getenv('GK_CHECK_WAYPOINT_MAX_LENGTH') ?: 20);
        define('GK_CHECK_WAYPOINT_NAME_MIN_LENGTH', getenv('GK_CHECK_WAYPOINT_NAME_MIN_LENGTH') ?: 4);
        define('GK_CHECK_WAYPOINT_NAME_MAX_LENGTH', getenv('GK_CHECK_WAYPOINT_NAME_MAX_LENGTH') ?: 20);
        define('GK_CHECK_WAYPOINT_NAME_COUNT', getenv('GK_CHECK_WAYPOINT_NAME_COUNT') ?: 10);

        // SIZES
        define('GK_GEOKRET_NAME_MIN_LENGTH', getenv('GK_GEOKRET_NAME_MIN_LENGTH') ?: 4);
        define('GK_GEOKRET_NAME_MAX_LENGTH', getenv('GK_GEOKRET_NAME_MAX_LENGTH') ?: 75);
        define('GK_USERNAME_MIN_LENGTH', getenv('GK_USERNAME_MIN_LENGTH') ?: 3);
        define('GK_USERNAME_MAX_LENGTH', getenv('GK_USERNAME_MAX_LENGTH') ?: 20);

        // CDN
        define('GK_CDN_SERVER_URL', getenv('GK_CDN_SERVER_URL') ?: 'https://cdn.geokrety.org');

        define('GK_CDN_GPGKEY_URL', getenv('GK_CDN_GPGKEY_URL') ?: GK_CDN_SERVER_URL.'/geokrety.org.pub');
        define('GK_CDN_GPGKEY_ID', getenv('GK_CDN_GPGKEY_ID') ?: '76B00039');

        define('GK_CDN_CSS_URL', getenv('GK_CDN_CSS_URL') ?: GK_CDN_SERVER_URL.'/css');
        define('GK_CDN_IMAGES_URL', getenv('GK_CDN_IMAGES_URL') ?: GK_CDN_SERVER_URL.'/images');
        define('GK_CDN_ICONS_URL', getenv('GK_CDN_ICONS_URL') ?: GK_CDN_IMAGES_URL.'/icons');
        define('GK_CDN_LOGOS_URL', getenv('GK_CDN_LOGOS_URL') ?: GK_CDN_IMAGES_URL.'/logos');

        // CDN LIBRARIES
        define('GK_CDN_LIBRARIES_URL', getenv('GK_CDN_LIBRARIES_URL') ?: GK_CDN_SERVER_URL.'/libraries');

        define('GK_CDN_LIBRARIES_PARSLEY_CSS_URL', getenv('GK_CDN_LIBRARIES_PARSLEY_CSS_URL') ?: GK_CDN_LIBRARIES_URL.'/parsley.js/2.9.1/parsley.css');
        define('GK_CDN_LIBRARIES_PARSLEY_JS_URL', getenv('GK_CDN_LIBRARIES_PARSLEY_JS_URL') ?: GK_CDN_LIBRARIES_URL.'/parsley.js/2.9.1/parsley.js');
        define('GK_CDN_LIBRARIES_PARSLEY_JS_LANG_DIR_URL', getenv('GK_CDN_LIBRARIES_PARSLEY_JS_LANG_DIR_URL') ?: GK_CDN_LIBRARIES_URL.'/parsley.js/2.9.1/i18n');
        define('GK_CDN_LIBRARIES_PARSLEY_BOOTSTRAP3_JS_URL', getenv('GK_CDN_LIBRARIES_PARSLEY_BOOTSTRAP3_JS_URL') ?: GK_CDN_LIBRARIES_URL.'/parsley.js/parsleyjs-bootstrap3.js');

        define('GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL', getenv('GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL') ?: GK_CDN_LIBRARIES_URL.'/inscrybmde/1.11.6/inscrybmde.min.css');
        define('GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL', getenv('GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL') ?: GK_CDN_LIBRARIES_URL.'/inscrybmde/1.11.6/inscrybmde.min.js');

        define('GK_CDN_LIBRARIES_PRISM_CSS_URL', getenv('GK_CDN_LIBRARIES_PRISM_CSS_URL') ?: GK_CDN_LIBRARIES_URL.'/prism/1.16.0/prism.min.css');
        define('GK_CDN_LIBRARIES_PRISM_JS_URL', getenv('GK_CDN_LIBRARIES_PRISM_JS_URL') ?: GK_CDN_LIBRARIES_URL.'/prism/1.16.0/prism.min.js');
        define('GK_CDN_LIBRARIES_PRISM_PHP_JS_URL', getenv('GK_CDN_LIBRARIES_PRISM_PHP_JS_URL') ?: GK_CDN_LIBRARIES_URL.'/prism/1.16.0/prism-php.min.js');
        define('GK_CDN_LIBRARIES_MARKUP_TEMPLATING_JS_URL', getenv('GK_CDN_LIBRARIES_MARKUP_TEMPLATING_JS_URL') ?: GK_CDN_LIBRARIES_URL.'/prism/1.16.0/prism-markup-templating.min.js');

        define('GK_CDN_LEAFLET_JS', getenv('GK_CDN_LEAFLET_JS') ?: GK_CDN_LIBRARIES_URL.'/leaflet/1.4.0/leaflet.js');
        define('GK_CDN_LEAFLET_CSS', getenv('GK_CDN_LEAFLET_CSS') ?: GK_CDN_LIBRARIES_URL.'/leaflet/1.4.0/leaflet.css');
        define('GK_CDN_LEAFLET_CENTERCROSS_JS', getenv('GK_CDN_LEAFLET_CENTERCROSS_JS') ?: GK_CDN_LIBRARIES_URL.'/Leaflet.CenterCross/0.0.8/leaflet.CenterCross.js');
        define('GK_CDN_LEAFLET_AJAX_JS', getenv('GK_CDN_LEAFLET_AJAX_JS') ?: GK_CDN_LIBRARIES_URL.'/leaflet-ajax/2.1.0/leaflet.ajax.min.js');
        define('GK_CDN_LEAFLET_MARKERCLUSTER_JS', getenv('GK_CDN_LEAFLET_MARKERCLUSTER_JS') ?: GK_CDN_LIBRARIES_URL.'/Leaflet.markercluster/1.4.1/leaflet.markercluster.js');
        define('GK_CDN_LEAFLET_MARKERCLUSTER_CSS', getenv('GK_CDN_LEAFLET_MARKERCLUSTER_CSS') ?: GK_CDN_LIBRARIES_URL.'/Leaflet.markercluster/1.4.1/MarkerCluster.css');
        define('GK_CDN_LEAFLET_MARKERCLUSTER_DEFAULT_CSS', getenv('GK_CDN_LEAFLET_MARKERCLUSTER_DEFAULT_CSS') ?: GK_CDN_LIBRARIES_URL.'/Leaflet.markercluster/1.4.1/MarkerCluster.Default.css');
        define('GK_CDN_LEAFLET_GEOKRETYFILTER_JS', getenv('GK_CDN_LEAFLET_GEOKRETYFILTER_JS') ?: GK_CDN_LIBRARIES_URL.'/Leaflet.geokretyfilter/leaflet.Control.GeoKretyFilter.js');
        define('GK_CDN_LEAFLET_GEOKRETYFILTER_CSS', getenv('GK_CDN_LEAFLET_GEOKRETYFILTER_CSS') ?: GK_CDN_LIBRARIES_URL.'/Leaflet.geokretyfilter/leaflet.Control.GeoKretyFilter.css');
        define('GK_CDN_LEAFLET_PLUGIN_BING_JS', getenv('GK_CDN_LEAFLET_PLUGIN_BING_JS') ?: GK_CDN_LIBRARIES_URL.'/leaflet-bing/Bing.js');
        define('GK_CDN_LEAFLET_NOUISLIDER_JS', getenv('GK_CDN_LEAFLET_NOUISLIDER_JS') ?: GK_CDN_LIBRARIES_URL.'/noUiSlider/8.1.0/nouislider.min.js');
        define('GK_CDN_LEAFLET_NOUISLIDER_CSS', getenv('GK_CDN_LEAFLET_NOUISLIDER_CSS') ?: GK_CDN_LIBRARIES_URL.'/noUiSlider/8.1.0/nouislider.min.css');
        define('GK_CDN_LEAFLET_SPIN_JS', getenv('GK_CDN_LEAFLET_SPIN_JS') ?: GK_CDN_LIBRARIES_URL.'/leaflet.spin.js/leaflet.spin.js');
        define('GK_CDN_LEAFLET_FULLSCREEN_JS', getenv('GK_CDN_LEAFLET_FULLSCREEN_JS') ?: GK_CDN_LIBRARIES_URL.'/leaflet-fullscreen/v0.0.4/Leaflet.fullscreen.min.js');
        define('GK_CDN_LEAFLET_FULLSCREEN_CSS', getenv('GK_CDN_LEAFLET_FULLSCREEN_CSS') ?: GK_CDN_LIBRARIES_URL.'/leaflet-fullscreen/v0.0.4/leaflet.fullscreen.css');

        define('GK_CDN_SPIN_JS', getenv('GK_CDN_SPIN_JS') ?: GK_CDN_LIBRARIES_URL.'/spin.js/2.3.2/spin.min.js');

        define('GK_CDN_MOMENT_JS', getenv('GK_CDN_MOMENT_JS') ?: GK_CDN_LIBRARIES_URL.'/moment.js/2.24.0/moment-with-locales.min.js');
        define('GK_CDN_BOOTSTRAP_DATETIMEPICKER_JS', getenv('GK_CDN_BOOTSTRAP_DATETIMEPICKER_JS') ?: GK_CDN_LIBRARIES_URL.'/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js');
        define('GK_CDN_BOOTSTRAP_DATETIMEPICKER_CSS', getenv('GK_CDN_BOOTSTRAP_DATETIMEPICKER_CSS') ?: GK_CDN_LIBRARIES_URL.'/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css');

        define('GK_CDN_LATINIZE_JS', getenv('GK_CDN_LATINIZE_JS') ?: GK_CDN_LIBRARIES_URL.'/latinize/0.4.0/latinize.min.js');

        define('GK_CDN_BOOTSTRAP_3_TYPEAHEAD_JS', getenv('GK_CDN_BOOTSTRAP_3_TYPEAHEAD_JS') ?: GK_CDN_LIBRARIES_URL.'/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.js');

        define('GK_CDN_ZXCVBN_JS', getenv('GK_CDN_ZXCVBN_JS') ?: GK_CDN_LIBRARIES_URL.'/zxcvbn/4.4.2/zxcvbn.min.js');
        define('GK_CDN_STRENGTHIFY_JS', getenv('GK_CDN_STRENGTHIFY_JS') ?: GK_CDN_LIBRARIES_URL.'/strengthify/0.5.8/jquery.strengthify.min.js');
        define('GK_CDN_STRENGTHIFY_CSS', getenv('GK_CDN_STRENGTHIFY_CSS') ?: GK_CDN_LIBRARIES_URL.'/strengthify/0.5.8/strengthify.min.css');
    }

    public static function printEnvironements() {
        $text = '';
        foreach ($_ENV as $key => $value) {
            $text .= "* **$key** -> $value\n";
        }
        echo \Markdown::instance()->convert($text);
    }

    public function isValid() {
        return count($this->validationDetails()) === 0;
    }

    public function validationDetails() {
        $details = [];
        if (GK_PASSWORD_HASH_ROTATION > GK_PASSWORD_MAX_HASH_ROTATION) {
            array_push($details, sprintf('GK_PASSWORD_HASH_ROTATION must be less than %d', GK_PASSWORD_MAX_HASH_ROTATION));
        }
        if (GK_PASSWORD_HASH_ROTATION < GK_PASSWORD_MIN_HASH_ROTATION) {
            array_push($details, sprintf('GK_PASSWORD_HASH_ROTATION must be greater than %d', GK_PASSWORD_MIN_HASH_ROTATION));
        }

        return $details;
    }
}
