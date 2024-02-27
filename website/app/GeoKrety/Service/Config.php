<?php

namespace GeoKrety\Service;

class Config extends \Prefab {
    public function __construct() {
        // SITE CONFIG
        define('HOSTNAME', getenv('HOSTNAME') ?: 'localhost');
        define('GK_SITE_BASE_SERVER_URL', getenv('GK_SITE_BASE_SERVER_URL') ?: 'https://geokrety.org');
        define('GK_SITE_BASE_SERVER_FQDN', getenv('GK_SITE_BASE_SERVER_FQDN') ?: 'geokrety.org');
        define('GK_SITE_ADMINISTRATORS', explode(',', getenv('GK_SITE_ADMINISTRATORS') ?: '26422'));
        define('GK_SITE_SESSION_REMEMBER', getenv('GK_SITE_SESSION_REMEMBER') ?: 60 * 60 * 24); // 24 hours
        define('GK_SITE_SESSION_LIFETIME_REMEMBER', getenv('GK_SITE_SESSION_LIFETIME_REMEMBER') ?: 60 * 60 * 24 * 30); // 30 days
        define('GK_SITE_SESSION_SHORT_LIVED_REMEMBER', getenv('GK_SITE_SESSION_SHORT_LIVED_REMEMBER') ?: 60 * 5); // 5 min
        define('GK_SITE_SESSION_NON_LIVED_REMEMBER', getenv('GK_SITE_SESSION_NON_LIVED_REMEMBER') ?: 0);
        define('GK_SITE_SESSION_SHORT_LIVED_TOKEN', getenv('GK_SITE_SESSION_SHORT_LIVED_TOKEN') ?: substr(str_shuffle(md5(microtime())), 0, 10));
        define('GK_SITE_ACCOUNT_ACTIVATION_CODE_LENGTH', getenv('GK_SITE_ACCOUNT_ACTIVATION_CODE_LENGTH') ?: 42);
        define('GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY', getenv('GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY') ?: 15);
        define('GK_SITE_EMAIL_ACTIVATION_CODE_LENGTH', getenv('GK_SITE_EMAIL_ACTIVATION_CODE_LENGTH') ?: 42);
        define('GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY', getenv('GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY') ?: 5);
        define('GK_SITE_EMAIL_REVERT_CODE_DAYS_VALIDITY', getenv('GK_SITE_EMAIL_REVERT_CODE_DAYS_VALIDITY') ?: 31);
        define('GK_SITE_EMAIL_REVALIDATE_CODE_DAYS_VALIDITY', getenv('GK_SITE_EMAIL_REVALIDATE_CODE_DAYS_VALIDITY') ?: 7);
        define('GK_SITE_NEWS_DISPLAY_DAYS_VALIDITY', getenv('GK_SITE_NEWS_DISPLAY_DAYS_VALIDITY') ?: 31);
        define('GK_SITE_NEWS_EMAIL_DAYS_VALIDITY', getenv('GK_SITE_NEWS_EMAIL_DAYS_VALIDITY') ?: 31);
        define('GK_SITE_PASSWORD_RECOVERY_CODE_LENGTH', getenv('GK_SITE_PASSWORD_RECOVERY_CODE_LENGTH') ?: 42);
        define('GK_SITE_PASSWORD_RECOVERY_CODE_DAYS_VALIDITY', getenv('GK_SITE_PASSWORD_RECOVERY_CODE_DAYS_VALIDITY') ?: 1);
        define('GK_SITE_SECID_CODE_LENGTH', intval(getenv('GK_SITE_SECID_CODE_LENGTH')) ?: 128);
        define('GK_SITE_MAIL_TOKEN_LENGTH', getenv('GK_SITE_MAIL_TOKEN_LENGTH') ?: 10);
        define('GK_SITE_OWNER_CODE_LENGTH', getenv('GK_SITE_OWNER_CODE_LENGTH') ?: 6);
        define('GK_SITE_USERNAME_MIN_LENGTH', getenv('GK_SITE_USERNAME_MIN_LENGTH') ?: 3);
        define('GK_SITE_USERNAME_MAX_LENGTH', getenv('GK_SITE_USERNAME_MAX_LENGTH') ?: 80);
        define('GK_SITE_USER_PASSWORD_MIN_LENGTH', getenv('GK_SITE_USER_PASSWORD_MIN_LENGTH') ?: 6);
        define('GK_SITE_PICTURE_UPLOAD_MAX_FILESIZE', getenv('GK_SITE_PICTURE_UPLOAD_MAX_FILESIZE') ?: 12); // Mo
        define('GK_SITE_PICTURE_UPLOAD_DELAY_MINUTES', getenv('GK_SITE_PICTURE_UPLOAD_DELAY_MINUTES') ?: 20);
        define('GK_SITE_CRON_LOCKED_MINUTES', getenv('GK_SITE_CRON_LOCKED_MINUTES') ?: 5);
        define('GK_SYSTEM_PATH_ALLOWED_IPS', getenv('GK_SYSTEM_PATH_ALLOWED_IPS') ? explode(',', getenv('GK_SYSTEM_PATH_ALLOWED_IPS')) : ['127.0.0.1', '192.168.0.0/16', '172.16.0.0/12', '10.0.0.0/8']);

        // SITE EMAIL From
        define('GK_SITE_EMAIL', getenv('GK_SITE_EMAIL') ?: 'geokrety@gmail.com');
        define('GK_SITE_EMAIL_NOREPLY', getenv('GK_SITE_EMAIL_NOREPLY') ?: GK_SITE_EMAIL);
        define('GK_SITE_EMAIL_NOTIF', getenv('GK_SITE_EMAIL_NOTIF') ?: GK_SITE_EMAIL);
        define('GK_SITE_EMAIL_SUPPORT', getenv('GK_SITE_EMAIL_SUPPORT') ?: GK_SITE_EMAIL);
        define('GK_SITE_EMAIL_REGISTRATION', getenv('GK_SITE_EMAIL_REGISTRATION') ?: GK_SITE_EMAIL);
        define('GK_SITE_EMAIL_DAILY_MAIL', getenv('GK_SITE_EMAIL_DAILY_MAIL') ?: GK_SITE_EMAIL);
        define('GK_SITE_EMAIL_MESSAGE_CENTER', getenv('GK_SITE_EMAIL_MESSAGE_CENTER') ?: GK_SITE_EMAIL);
        define('GK_SITE_EMAIL_ADMIN', getenv('GK_SITE_EMAIL_ADMIN') ?: GK_SITE_EMAIL);

        // SENTRY CONFIG
        define('GK_SENTRY_DSN', getenv('GK_SENTRY_DSN') ?: null);
        define('GK_SENTRY_ENV', getenv('GK_SENTRY_ENV') ?: 'dev');

        // BaseX
        define('GK_BASEX_HOST', getenv('GK_BASEX_HOST') ?: null);
        define('GK_BASEX_PORT', getenv('GK_BASEX_PORT') ?: null);
        define('GK_BASEX_USER', getenv('GK_BASEX_USER') ?: null);
        define('GK_BASEX_PASSWORD', getenv('GK_BASEX_PASSWORD') ?: null);
        define('GK_BASEX_EXPORTS_PATH', getenv('GK_BASEX_EXPORTS_PATH') ?: '/srv/basex/exports/');

        // RabbitMQ
        define('GK_RABBITMQ_HOST', getenv('GK_RABBITMQ_HOST') ?: null);
        define('GK_RABBITMQ_PORT', getenv('GK_RABBITMQ_PORT') ?: null);
        define('GK_RABBITMQ_USER', getenv('GK_RABBITMQ_USER') ?: null);
        define('GK_RABBITMQ_PASS', getenv('GK_RABBITMQ_PASSWORD') ?: null);
        define('GK_RABBITMQ_VHOST', getenv('GK_RABBITMQ_VHOST') ?: null);

        // Minio
        define('GK_MINIO_HOST', getenv('GK_MINIO_HOST') ?: 'minio');
        define('GK_MINIO_PORT', getenv('GK_MINIO_PORT') ?: '9000');
        define('GK_MINIO_SERVER_URL', getenv('GK_MINIO_SERVER_URL') ?: sprintf('http://%s:%s', GK_MINIO_HOST, GK_MINIO_PORT));
        define('GK_MINIO_SERVER_URL_EXTERNAL', getenv('GK_MINIO_SERVER_URL_EXTERNAL') ?: GK_MINIO_SERVER_URL);
        define('MINIO_ACCESS_KEY', getenv('MINIO_ACCESS_KEY') ?: null);
        define('MINIO_SECRET_KEY', getenv('MINIO_SECRET_KEY') ?: null);

        define('GK_BUCKET_NAME_STATPIC', getenv('GK_BUCKET_NAME_STATPIC') ?: 'statpic');
        define('GK_BUCKET_NAME_GEOKRETY_AVATARS', getenv('GK_BUCKET_NAME_GEOKRETY_AVATARS') ?: 'gk-avatars');
        define('GK_BUCKET_NAME_USERS_AVATARS', getenv('GK_BUCKET_NAME_USERS_AVATARS') ?: 'users-avatars');
        define('GK_BUCKET_NAME_MOVES_PICTURES', getenv('GK_BUCKET_NAME_MOVES_PICTURES') ?: 'moves-pictures');

        define('GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER', getenv('GK_BUCKET_NAME_PICTURES_PROCESSOR_DOWNLOADER') ?: 'pictures-processor-downloader');
        define('GK_BUCKET_NAME_PICTURES_PROCESSOR_UPLOADER', getenv('GK_BUCKET_NAME_PICTURES_PROCESSOR_UPLOADER') ?: 'pictures-processor-uploader');

        define('GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURE_UPLOADED', getenv('GK_MINIO_WEBHOOK_AUTH_TOKEN_PICTURE_UPLOADED') ?: '');

        define('GK_AUTH_TOKEN_DROP_S3_FILE_UPLOAD_REQUEST', getenv('GK_AUTH_TOKEN_DROP_S3_FILE_UPLOAD_REQUEST') ?: '');

        // External services
        define('GK_CROWDIN_URL', getenv('GK_CROWDIN_URL') ?: 'https://crowdin.geokrety.org');

        // Admin services
        define('ADMIN_SERVICE_ADMINER_URL', getenv('ADMIN_SERVICE_ADMINER_URL') ?: GK_SITE_BASE_SERVER_URL.'/adminer');
        define('ADMIN_SERVICE_PGADMIN_URL', getenv('ADMIN_SERVICE_PGADMIN_URL') ?: GK_SITE_BASE_SERVER_URL.'/pgadmin');
        define('ADMIN_SERVICE_GRAFANA_URL', getenv('ADMIN_SERVICE_GRAFANA_URL') ?: GK_SITE_BASE_SERVER_URL.'/grafana');
        define('ADMIN_SERVICE_PROMETHEUS_URL', getenv('ADMIN_SERVICE_PROMETHEUS_URL') ?: GK_SITE_BASE_SERVER_URL.'/prometheus');
        define('ADMIN_SERVICE_RABBIT_MQ_URL', getenv('ADMIN_SERVICE_RABBIT_MQ_URL') ?: 'https://rabbitmq.'.GK_SITE_BASE_SERVER_FQDN);

        // Environment
        define('GK_INSTANCE_NAME', getenv('GK_INSTANCE_NAME') ?: 'dev');
        define('GK_ENVIRONMENT', getenv('GK_ENVIRONMENT') ?: 'dev');
        define('GK_DEPLOY_DATE', getenv('GK_DEPLOY_DATE') ?: 'unknown');
        define('GK_IS_PRODUCTION', GK_ENVIRONMENT === 'prod');
        if (GK_IS_PRODUCTION) {
            define('GK_DEBUG', false);
            define('GK_F3_DEBUG', false);
            define('GK_DEVEL', false);
            define('GK_HELP_GEOKRETY_EXAMPLE_1', getenv('GK_HELP_GEOKRETY_EXAMPLE_1') ?: 46657);
            define('GK_HELP_GEOKRETY_EXAMPLE_2', getenv('GK_HELP_GEOKRETY_EXAMPLE_2') ?: 65509);
        } else {
            define('GK_DEBUG', getenv('GK_DEBUG') ? filter_var(getenv('GK_DEBUG'), FILTER_VALIDATE_BOOLEAN) : false);
            define('GK_F3_DEBUG', getenv('GK_F3_DEBUG') ? filter_var(getenv('GK_F3_DEBUG'), FILTER_VALIDATE_BOOLEAN) : true);
            define('GK_DEVEL', getenv('GK_DEVEL') ? filter_var(getenv('GK_DEVEL'), FILTER_VALIDATE_BOOLEAN) : false);
            define('GK_HELP_GEOKRETY_EXAMPLE_1', getenv('GK_HELP_GEOKRETY_EXAMPLE_1') ?: 1);
            define('GK_HELP_GEOKRETY_EXAMPLE_2', getenv('GK_HELP_GEOKRETY_EXAMPLE_2') ?: 2);
        }
        define('GK_APP_NAME', getenv('GK_APP_NAME') ?: 'www');
        define('GK_APP_VERSION', getenv('GIT_COMMIT') ?: 'undef');
        define('GK_EMAIL_SUBJECT_PREFIX', getenv('GK_EMAIL_SUBJECT_PREFIX') ?: '[GeoKrety]');
        define('GK_BOT_USERNAME', getenv('GK_BOT_USERNAME') ?: 'GeoKrety Bot ');
        define('GK_SITE_USER_AGENT', getenv('GK_SITE_USER_AGENT') ?: sprintf('GeoKrety/%s (%s)', GK_APP_VERSION, GK_ENVIRONMENT));

        define('GK_HELP_GEOKRETY_EXAMPLE_LIST', [GK_HELP_GEOKRETY_EXAMPLE_1, GK_HELP_GEOKRETY_EXAMPLE_2]);

        // DATABASE config
        define('GK_DB_ENGINE', getenv('GK_DB_ENGINE') ?: 'pgsql');
        define('GK_DB_HOST', getenv('GK_DB_HOST') ?: 'postgres');
        define('GK_DB_NAME', getenv('GK_DB_NAME') ?: 'geokrety');
        define('GK_DB_USER', getenv('GK_DB_USER') ?: 'geokrety');
        define('GK_DB_SCHEMA', getenv('GK_DB_SCHEMA') ?: 'geokrety');
        define('GK_DB_PASSWORD', getenv('GK_DB_PASSWORD') ?: 'geokrety');
        define('GK_DB_DSN', getenv('GK_DB_DSN') ?: sprintf('%s:host=%s;dbname=%s;user=%s;password=%s', GK_DB_ENGINE, GK_DB_HOST, GK_DB_NAME, GK_DB_USER, GK_DB_PASSWORD));

        define('GK_DB_DATETIME_FORMAT', 'Y-m-d H:i:sP');
        define('GK_DB_DATETIME_FORMAT_MICROSECONDS', 'Y-m-d H:i:s.uP');
        define('GK_DB_DATETIME_FORMAT_WITHOUT_TZ', 'Y-m-d H:i:s');
        define('GK_DB_DATETIME_FORMAT_AS_INT', 'YmdHis');

        define('GK_DB_GPG_PASSWORD', getenv('GK_DB_GPG_PASSWORD') ?: 'geokrety');
        define('GK_DB_SECRET_KEY', getenv('GK_DB_SECRET_KEY') ?: 'secretkey');

        // SMTP config
        define('GK_SMTP_HOST', getenv('GK_SMTP_HOST') ?: 'smtp-relay');
        define('GK_SMTP_PORT', getenv('GK_SMTP_PORT') ?: 25);
        define('GK_SMTP_SCHEME', getenv('GK_SMTP_SCHEME') ?: '');
        define('GK_SMTP_URI', getenv('GK_SMTP_URI') ?: sprintf('%s:%d', GK_SMTP_HOST, GK_SMTP_PORT));
        define('GK_SMTP_USER', getenv('GK_SMTP_USER') ?: '');
        define('GK_SMTP_PASSWORD', getenv('GK_SMTP_PASSWORD') ?: '');

        // HASHING seeds
        define('GK_PASSWORD_MIN_HASH_ROTATION', 10);
        define('GK_PASSWORD_MAX_HASH_ROTATION', 99);
        define('GK_PASSWORD_HASH_ROTATION', getenv('GK_PASSWORD_HASH_ROTATION') ?: 11);
        define('GK_PASSWORD_HASH', getenv('GK_PASSWORD_HASH') ?: 'geokrety');
        define('GK_PASSWORD_SEED', getenv('GK_PASSWORD_SEED') ?: 'rand_string');

        // F3
        define('GK_F3_UI', getenv('GK_F3_UI') ?: 'app-ui/');
        define('GK_F3_TMP', getenv('GK_F3_TMP') ?: '/tmp/f3/');
        define('GK_F3_LOGS', getenv('GK_F3_LOGS') ?: '/tmp/f3/logs/');
        define('GK_REDIS_HOST', getenv('GK_REDIS_HOST') ?: 'redis');
        define('GK_REDIS_PORT', getenv('GK_REDIS_PORT') ?: '6379');
        define('GK_F3_CACHE', getenv('GK_F3_CACHE') ?: sprintf('redis=%s:%s', GK_REDIS_HOST, GK_REDIS_PORT));
        define('GK_GETTEXT_BINDTEXTDOMAIN_PATH', getenv('GK_GETTEXT_BINDTEXTDOMAIN_PATH') ?: '../app/languages');
        define('GK_F3_ASSETS_PUBLIC', 'assets/compressed/');

        // Smarty
        define('GK_SMARTY_TEMPLATES_DIR', getenv('GK_SMARTY_TEMPLATES_DIR') ?: '../app-templates/smarty/');
        define('GK_SMARTY_PLUGINS_DIR', getenv('GK_SMARTY_PLUGINS_DIR') ?: '../app-templates/smarty/plugins/');
        define('GK_SMARTY_COMPILE_DIR', getenv('GK_SMARTY_COMPILE_DIR') ?: '/tmp/smarty/compile/');
        define('GK_SMARTY_CACHE_DIR', getenv('GK_SMARTY_CACHE_DIR') ?: '/tmp/smarty/cache/');

        // Labels
        define('GK_LABELS_SVG2PNG_SCHEME', getenv('GK_LABELS_SVG2PNG_SCHEME') ?: 'http');
        define('GK_LABELS_SVG2PNG_HOST', getenv('GK_LABELS_SVG2PNG_HOST') ?: 'svg-to-png');
        define('GK_LABELS_SVG2PNG_PORT', getenv('GK_LABELS_SVG2PNG_PORT') ?: '8080');
        define('GK_LABELS_SVG2PNG_URL', getenv('GK_LABELS_SVG2PNG_URL') ?: sprintf('%s://%s:%s/', GK_LABELS_SVG2PNG_SCHEME, GK_LABELS_SVG2PNG_HOST, GK_LABELS_SVG2PNG_PORT));
        define('GK_LABELS_GENERATE_MAX', getenv('GK_LABELS_GENERATE_MAX') ?: 10);

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

        // Google Recaptcha
        if (!GK_DEVEL) {
            define('GK_GOOGLE_RECAPTCHA_PUBLIC_KEY', getenv('GK_GOOGLE_RECAPTCHA_PUBLIC_KEY') ?: false);
            define('GK_GOOGLE_RECAPTCHA_SECRET_KEY', getenv('GK_GOOGLE_RECAPTCHA_SECRET_KEY') ?: false);
            define('GK_GOOGLE_RECAPTCHA_JS_URL', getenv('GK_GOOGLE_RECAPTCHA_JS_URL') ?: 'https://www.google.com/recaptcha/api.js');
        } else {
            define('GK_GOOGLE_RECAPTCHA_PUBLIC_KEY', false);
            define('GK_GOOGLE_RECAPTCHA_SECRET_KEY', false);
            define('GK_GOOGLE_RECAPTCHA_JS_URL', '');
        }

        // OpAuth
        define('GK_OPAUTH_SECURITY_SALT', getenv('GK_OPAUTH_SECURITY_SALT') ?: false);
        define('GK_OPAUTH_GOOGLE_CLIENT_ID', getenv('GK_OPAUTH_GOOGLE_CLIENT_ID') ?: false);
        define('GK_OPAUTH_GOOGLE_CLIENT_SECRET', getenv('GK_OPAUTH_GOOGLE_CLIENT_SECRET') ?: false);
        define('GK_OPAUTH_FACEBOOK_CLIENT_ID', getenv('GK_OPAUTH_FACEBOOK_CLIENT_ID') ?: false);
        define('GK_OPAUTH_FACEBOOK_CLIENT_SECRET', getenv('GK_OPAUTH_FACEBOOK_CLIENT_SECRET') ?: false);

        // go2geo url
        define('GK_SERVICE_GO2GEO_URL', getenv('GK_SERVICE_GO2GEO_URL') ?: 'https://geokrety.org/go2geo/?wpt=%s');
        define('GK_SERVICE_GC_SEARCH_NEAREST_URL', getenv('GK_SERVICE_GC_SEARCH_NEAREST_URL') ?: 'https://www.geocaching.com/seek/nearest.aspx?origin_lat=%f&origin_long=%f&dist=1');

        // // Waypoint services
        // define('OC_PL', 'OC_PL');
        // define('OC_DE', 'OC_DE');
        // define('OC_UK', 'OC_UK');
        // define('OC_US', 'OC_US');
        // define('OC_NL', 'OC_NL');
        // define('OC_RO', 'OC_RO');

        define('GK_WAYPOINT_SYNC_SEND_WAYPOINT_ERRORS', getenv('GK_WAYPOINT_SYNC_SEND_WAYPOINT_ERRORS') ?: false);

        define('GK_WAYPOINT_SERVICE_URL_OC_PL', getenv('GK_WAYPOINT_SERVICE_URL_OC_PL') ?: 'https://opencaching.pl');
        define('GK_WAYPOINT_SERVICE_URL_OC_DE', getenv('GK_WAYPOINT_SERVICE_URL_OC_DE') ?: 'https://www.opencaching.de');
        define('GK_WAYPOINT_SERVICE_URL_OC_UK', getenv('GK_WAYPOINT_SERVICE_URL_OC_UK') ?: 'https://opencache.uk');
        define('GK_WAYPOINT_SERVICE_URL_OC_US', getenv('GK_WAYPOINT_SERVICE_URL_OC_US') ?: 'https://www.opencaching.us');
        define('GK_WAYPOINT_SERVICE_URL_OC_NL', getenv('GK_WAYPOINT_SERVICE_URL_OC_NL') ?: 'https://www.opencaching.nl');
        define('GK_WAYPOINT_SERVICE_URL_OC_RO', getenv('GK_WAYPOINT_SERVICE_URL_OC_RO') ?: 'https://www.opencaching.ro');
        define('GK_WAYPOINT_SERVICE_URL_WPG', getenv('GK_WAYPOINT_SERVICE_URL_OC_WPG') ?: 'http://wpg.alleycat.pl');
        define('GK_WAYPOINT_SERVICE_URL_GEODASHING', getenv('GK_WAYPOINT_SERVICE_URL_GEODASHING') ?: 'http://geodashing.gpsgames.org');
        define('GK_WAYPOINT_SERVICE_URL_GPS_GAMES', getenv('GK_WAYPOINT_SERVICE_URL_GPS_GAMES') ?: 'http://geocaching.gpsgames.org');
        define('GK_WAYPOINT_SERVICE_URL_GC_SU', getenv('GK_WAYPOINT_SERVICE_URL_GC_SU') ?: 'https://geocaching.su');
        define('GK_WAYPOINT_SERVICE_URL_GC_HU', getenv('GK_WAYPOINT_SERVICE_URL_GC_HU') ?: 'https://www.geocaching.hu');
        define('GK_WAYPOINT_SERVICE_URL_GC', getenv('GK_WAYPOINT_SERVICE_URL_GC') ?: 'https://www.geocaching.com');

        define('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_PL', getenv('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_PL') ?: 5);
        define('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_DE', getenv('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_DE') ?: 5);
        define('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_UK', getenv('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_UK') ?: 5);
        define('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_US', getenv('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_US') ?: 5);
        define('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_NL', getenv('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_NL') ?: 5);
        define('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_RO', getenv('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_RO') ?: 5);
        define('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_WPG', getenv('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_OC_WPG') ?: 1440);
        define('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_GEODASHING', getenv('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_GEODASHING') ?: date('t') * 24 * 60);
        define('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_GPS_GAMES', getenv('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_GPS_GAMES') ?: 1440);
        define('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_GC_SU', getenv('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_GC_SU') ?: 120);
        define('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_GC_HU', getenv('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_GC_HU') ?: 1440);

        // okapi services
        define('GK_OKAPI_CONSUMER_KEY_OC_PL', getenv('GK_OKAPI_CONSUMER_KEY_OC_PL') ?: null);
        define('GK_OKAPI_CONSUMER_KEY_OC_DE', getenv('GK_OKAPI_CONSUMER_KEY_OC_DE') ?: null);
        define('GK_OKAPI_CONSUMER_KEY_OC_UK', getenv('GK_OKAPI_CONSUMER_KEY_OC_UK') ?: null);
        define('GK_OKAPI_CONSUMER_KEY_OC_US', getenv('GK_OKAPI_CONSUMER_KEY_OC_US') ?: null);
        define('GK_OKAPI_CONSUMER_KEY_OC_NL', getenv('GK_OKAPI_CONSUMER_KEY_OC_NL') ?: null);
        define('GK_OKAPI_CONSUMER_KEY_OC_RO', getenv('GK_OKAPI_CONSUMER_KEY_OC_RO') ?: null);

        define('GK_OKAPI_PARTNERS', [
            'OC_DE' => ['key' => GK_OKAPI_CONSUMER_KEY_OC_DE, 'url' => GK_WAYPOINT_SERVICE_URL_OC_DE],
            'OC_PL' => ['key' => GK_OKAPI_CONSUMER_KEY_OC_PL, 'url' => GK_WAYPOINT_SERVICE_URL_OC_PL],
            'OC_UK' => ['key' => GK_OKAPI_CONSUMER_KEY_OC_UK, 'url' => GK_WAYPOINT_SERVICE_URL_OC_UK],
            'OC_US' => ['key' => GK_OKAPI_CONSUMER_KEY_OC_US, 'url' => GK_WAYPOINT_SERVICE_URL_OC_US],
            'OC_NL' => ['key' => GK_OKAPI_CONSUMER_KEY_OC_NL, 'url' => GK_WAYPOINT_SERVICE_URL_OC_NL],
            // 'OC_RO' => ['key' => GK_OKAPI_CONSUMER_KEY_OC_RO, 'url' => GK_WAYPOINT_SERVICE_URL_OC_RO],
        ]);

        define('GK_METRICS_EXCLUDE_PATH', [
            '/cron',
            '/health',
            '/metrics',
        ]);

        // Audit logs
        define('GK_AUDIT_LOGS_EXCLUDE_PATH_BYPASS', !GK_IS_PRODUCTION && filter_var(getenv('GK_AUDIT_LOGS_EXCLUDE_PATH_BYPASS'), FILTER_VALIDATE_BOOLEAN));
        define('GK_AUDIT_LOGS_EXCLUDE_PATH', [
            '/auth',
        ]);
        define('GK_AUDIT_LOGS_EXCLUDE_RETENTION_DAYS', getenv('GK_AUDIT_LOGS_EXCLUDE_RETENTION_DAYS') ?: 90);
        define('GK_AUDIT_POST_EXCLUDE_RETENTION_DAYS', getenv('GK_AUDIT_POST_EXCLUDE_RETENTION_DAYS') ?: 90);
        define('GK_USER_AUTHENTICATION_HISTORY_RETENTION_DAYS', getenv('GK_USER_AUTHENTICATION_HISTORY_RETENTION_DAYS') ?: 120);

        // User
        define('GK_USER_DELETED_USERNAME', getenv('GK_USER_DELETED_USERNAME') ?: 'Deleted user');

        // map api url
        define('GK_MAP_URL', getenv('GK_MAP_URL') ?: 'https://api.geokretymap.org');
        define('GK_MAP_DEFAULT_ZOOM', getenv('GK_MAP_DEFAULT_ZOOM') ?: 4);
        define('GK_MAP_DEFAULT_ZOOM_USER_HOME', getenv('GK_MAP_DEFAULT_ZOOM_USER_HOME') ?: 8);
        define('GK_MAP_DEFAULT_LAT', getenv('GK_MAP_DEFAULT_LAT') ?: 42.941);
        define('GK_MAP_DEFAULT_LON', getenv('GK_MAP_DEFAULT_LON') ?: 2.109);

        define('GK_OSM_STATIC_MAPS_URI', getenv('GK_OSM_STATIC_MAPS_URI') ?: 'http://osm-static-maps:3000');

        // Home LATEST COUNTS
        define('GK_HOME_COUNT_NEWS', getenv('GK_HOME_COUNT_NEWS') ?: 3);
        define('GK_HOME_COUNT_MOVES', getenv('GK_HOME_COUNT_MOVES') ?: 10);
        define('GK_HOME_COUNT_RECENT_GEOKRETY', getenv('GK_HOME_COUNT_RECENT_GEOKRETY') ?: 10);
        define('GK_HOME_COUNT_RECENT_PICTURES', getenv('GK_HOME_COUNT_RECENT_PICTURES') ?: 18);

        // PAGINATION LIMITS
        define('GK_PAGINATION_NEWS', getenv('GK_PAGINATION_NEWS') ?: 10);
        define('GK_PAGINATION_GEOKRET_MOVES', getenv('GK_PAGINATION_GEOKRET_MOVES') ?: 10);
        define('GK_PAGINATION_GEOKRET_MOVES_MAP', getenv('GK_PAGINATION_GEOKRET_MOVES_MAP') ?: 500);
        define('GK_PAGINATION_USER_INVENTORY', getenv('GK_PAGINATION_USER_INVENTORY') ?: 10);
        define('GK_PAGINATION_USER_OWNED_GEOKRETY', getenv('GK_PAGINATION_USER_OWNED_GEOKRETY') ?: 10);
        define('GK_PAGINATION_USER_WATCHED_GEOKRETY', getenv('GK_PAGINATION_USER_WATCHED_GEOKRETY') ?: 10);
        define('GK_PAGINATION_USER_RECENT_MOVES', getenv('GK_PAGINATION_USER_RECENT_MOVES') ?: 10);
        define('GK_PAGINATION_USER_OWNED_GEOKRETY_RECENT_MOVES', getenv('GK_PAGINATION_USER_OWNED_GEOKRETY_RECENT_MOVES') ?: 10);
        define('GK_PAGINATION_USER_PICTURES_GALLERY', getenv('GK_PAGINATION_USER_PICTURES_GALLERY') ?: 24);
        define('GK_PAGINATION_USER_OWNED_PICTURES_GALLERY', getenv('GK_PAGINATION_USER_OWNED_PICTURES_GALLERY') ?: 24);
        define('GK_PAGINATION_PICTURES_GALLERY', getenv('GK_PAGINATION_PICTURES_GALLERY') ?: 36);
        define('GK_PAGINATION_SEARCH_BY_GEOKRET', getenv('GK_PAGINATION_SEARCH_BY_GEOKRET') ?: 25);
        define('GK_PAGINATION_SEARCH_BY_USER', getenv('GK_PAGINATION_SEARCH_BY_USER') ?: 20);
        define('GK_PAGINATION_SEARCH_BY_WAYPOINT', getenv('GK_PAGINATION_SEARCH_BY_WAYPOINT') ?: 50);
        define('GK_PAGINATION_ADMIN_USER_SEARCH', getenv('GK_PAGINATION_ADMIN_USER_SEARCH') ?: 10);

        // TTL LIMITS
        define('GK_SITE_CACHE_TTL_WAYPOINT', getenv('GK_SITE_CACHE_TTL_WAYPOINT') ?: (GK_DEVEL ? 0 : 3600));
        define('GK_SITE_CACHE_TTL_STATS', getenv('GK_SITE_CACHE_TTL_STATS') ?: (GK_DEVEL ? 0 : 600));
        define('GK_SITE_CACHE_TTL_LATEST_NEWS', getenv('GK_SITE_CACHE_TTL_LATEST_NEWS') ?: (GK_DEVEL ? 0 : 60));
        define('GK_SITE_CACHE_TTL_LATEST_MOVED_GEOKRETY', getenv('GK_SITE_CACHE_TTL_LATEST_MOVED_GEOKRETY') ?: (GK_DEVEL ? 0 : 60));
        define('GK_SITE_CACHE_TTL_LATEST_GEOKRETY', getenv('GK_SITE_CACHE_TTL_LATEST_GEOKRETY') ?: (GK_DEVEL ? 0 : 60));
        define('GK_SITE_CACHE_TTL_LATEST_PICTURES', getenv('GK_SITE_CACHE_TTL_LATEST_PICTURES') ?: (GK_DEVEL ? 0 : 60));
        define('GK_SITE_CACHE_TTL_PICTURE_CAPTION', getenv('GK_SITE_CACHE_TTL_PICTURE_CAPTION') ?: (GK_DEVEL ? 0 : 600));
        define('GK_SITE_CACHE_TTL_LABELS_LIST', getenv('GK_SITE_CACHE_TTL_LABELS_LIST') ?: (GK_DEVEL ? 0 : 600));
        define('GK_SITE_CACHE_TTL_LABELS_LOOKUP', getenv('GK_SITE_CACHE_TTL_LABELS_LOOKUP') ?: (GK_DEVEL ? 0 : 600));
        define('GK_SITE_CACHE_TTL_SOCIAL_AUTH_PROVIDERS', getenv('GK_SITE_CACHE_TTL_SOCIAL_AUTH_PROVIDERS') ?: (GK_DEVEL ? 0 : 600));
        define('GK_SITE_CACHE_TTL_SITE_DEFAULT_SETTINGS', getenv('GK_SITE_CACHE_TTL_SITE_DEFAULT_SETTINGS') ?: (GK_DEVEL ? 0 : 600));

        // JS TIMEOUTS
        define('GK_OBSERVATION_AREA_RADIUS_TIMEOUT', getenv('GK_OBSERVATION_AREA_RADIUS_TIMEOUT') ?: (GK_DEVEL ? 500 : 100));
        define('GK_PICTURE_UPLOAD_REFRESH_TIMEOUT', getenv('GK_PICTURE_UPLOAD_REFRESH_TIMEOUT') ?: (GK_DEVEL ? 2000 : 500));

        // API LIMITS
        define('GK_API_EXPORT_PASSWORD_BYPASS_LIMIT_DAYS', getenv('GK_API_EXPORT_PASSWORD_BYPASS_LIMIT_DAYS') ?: 'geokrety');
        define('GK_API_EXPORT_LIMIT_DAYS', getenv('GK_API_EXPORT_LIMIT_DAYS') ?: 10);
        define('GK_API_EXPORT_SURFACE_LIMIT', getenv('GK_API_EXPORT_SURFACE_LIMIT') ?: 252000);
        define('GK_API_EXPORT_GEOKRET_DETAILS_MOVES_LIMIT', getenv('GK_GKT_SEARCH_DISTANCE_LIMITGK_API_EXPORT_GEOKRET_DETAILS_MOVES_LIMIT') ?: 10);
        define('GK_GKT_SEARCH_DISTANCE_LIMIT', getenv('GK_GKT_SEARCH_DISTANCE_LIMIT') ?: 30); // meters around the original position

        // ITEMS LIMITS
        define('GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS', getenv('GK_CHECK_TRACKING_CODE_MAX_PROCESSED_ITEMS') ?: 10);
        define('GK_CHECK_WAYPOINT_MIN_LENGTH', getenv('GK_CHECK_WAYPOINT_MIN_LENGTH') ?: 4);
        define('GK_CHECK_WAYPOINT_MAX_LENGTH', getenv('GK_CHECK_WAYPOINT_MAX_LENGTH') ?: 20);
        define('GK_CHECK_WAYPOINT_NAME_MIN_LENGTH', getenv('GK_CHECK_WAYPOINT_NAME_MIN_LENGTH') ?: 4);
        define('GK_CHECK_WAYPOINT_NAME_MAX_LENGTH', getenv('GK_CHECK_WAYPOINT_NAME_MAX_LENGTH') ?: 20);
        define('GK_CHECK_WAYPOINT_NAME_COUNT', getenv('GK_CHECK_WAYPOINT_NAME_COUNT') ?: 10);

        // SIZES
        define('GK_SITE_TRACKING_CODE_MIN_LENGTH', (int) getenv('GK_SITE_TRACKING_CODE_MIN_LENGTH') ?: 6);
        define('GK_SITE_TRACKING_CODE_MAX_LENGTH', (int) getenv('GK_SITE_TRACKING_CODE_MAX_LENGTH') ?: 7);
        define('GK_GEOKRET_NAME_MIN_LENGTH', getenv('GK_GEOKRET_NAME_MIN_LENGTH') ?: 4);
        define('GK_GEOKRET_NAME_MAX_LENGTH', getenv('GK_GEOKRET_NAME_MAX_LENGTH') ?: 75);
        define('GK_NEWS_TITLE_MAX_LENGTH', getenv('GK_NEWS_TITLE_MAX_LENGTH') ?: 128);
        define('GK_USERNAME_MIN_LENGTH', getenv('GK_USERNAME_MIN_LENGTH') ?: 3);
        define('GK_USERNAME_MAX_LENGTH', getenv('GK_USERNAME_MAX_LENGTH') ?: 80);
        define('GK_PICTURE_CAPTION_MAX_LENGTH', getenv('GK_PICTURE_CAPTION_MAX_LENGTH') ?: 50);
        define('GK_MOVE_COMMENT_MAX_LENGTH', getenv('GK_MOVE_COMMENT_MAX_LENGTH') ?: 5120);

        // GeoKrety generator
        define('GK_GENERATOR_MAX_COUNT', getenv('GK_GENERATOR_MAX_COUNT') ?: 100);
        define('GK_GENERATOR_TRACKING_CODE_ALPHABET', getenv('GK_GENERATOR_TRACKING_CODE_ALPHABET') ?: 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789');
        define('GK_GENERATOR_OWNER_CODE_ALPHABET', getenv('GK_GENERATOR_OWNER_CODE_ALPHABET') ?: '0123456789');
        define('GK_GENERATOR_SECID_ALPHABET', getenv('GK_GENERATOR_SECID_ALPHABET') ?: 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        define('GK_GENERATOR_TRACKING_CODE_PREFIX_REGEX', getenv('GK_GENERATOR_TRACKING_CODE_PREFIX_REGEX') ?: '[a-np-zA-NP-Z1-9]*');
        define('GK_GENERATOR_TRACKING_CODE_SUFFIX_REGEX', getenv('GK_GENERATOR_TRACKING_CODE_SUFFIX_REGEX') ?: '[a-np-zA-NP-Z1-9]*');
        define('GK_TRACKING_CODE_GENERATE_MAX_TRIES', getenv('GK_TRACKING_CODE_GENERATE_MAX_TRIES') ?: 150);
        define('GK_GENERATOR_CACHE_RESULT_TTL', getenv('GK_GENERATOR_CACHE_RESULT_TTL') ?: 60);

        // Rate Limits
        define('GK_RATE_LIMITS_BYPASS', getenv('GK_RATE_LIMITS_BYPASS') ?: 'geokrety');
        define('GK_RATE_LIMITS', [
            'API_LEGACY_MOVE_POST' => [1500, 60 * 60 * 24], // 1500/day
            'API_LEGACY_PICTURE_PROXY' => [5000, 60 * 60 * 24], // 5000/day
            'API_V1_CHECK_RATE_LIMIT' => [250, 60 * 60 * 24], // 250/day
            'API_V1_LOGIN_2_SECID' => [25, 60 * 60 * 24], // 25/day
            'API_V1_EXPORT2' => [1500, 60 * 60 * 24], // 1500/day
            'API_V1_EXPORT' => [12, 60], // 12/minute
            'API_V1_EXPORT_OC' => [12, 60], // 12/minute
            'API_V1_REQUEST_S3_FILE_SIGNATURE' => [50, 60 * 60 * 24], // 50/day
            'API_GKT_V3_SEARCH' => [10000, 60 * 60 * 24], // 10000/day
            'API_GKT_V3_INVENTORY' => [1500, 60 * 60 * 24], // 1500/day
            'USERNAME_CHANGE' => [3, 60 * 60 * 24 * 28], // 3/month
        ]);

        // PIWIK
        define('GK_PIWIK_URL', getenv('GK_PIWIK_URL') ?: null);
        define('GK_PIWIK_SITE_ID', (int) getenv('GK_PIWIK_SITE_ID') ?: null);
        define('GK_PIWIK_TOKEN', getenv('GK_PIWIK_TOKEN') ?: null);
        define('GK_PIWIK_CONNECT_TIMEOUT_MS', getenv('GK_PIWIK_CONNECT_TIMEOUT_MS') ?: 400);
        define('GK_PIWIK_ENABLED',
            !empty(GK_PIWIK_URL)
            && !empty(GK_PIWIK_SITE_ID)
            && !empty(GK_PIWIK_TOKEN)
            && (
                GK_DEBUG
                || !filter_var(\Base::instance()->get('HEADERS.Dnt'), FILTER_VALIDATE_BOOLEAN)
                || !filter_var(\Base::instance()->get('GET.DNT'), FILTER_VALIDATE_BOOLEAN)
            )
        );

        // CDN
        define('GK_CDN_SERVER_URL', getenv('GK_CDN_SERVER_URL') ?: 'https://cdn.geokrety.org');

        define('GK_CDN_GPGKEY_URL', getenv('GK_CDN_GPGKEY_URL') ?: GK_CDN_SERVER_URL.'/geokrety.org.pub');
        define('GK_CDN_GPGKEY_ID', getenv('GK_CDN_GPGKEY_ID') ?: '76B00039');

        define('GK_CDN_CSS_URL', getenv('GK_CDN_CSS_URL') ?: GK_CDN_SERVER_URL.'/css');
        define('GK_CDN_IMAGES_URL', getenv('GK_CDN_IMAGES_URL') ?: GK_CDN_SERVER_URL.'/images');
        define('GK_CDN_ICONS_URL', getenv('GK_CDN_ICONS_URL') ?: GK_CDN_IMAGES_URL.'/icons');
        define('GK_CDN_LOGOS_URL', getenv('GK_CDN_LOGOS_URL') ?: GK_CDN_IMAGES_URL.'/logos');
        define('GK_CDN_PINS_ICONS_URL', getenv('GK_CDN_PINS_ICONS_URL') ?: GK_CDN_IMAGES_URL.'/icons/pins');
        define('GK_CDN_LABELS_SCREENSHOTS_URL', getenv('GK_CDN_LABELS_SCREENSHOTS_URL') ?: GK_CDN_IMAGES_URL.'/labels/screenshots');
        define('GK_AVATAR_DEFAULT_URL', getenv('GK_AVATAR_DEFAULT_URL') ?: '/assets/images/the-mole-grey.svg');

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

        define('GK_CDN_JQUERY_JS', getenv('GK_CDN_JQUERY_JS') ?: GK_CDN_LIBRARIES_URL.'/jquery/jquery-3.3.1.min.js');

        define('GK_CDN_BOOTSTRAP_JS', getenv('GK_CDN_BOOTSTRAP_JS') ?: GK_CDN_LIBRARIES_URL.'/bootstrap/3.3.7/js/bootstrap.min.js');
        define('GK_CDN_BOOTSTRAP_CSS', getenv('GK_CDN_BOOTSTRAP_CSS') ?: GK_CDN_LIBRARIES_URL.'/bootstrap/3.3.7/css/bootstrap.min.css');

        define('GK_CDN_FONT_AWESOME_CSS', getenv('GK_CDN_FONT_AWESOME_CSS') ?: GK_CDN_LIBRARIES_URL.'/font-awesome/4.7.0/css/font-awesome.min.css');
        define('GK_CDN_FLAG_ICON_CSS', getenv('GK_CDN_FLAG_ICON_CSS') ?: GK_CDN_CSS_URL.'/flag-icon.min.css');

        define('GK_CDN_BOOTSTRAP_MAXLENGTH_JS', getenv('GK_CDN_BOOTSTRAP_MAXLENGTH_JS') ?: GK_CDN_LIBRARIES_URL.'/bootstrap-maxlength/1.7.0/bootstrap-maxlength.min.js');
        define('GK_CDN_PREVIEW_IMAGE_JQUERY_JS', getenv('GK_CDN_PREVIEW_IMAGE_JQUERY_JS') ?: GK_CDN_LIBRARIES_URL.'/preview-image-jquery/1.0/preview-image.min.js');

        //        define('GK_CDN_MOMENT_JS', getenv('GK_CDN_MOMENT_JS') ?: GK_CDN_LIBRARIES_URL.'/moment.js/2.22.0/moment.min.js');
        define('GK_CDN_MOMENT_JS', getenv('GK_CDN_MOMENT_JS') ?: GK_CDN_LIBRARIES_URL.'/moment.js/2.24.0/moment-with-locales.min.js');
        define('GK_CDN_MOMENT_TIMEZONE_JS', getenv('GK_CDN_MOMENT_TIMEZONE_JS') ?: GK_CDN_LIBRARIES_URL.'/moment-timezone/0.5.31/moment-timezone-with-data.min.js');
        define('GK_CDN_BOOTSTRAP_DATETIMEPICKER_JS', getenv('GK_CDN_BOOTSTRAP_DATETIMEPICKER_JS') ?: GK_CDN_LIBRARIES_URL.'/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js');
        define('GK_CDN_BOOTSTRAP_DATETIMEPICKER_CSS', getenv('GK_CDN_BOOTSTRAP_DATETIMEPICKER_CSS') ?: GK_CDN_LIBRARIES_URL.'/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css');

        define('GK_CDN_LEAFLET_JS', getenv('GK_CDN_LEAFLET_JS') ?: GK_CDN_LIBRARIES_URL.'/leaflet/1.7.1/leaflet.js');
        define('GK_CDN_LEAFLET_CSS', getenv('GK_CDN_LEAFLET_CSS') ?: GK_CDN_LIBRARIES_URL.'/leaflet/1.7.1/leaflet.css');
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
        define('GK_CDN_LEAFLET_HOTLINE_JS', getenv('GK_CDN_LEAFLET_HOTLINE_JS') ?: GK_CDN_LIBRARIES_URL.'/Leaflet.hotline/0.4.0/leaflet.hotline.js');

        define('GK_CDN_SPIN_JS', getenv('GK_CDN_SPIN_JS') ?: GK_CDN_LIBRARIES_URL.'/spin.js/2.3.2/spin.min.js');

        define('GK_CDN_LATINIZE_JS', getenv('GK_CDN_LATINIZE_JS') ?: GK_CDN_LIBRARIES_URL.'/latinize/0.4.0/latinize.min.js');

        define('GK_CDN_BOOTSTRAP_3_TYPEAHEAD_JS', getenv('GK_CDN_BOOTSTRAP_3_TYPEAHEAD_JS') ?: GK_CDN_LIBRARIES_URL.'/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.js');

        define('GK_CDN_ZXCVBN_JS', getenv('GK_CDN_ZXCVBN_JS') ?: GK_CDN_LIBRARIES_URL.'/zxcvbn/4.4.2/zxcvbn.min.js');
        define('GK_CDN_STRENGTHIFY_JS', getenv('GK_CDN_STRENGTHIFY_JS') ?: GK_CDN_LIBRARIES_URL.'/strengthify/0.5.8/jquery.strengthify.min.js');
        define('GK_CDN_STRENGTHIFY_CSS', getenv('GK_CDN_STRENGTHIFY_CSS') ?: GK_CDN_LIBRARIES_URL.'/strengthify/0.5.8/strengthify.min.css');

        define('GK_CDN_DATATABLE_JS', getenv('GK_CDN_DATATABLE_JS') ?: GK_CDN_LIBRARIES_URL.'/datatables/1.12.1/datatables.js');
        define('GK_CDN_DATATABLE_CSS', getenv('GK_CDN_DATATABLE_CSS') ?: GK_CDN_LIBRARIES_URL.'/datatables/1.12.1/datatables.css');
        define('GK_CDN_DATATABLE_I18N', getenv('GK_CDN_DATATABLE_I18N') ?: GK_CDN_LIBRARIES_URL.'/datatables/1.12.1/i18n/%s.json');

        define('GK_CDN_DROPZONE_JS', getenv('GK_CDN_DROPZONE_JS') ?: GK_CDN_LIBRARIES_URL.'/dropzone/5.9.3/dropzone.js');

        define('GK_CDN_MAGNIFIC_POPUP_JS', getenv('GK_CDN_MAGNIFIC_POPUP_JS') ?: GK_CDN_LIBRARIES_URL.'/magnific-popup/1.1.0/jquery.magnific-popup.min.js');
        define('GK_CDN_MAGNIFIC_POPUP_CSS', getenv('GK_CDN_MAGNIFIC_POPUP_CSS') ?: GK_CDN_LIBRARIES_URL.'/magnific-popup/1.1.0/magnific-popup.css');

        define('GK_CDN_ANIMATE_CSS', getenv('GK_CDN_ANIMATE_CSS') ?: GK_CDN_LIBRARIES_URL.'/animate.css/4.1.1/animate.min.css');

        define('GK_CDN_SELECT2_JS', getenv('GK_CDN_SELECT2_JS') ?: GK_CDN_LIBRARIES_URL.'/select2/4.1.0-rc.0/js/select2.min.js');
        define('GK_CDN_SELECT2_CSS', getenv('GK_CDN_SELECT2_CSS') ?: GK_CDN_LIBRARIES_URL.'/select2/4.1.0-rc.0/css/select2.min.css');

        define('GK_CDN_D3_JS', getenv('GK_CDN_D3_JS') ?: GK_CDN_LIBRARIES_URL.'/d3/v7.4.3/d3.min.js');
        define('GK_CDN_D3_QUEUE_JS', getenv('GK_CDN_D3_QUEUE_JS') ?: GK_CDN_LIBRARIES_URL.'/d3-queue/v3.0.7/d3-queue.min.js');
        define('GK_CDN_D3_PLOT_JS', getenv('GK_CDN_D3_PLOT_JS') ?: GK_CDN_LIBRARIES_URL.'/d3-plot/v0.4.3/plot.umd.min.js');
        define('GK_CDN_D3_PATH_JS', getenv('GK_CDN_D3_PATH_JS') ?: GK_CDN_LIBRARIES_URL.'/d3-path/v3.0.1/d3-path.js');
        define('GK_CDN_D3_SHAPE_JS', getenv('GK_CDN_D3_SHAPE_JS') ?: GK_CDN_LIBRARIES_URL.'/d3-shape/v3.1.0/d3-shape.js');

        $this->clearEnvironments();
    }

    public static function printEnvironments() {
        $text = '';
        foreach ($_ENV as $key => $value) {
            $text .= "* **$key** -> $value\n";
        }
        echo \Markdown::instance()->convert($text);
    }

    public function clearEnvironments() {
        $f3 = \Base::instance();
        if (preg_match('/^\/cron/', $f3->PATH)) {
            // Skip cleaning env in cron context
            return;
        }
        foreach ($_ENV as $key => $value) {
            if (substr($key, 0, 3) !== 'GK_') {
                continue;
            }
            $this->_clearEnvironment($f3, $key);
        }
        $to_clean = ['MINIO_ACCESS_KEY', 'MINIO_SECRET_KEY', 'GPG_KEYS'];
        foreach ($to_clean as $key) {
            $this->_clearEnvironment($f3, $key);
        }
        $f3->sync('ENV');
    }

    private function _clearEnvironment(\Base $f3, string $key) {
        putenv($key);
        $f3->clear('ENV.'.$key);
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
