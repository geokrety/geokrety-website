<?php

// config file for GeoKrety
$config['prod_server_name'] = isset($_ENV['PROD_SERVER_NAME']) ? $_ENV['PROD_SERVER_NAME'] : 'geokrety.org';
$config['adres'] = isset($_ENV['SERVER_URL']) ? $_ENV['SERVER_URL'] : $config['prod_server_name'];
$config['gk_version'] = isset($_ENV['GK_VERSION']) ? $_ENV['GK_VERSION'] : 'dev';
$config['gk_app_name'] = isset($_ENV['GK_APP_NAME']) ? $_ENV['GK_APP_NAME'] : 'www';

// MySQL config
$config['host'] = isset($_ENV['DB_HOSTNAME']) ? $_ENV['DB_HOSTNAME'] : 'db';
$config['username'] = isset($_ENV['DB_USERNAME']) ? $_ENV['DB_USERNAME'] : 'xxx';
$config['pass'] = isset($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : 'xxx';
$config['db'] = isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'xxx';
$config['charset'] = isset($_ENV['DB_CHARSET']) ? $_ENV['DB_CHARSET'] : 'utf8';

// Redis
$config['redis_dsn'] = isset($_ENV['REDIS_DSN']) ? $_ENV['REDIS_DSN'] : 'tcp://redis:6379';

// Session
$config['session_in_redis'] = isset($_ENV['SESSION_IN_REDIS']) ? filter_var($_ENV['SESSION_IN_REDIS'], FILTER_VALIDATE_BOOLEAN) : false;

// CDN url
$config['cdn_url'] = isset($_ENV['CDN_SERVER_URL']) ? $_ENV['CDN_SERVER_URL'] : 'https://cdn.geokrety.org';

// Google map Api key
$GOOGLE_MAP_KEY = isset($_ENV['GOOGLE_MAP_KEY']) ? $_ENV['GOOGLE_MAP_KEY'] : 'xxx';

// reCaptcha Api key
if (isset($_ENV['GOOGLE_RECAPTCHA_PUBLIC_KEY'])) {
    $GOOGLE_RECAPTCHA_PUBLIC_KEY = $_ENV['GOOGLE_RECAPTCHA_PUBLIC_KEY'];
}
if (isset($_ENV['GOOGLE_RECAPTCHA_SECRET_KEY'])) {
    $GOOGLE_RECAPTCHA_SECRET_KEY = $_ENV['GOOGLE_RECAPTCHA_SECRET_KEY'];
}

// Password hashing
// Crypt alorythms https://en.wikipedia.org/wiki/Crypt_(C)#Key_derivation_functions_supported_by_crypt
$config['sol'] = isset($_ENV['PASSWORD_HASH']) ? $_ENV['PASSWORD_HASH'] : '$5$xxx'; // crypt() hash
$config['sol2'] = isset($_ENV['PASSWORD_HASH_LEGACY']) ? $_ENV['PASSWORD_HASH_LEGACY'] : 'xxx'; // some random string

// Api2login hashes
$config['md5_string1'] = isset($_ENV['API2LOGIN_MD5_STR1']) ? $_ENV['API2LOGIN_MD5_STR1'] : 'xxx'; // hex chars
$config['md5_string2'] = isset($_ENV['API2LOGIN_MD5_STR2']) ? $_ENV['API2LOGIN_MD5_STR2'] : 'xxx'; // hex chars

// Cryptographic vectors
$config['swistak_key'] = isset($_ENV['SWISTAK_KEY']) ? $_ENV['SWISTAK_KEY'] : 'xxx'; // some random string
$config['swistak_iv32'] = isset($_ENV['SWISTAK_IV32']) ? $_ENV['SWISTAK_IV32'] : 'xxx'; // 32 hex chars

// dodajniusa access
$config['news_password'] = isset($_ENV['NEWS_PASSWORD']) ? $_ENV['NEWS_PASSWORD'] : 'xxx'; // some random string

// export bypass
$kocham_kaczynskiego = isset($_ENV['EXPORT_BYPASS_TOKEN']) ? $_ENV['EXPORT_BYPASS_TOKEN'] : 'xxx'; // some random string

// jRating access token
$config['jrating_token'] = isset($_ENV['JRATING_TOKEN']) ? $_ENV['JRATING_TOKEN'] : 'xxx'; // some random string

// Delay between each message (rate limit inter-users messages)
$config['mail_rate_limit'] = isset($_ENV['MAIL_RATE_LIMIT']) ? $_ENV['MAIL_RATE_LIMIT'] : 15;

// admin users
$config['superusers'] = array('1', '6262', '26422');

// Email gateway
$config['pop_hostname'] = isset($_ENV['POP_HOSTNAME']) ? $_ENV['POP_HOSTNAME'] : 'pop.gmail.com';
$config['pop_port'] = isset($_ENV['POP_PORT']) ? $_ENV['POP_PORT'] : 995;
$config['pop_tls'] = isset($_ENV['POP_TLS']) ? filter_var($_ENV['POP_TLS'], FILTER_VALIDATE_BOOLEAN) : true;
$config['pop_username'] = isset($_ENV['POP_USERNAME']) ? $_ENV['POP_USERNAME'] : 'xxx';
$config['pop_password'] = isset($_ENV['POP_PASSWORD']) ? $_ENV['POP_PASSWORD'] : 'xxx';
$config['support_mail'] = isset($_ENV['SUPPORT_MAIL']) ? $_ENV['SUPPORT_MAIL'] : 'xxx';

// Sentry integration
$config['sentry_dsn'] = isset($_ENV['SENTRY_DSN']) ? $_ENV['SENTRY_DSN'] : 'https://xx:yyy@zzz/1';
$config['sentry_env'] = isset($_ENV['SENTRY_ENV']) ? $_ENV['SENTRY_ENV'] : 'development';

// Piwik conf
$config['piwik_url'] = isset($_ENV['PIWIK_URL']) ? $_ENV['PIWIK_URL'] : '';
$config['piwik_site_id'] = isset($_ENV['PIWIK_SITE_ID']) ? $_ENV['PIWIK_SITE_ID'] : '';
$config['piwik_token'] = isset($_ENV['PIWIK_TOKEN']) ? $_ENV['PIWIK_TOKEN'] : '';

// Partners
$config['cache_wpt_link_gc'] = 'https://www.geocaching.com/seek/cache_details.aspx?wp=';
$config['cache_wpt_link_n'] = 'http://www.navicache.com/cgi-bin/db/displaycache2.pl?CacheID=';

// map api url
$config['geokrety_map_url'] = isset($_ENV['GEOKRETY_MAP_URL']) ? $_ENV['GEOKRETY_MAP_URL'] : 'https://api.geokretymap.org';
$config['geokrety_map_default_params'] = isset($_ENV['GEOKRETY_MAP_DEFAULT_PARAMS']) ? $_ENV['GEOKRETY_MAP_DEFAULT_PARAMS'] : '#2/42.941/2.109/1/1/0/0/90/';
define('GEOKRETY_MAP_URL', $config['geokrety_map_url']);
define('GEOKRETY_MAP_DEFAULT_PARAMS', $config['geokrety_map_default_params']);

// input validation
$config['waypointy_min_length'] = 4;

// generated files
$config['obrazki'] = 'obrazki/';
$config['obrazki-male'] = 'obrazki-male/';
$config['obrazki-skasowane'] = 'obrazki-dowonu/';
$config['wykresy'] = 'templates/wykresy/';
$config['mapki'] = 'mapki/';
$config['generated'] = 'files/';

// cdn paths
$config['cdn_images'] = $config['cdn_url'].'/images';
$config['cdn_banners'] = $config['cdn_images'].'/banners';
$config['cdn_logos'] = $config['cdn_images'].'/logos/';
$config['cdn_obrazki'] = $config['cdn_images'].'/obrazki';
$config['cdn_obrazki_male'] = $config['cdn_images'].'/obrazki-male';
$config['cdn_icons'] = $config['cdn_images'].'/icons';
$config['cdn_log_icons'] = $config['cdn_images'].'/log-icons';
$config['cdn_pins'] = $config['cdn_icons'].'/pins';
$config['cdn_api_icon_16'] = $config['cdn_images'].'/api/icons/16';
$config['cdn_country_codes'] = $config['cdn_images'].'/country-codes';
$config['cdn_libraries'] = $config['cdn_url'].'/libraries';
$config['cdn_js'] = $config['cdn_url'].'/js';
$config['cdn_css'] = $config['cdn_url'].'/css';
$config['cdn_maps'] = $config['cdn_url'].'/maps';

define('CONFIG_SITE_BASE_URL', $config['adres']);
define('CONFIG_CDN_URL', $config['cdn_url']);

define('CONFIG_PROD_SERVER_NAME', $config['prod_server_name']);
define('CONFIG_GK_APP_NAME', $config['gk_app_name']);
define('CONFIG_GK_VERSION', $config['gk_version']);
define('CONFIG_CDN', $config['cdn_url']);
define('CONFIG_CDN_IMAGES', $config['cdn_images']);
define('CONFIG_CDN_BANNERS', $config['cdn_banners']);
define('CONFIG_CDN_LOGOS', $config['cdn_logos']);
define('CONFIG_CDN_OBRAZKI', $config['cdn_obrazki']);
define('CONFIG_CDN_OBRAZKI_MALE', $config['cdn_obrazki_male']);
define('CONFIG_CDN_ICONS', $config['cdn_icons']);
define('CONFIG_CDN_LOG_ICONS', $config['cdn_log_icons']);
define('CONFIG_CDN_PINS_ICONS', $config['cdn_pins']);
define('CONFIG_CDN_API_ICONS', $config['cdn_api_icon_16']);
define('CONFIG_CDN_COUNTRY_FLAGS', $config['cdn_country_codes']);
define('CONFIG_CDN_LIBRARIES', $config['cdn_libraries']);
define('CONFIG_CDN_JS', $config['cdn_js']);
define('CONFIG_CDN_CSS', $config['cdn_css']);
define('CONFIG_CDN_MAPS', $config['cdn_maps']);

// Some configs
define('CHECK_NR_MAX_PROCESSED_ITEMS', isset($_ENV['CHECK_NR_MAX_PROCESSED_ITEMS']) ? $_ENV['CHECK_NR_MAX_PROCESSED_ITEMS'] : 10);

//js
$config['funkcje.js'] = '/funkcje.js';
$config['ajaxtooltip.js'] = CONFIG_CDN_LIBRARIES.'/ajaxtooltip/ajaxtooltip-1.min.js';
$config['securimage'] = 'templates/libraries/securimage-3.6.7/';
define('CDN_BOOTSTRAP_DATEPICKER_JS', CONFIG_CDN_LIBRARIES.'/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js');
define('CDN_BOOTSTRAP_DATEPICKER_CSS', CONFIG_CDN_LIBRARIES.'/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css');

define('CDN_BOOTSTRAP_DATETIMEPICKER_JS', CONFIG_CDN_LIBRARIES.'/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js');
define('CDN_BOOTSTRAP_DATETIMEPICKER_CSS', CONFIG_CDN_LIBRARIES.'/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css');

define('CDN_BOOTSTRAP_3_TYPEAHEAD_JS', CONFIG_CDN_LIBRARIES.'/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.js');

define('CDN_BOOTSTRAP_TAGSINPUT_JS', CONFIG_CDN_LIBRARIES.'/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.js');
define('CDN_BOOTSTRAP_TAGSINPUT_CSS', CONFIG_CDN_LIBRARIES.'/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css');

define('CDN_MOMENT_JS', CONFIG_CDN_LIBRARIES.'/moment.js/2.24.0/moment-with-locales.min.js');
define('CDN_LATINIZE_JS', CONFIG_CDN_LIBRARIES.'/latinize/0.4.0/latinize.min.js');

define('CDN_COLORBOX_JS', CONFIG_CDN_LIBRARIES.'/colorbox/1.6.4/jquery.colorbox-min.js');
define('CDN_COLORBOX_CSS', CONFIG_CDN_LIBRARIES.'/colorbox/colorbox-1.min.css');

define('CDN_SIMPLEMDE_JS', CONFIG_CDN_LIBRARIES.'/inscrybmde/1.11.6/inscrybmde.min.js');
define('CDN_SIMPLEMDE_CSS', CONFIG_CDN_LIBRARIES.'/inscrybmde/1.11.6/inscrybmde.min.css');

define('CDN_PRISM_CSS', CONFIG_CDN_LIBRARIES.'/prism/1.16.0/prism.min.css');
define('CDN_PRISM_JS', CONFIG_CDN_LIBRARIES.'/prism/1.16.0/prism.min.js');
define('CDN_PRISM_MARKUP_TEMPLATING_JS', CONFIG_CDN_LIBRARIES.'/prism/1.16.0/prism-markup-templating.min.js');
define('CDN_PRISM_PHP_JS', CONFIG_CDN_LIBRARIES.'/prism/1.16.0/prism-php.min.js');

define('CDN_LEAFLET_JS', CONFIG_CDN_LIBRARIES.'/leaflet/1.4.0/leaflet.js');
define('CDN_LEAFLET_CSS', CONFIG_CDN_LIBRARIES.'/leaflet/1.4.0/leaflet.css');
define('CDN_LEAFLET_CENTERCROSS_JS', CONFIG_CDN_LIBRARIES.'/Leaflet.CenterCross/0.0.8/leaflet.CenterCross.js');
define('CDN_LEAFLET_AJAX_JS', CONFIG_CDN_LIBRARIES.'/leaflet-ajax/2.1.0/leaflet.ajax.min.js');
define('CDN_LEAFLET_MARKERCLUSTER_JS', CONFIG_CDN_LIBRARIES.'/Leaflet.markercluster/1.4.1/leaflet.markercluster.js');
define('CDN_LEAFLET_MARKERCLUSTER_CSS', CONFIG_CDN_LIBRARIES.'/Leaflet.markercluster/1.4.1/MarkerCluster.css');
define('CDN_LEAFLET_MARKERCLUSTER_DEFAULT_CSS', CONFIG_CDN_LIBRARIES.'/Leaflet.markercluster/1.4.1/MarkerCluster.Default.css');
define('CDN_LEAFLET_GEOKRETYFILTER_JS', CONFIG_CDN_LIBRARIES.'/Leaflet.geokretyfilter/leaflet.Control.GeoKretyFilter.js');
define('CDN_LEAFLET_GEOKRETYFILTER_CSS', CONFIG_CDN_LIBRARIES.'/Leaflet.geokretyfilter/leaflet.Control.GeoKretyFilter.css');
define('CDN_LEAFLET_PLUGIN_BING_JS', CONFIG_CDN_LIBRARIES.'/leaflet-bing/Bing.js');
define('CDN_LEAFLET_NOUISLIDER_JS', CONFIG_CDN_LIBRARIES.'/noUiSlider/8.1.0/nouislider.min.js');
define('CDN_LEAFLET_NOUISLIDER_CSS', CONFIG_CDN_LIBRARIES.'/noUiSlider/8.1.0/nouislider.min.css');
define('CDN_LEAFLET_SPIN_JS', CONFIG_CDN_LIBRARIES.'/leaflet.spin.js/leaflet.spin.js');
define('CDN_LEAFLET_FULLSCREEN_JS', CONFIG_CDN_LIBRARIES.'/leaflet-fullscreen/v0.0.4/Leaflet.fullscreen.min.js');
define('CDN_LEAFLET_FULLSCREEN_CSS', CONFIG_CDN_LIBRARIES.'/leaflet-fullscreen/v0.0.4/leaflet.fullscreen.css');

define('CDN_SPIN_JS', CONFIG_CDN_LIBRARIES.'/spin.js/2.3.2/spin.min.js');

define('CDN_SLIDEOUT_JS', CONFIG_CDN_LIBRARIES.'/slideout/1.0.1/slideout.min.js');

define('CDN_JQUERY_VALIDATION_JS', CONFIG_CDN_LIBRARIES.'/jquery-validate/1.19.0/jquery.validate.js');
define('CDN_PARSLEY_BOOTSTRAP3_JS', CONFIG_CDN_LIBRARIES.'/parsley.js/parsleyjs-bootstrap3.js');
define('CDN_PARSLEY_JS', CONFIG_CDN_LIBRARIES.'/parsley.js/2.9.1/parsley.js');
define('CDN_PARSLEY_CSS', CONFIG_CDN_LIBRARIES.'/parsley.js/2.9.1/parsley.css');

define('CDN_BOOTSTRAP_SLIDER_JS', CONFIG_CDN_LIBRARIES.'/bootstrap-slider/10.6.1/bootstrap-slider.min.js');
define('CDN_BOOTSTRAP_SLIDER_CSS', CONFIG_CDN_LIBRARIES.'/bootstrap-slider/10.6.1/bootstrap-slider.min.css');

define('CDN_ZXCVBN_JS', CONFIG_CDN_LIBRARIES.'/zxcvbn/4.4.2/zxcvbn.min.js');
define('CDN_STRENGTHIFY_JS', CONFIG_CDN_LIBRARIES.'/strengthify/0.5.8/jquery.strengthify.min.js');
define('CDN_STRENGTHIFY_CSS', CONFIG_CDN_LIBRARIES.'/strengthify/0.5.8/strengthify.min.css');

// Default timezone
$config['timezone'] = isset($_ENV['TIMEZONE']) ? $_ENV['TIMEZONE'] : 'GMT';

// Temp directories
define('TEMP_DIR_SMARTY_COMPILE', isset($_ENV['TEMP_DIR_SMARTY_COMPILE']) ? $_ENV['TEMP_DIR_SMARTY_COMPILE'] : '/tmp/templates/compile/');
define('TEMP_DIR_SMARTY_CACHE', isset($_ENV['TEMP_DIR_SMARTY_CACHE']) ? $_ENV['TEMP_DIR_SMARTY_CACHE'] : '/tmp/templates/cache/');
define('TEMP_DIR_HTMLPURIFIER_CACHE', isset($_ENV['TEMP_DIR_HTMLPURIFIER_CACHE']) ? $_ENV['TEMP_DIR_HTMLPURIFIER_CACHE'] : '/tmp/htmlpurifier/cache/');

// Reverse geocoders
define('GOOGLE_MAP_KEY', $GOOGLE_MAP_KEY);
define('SERVICE_REVERSE_COUNTRY_GEOCODER', $_ENV['SERVICE_REVERSE_COUNTRY_GEOCODER'] ?? 'https://geo.geokrety.org/api/getCountry?lat=%s&lon=%s');
define('SERVICE_REVERSE_COUNTRY_GEOCODER_GOOGLE', $_ENV['SERVICE_REVERSE_COUNTRY_GEOCODER_GOOGLE'] ?? 'https://maps.googleapis.com/maps/api/geocode/json?latlng=%s,%s&key=%s');
define('SERVICE_ELEVATION_GEOCODER', $_ENV['SERVICE_ELEVATION_GEOCODER'] ?? 'https://geo.geokrety.org/api/getElevation?lat=%s&lon=%s');
define('SERVICE_ELEVATION_GEOCODER_GOOGLE', $_ENV['SERVICE_ELEVATION_GEOCODER_GOOGLE'] ?? 'https://maps.googleapis.com/maps/api/elevation/json?locations=%s,%s&key=%s');

// Side services
define('SERVICE_GO2GEO', $_ENV['SERVICE_GO2GEO'] ?? 'https://geokrety.org/go2geo/?wpt=%s');

// Smarty (composer install)
define('SMARTY_DIR', 'vendor/smarty/smarty/libs/');

// language .po directory
define('BINDTEXTDOMAIN_PATH', __DIR__.'/../rzeczy/lang');

set_include_path(__DIR__.'/..');

//http://pl.wiktionary.org/wiki/Wikis%C5%82ownik:Kody_j%C4%99zyk%C3%B3w
// pierwszy niech będzie angielski jako difoltowy!
$config_jezyk_encoding['en'] = 'en_US.UTF-8';
$config_jezyk_encoding['bg'] = 'bg_BG.UTF-8';
$config_jezyk_encoding['ca'] = 'ca_ES.UTF-8';
$config_jezyk_encoding['cn'] = 'zh_CN.UTF-8';
$config_jezyk_encoding['zh'] = 'zh_CN.UTF-8';
$config_jezyk_encoding['cz'] = 'cs_CZ.UTF-8';
$config_jezyk_encoding['cs'] = 'cs_CZ.UTF-8';
$config_jezyk_encoding['de'] = 'de_DE.UTF-8';
$config_jezyk_encoding['dk'] = 'da_DK.UTF-8';
$config_jezyk_encoding['da'] = 'da_DK.UTF-8';
$config_jezyk_encoding['el'] = 'el_GR.UTF-8';
$config_jezyk_encoding['es'] = 'es_ES.UTF-8';
$config_jezyk_encoding['et'] = 'et_EE.UTF-8';
$config_jezyk_encoding['fi'] = 'fi_FI.UTF-8';
$config_jezyk_encoding['fr'] = 'fr_FR.UTF-8';
$config_jezyk_encoding['hu'] = 'hu_HU.UTF-8';
$config_jezyk_encoding['it'] = 'it_IT.UTF-8';
$config_jezyk_encoding['lt'] = 'lt_LT.UTF-8';
$config_jezyk_encoding['lv'] = 'lv_LV.UTF-8';
$config_jezyk_encoding['nl'] = 'nl_NL.UTF-8';
$config_jezyk_encoding['ph'] = 'ph_TH.UTF-8';
$config_jezyk_encoding['pl'] = 'pl_PL.UTF-8';
$config_jezyk_encoding['pt'] = 'pt_PT.UTF-8';
$config_jezyk_encoding['ro'] = 'ro_RO.UTF-8';
$config_jezyk_encoding['ru'] = 'ru_RU.UTF-8';
$config_jezyk_encoding['sk'] = 'sk_SK.UTF-8';
$config_jezyk_encoding['sq'] = 'sq_AL.UTF-8';
$config_jezyk_encoding['sv'] = 'sv_SE.UTF-8';
$config_jezyk_encoding['th'] = 'th_TH.UTF-8';
$config_jezyk_encoding['tr'] = 'tr_TR.UTF-8';
$config_jezyk_encoding['uk'] = 'uk_UA.UTF-8';
$config_jezyk_encoding['tl'] = 'tl_PH.UTF-8';
$config_jezyk_encoding['zu'] = 'zu_ZA.UTF-8';
//$config_jezyk_encoding[''] = '.UTF-8';

$config_jezyk_nazwa['en'] = 'English';
$config_jezyk_nazwa['bg'] = 'Български';
$config_jezyk_nazwa['ca'] = 'Català';
$config_jezyk_nazwa['cn'] = 'Chinese';
// $config_jezyk_nazwa['cz'] = 'Česky'; // cz is not valid ISO 639-1 Code !!!
$config_jezyk_nazwa['cs'] = 'Česky';
$config_jezyk_nazwa['de'] = 'Deutsch';
$config_jezyk_nazwa['dk'] = 'Dansk';
$config_jezyk_nazwa['el'] = 'Ελληνικά';
$config_jezyk_nazwa['es'] = 'Español';
$config_jezyk_nazwa['et'] = 'Eesti';
$config_jezyk_nazwa['fi'] = 'Suomi';
$config_jezyk_nazwa['fr'] = 'Français';
$config_jezyk_nazwa['hu'] = 'Magyar';
$config_jezyk_nazwa['it'] = 'Italiano';
$config_jezyk_nazwa['nl'] = 'Nederlands';
$config_jezyk_nazwa['ph'] = 'Pilipinas';
$config_jezyk_nazwa['pl'] = 'Polski';
$config_jezyk_nazwa['pt'] = 'Português';
$config_jezyk_nazwa['ro'] = 'Română';
$config_jezyk_nazwa['ru'] = 'Русский';
$config_jezyk_nazwa['sk'] = 'Slovenčina';
$config_jezyk_nazwa['sq'] = 'Shqip';
$config_jezyk_nazwa['sv'] = 'Svenska';
$config_jezyk_nazwa['th'] = 'ไทย';
$config_jezyk_nazwa['tr'] = 'Türk';
$config_jezyk_nazwa['uk'] = 'Українська';

// Todo to be refactored using \Geokrety\Domain\LogType
$cotozalog['0'] = _('Dropped to');
$cotozalog['1'] = _('Grabbed from');
$cotozalog['2'] = _('A comment');
$cotozalog['3'] = _('Seen in');
$cotozalog['4'] = _('Archived');
$cotozalog['5'] = _('Dipped in');

$cotozakret['0'] = _('Traditional');
$cotozakret['1'] = _('A book/CD/DVD...');
$cotozakret['2'] = _('A human');
$cotozakret['3'] = _('A coin');
$cotozakret['4'] = _('KretyPost');

define('STATPIC_TEMPLATE_COUNT', 9);    // how many patterns in the statistics with the statistics

$config['home_news_per_page'] = 2;
$config['home_trip_per_page'] = 7;
$config['home_geokrety_per_page'] = 7;
$config['home_online_users_time'] = '5 MINUTE';
$config['geokrety_per_page'] = 25;
$config['trip_per_page'] = 25;
$config['pictures_per_gallery_page'] = 99;
$config['news_per_page'] = 10;
$config['export_day_limit'] = 10;
$config['sql_hard_limit'] = 1000;

$config['welcome'] = _('Welcome to GeoKrety.org!');
$config['punchline'] = _('Open source item tracking for all caching platforms');
$config['intro'] = _('GeoKrety a free online service for object tracking in GPS games like geocaching or opencaching. You can track small items, books, coins, pets or humans with us… <a href="%1">read more</a>.');
$config['keywords'] = 'geokrety,opencaching,geocaching,geocoin,geobook,krety,geokret,geokrets,geocache,gps';

// ---- ---- load konfig files ---- ---- //
if (!getenv('website_config_directory')) {
    $websiteConfigDirectory = dirname(__FILE__);
} else {
    $websiteConfigDirectory = getenv('website_config_directory');
}
//~ make platform dependant config configurable (for tests)
is_file($websiteConfigDirectory.'/konfig-local.php') and require $websiteConfigDirectory.'/konfig-local.php';
is_file($websiteConfigDirectory.'/konfig-mysql.php') and require $websiteConfigDirectory.'/konfig-mysql.php';

//~ keep hard-coded location for static configs
// halloffame credits
@require dirname(__FILE__).DIRECTORY_SEPARATOR.'konfig-credits.php';
// help social groups
@require dirname(__FILE__).DIRECTORY_SEPARATOR.'konfig-groups.php';
// ------------------------------------- //

define('CONFIG_HOST', $config['host']);
define('CONFIG_USERNAME', $config['username']);
define('CONFIG_PASS', $config['pass']);
define('CONFIG_DB', $config['db']);
define('CONFIG_CHARSET', $config['charset']);
define('CONFIG_TIMEZONE', $config['timezone']);

define('REDIS_DSN', $config['redis_dsn']);
define('SESSION_IN_REDIS', $config['session_in_redis']);

if (!function_exists('DBPConnect')) {
    /**
     * Function used by geokrety-scripts.
     *
     * @deprecated : please use GKDB or DBConnect()
     *
     * @return geokrety database link
     */
    function DBPConnect() {
        return GKDB::getLink();
    }
}
if (!function_exists('DBConnect')) {
    /**
     * Shortcut function that rely on GKDB singleton.
     *
     * @return geokrety database link
     */
    function DBConnect() {
        return GKDB::getLink();
    }
}
if (!function_exists('amIOnProd')) {
    /**
     * Function that check if script is executed on prod.
     *
     * @return true if we are on PROD
     */
    function amIOnProd() {
        return isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == CONFIG_PROD_SERVER_NAME;
    }
}
if (!function_exists('gkid')) {
    /**
     * Function that format a GeoKret id to hex notation.
     *
     * @return string
     */
    function gkid($id) {
        return sprintf('GK%04X', $id);
    }
}

if (!function_exists('loginFirst')) {
    /**
     * Function that check login and redirect if necessary.
     * It save the current page for 120s and restore after login.
     *
     * @return string
     */
    function loginFirst() {
        if (!isset($_SESSION['currentUser']) || empty($_SESSION['currentUser'])) {
            // Please login first
            warning('Please login first!');

            include_once 'defektoskop.php';
            errory_add('anonymous - longin_fwd', 4, 'Page:'.$_SERVER['REQUEST_URI']);
            setcookie('longin_fwd', base64_encode($_SERVER['REQUEST_URI']), time() + 120);
            header('Location: /longin.php');
            die();
        }

        return true;
    }
}

if (!function_exists('success')) {
    /**
     * Function that add message to session as success.
     *
     * @return string
     */
    function success($message) {
        $_SESSION['alert_msgs'][] = array(
            'level' => 'success',
            'message' => $message,
        );
    }
}

if (!function_exists('info')) {
    /**
     * Function that add message to session as info.
     *
     * @return string
     */
    function info($message) {
        $_SESSION['alert_msgs'][] = array(
            'level' => 'info',
            'message' => $message,
        );
    }
}

if (!function_exists('warning')) {
    /**
     * Function that add message to session as warning.
     *
     * @return string
     */
    function warning($message) {
        $_SESSION['alert_msgs'][] = array(
            'level' => 'warning',
            'message' => $message,
        );
    }
}

if (!function_exists('danger')) {
    /**
     * Function that add message to session as danger.
     *
     * @return string
     */
    function danger($message, $redirect = false) {
        $_SESSION['alert_msgs'][] = array(
            'level' => 'danger',
            'message' => $message,
        );
        if ($redirect) {
            header('Location: '.(isset($_POST['goto']) ? $_POST['goto'] : '/'));
            die();
        }
    }
}

if (!function_exists('hasErrors')) {
    /**
     * Function that check if errors were already recorded in the session.
     *
     * @return string
     */
    function hasErrors() {
        if (!isset($_SESSION['alert_msgs'])) {
            return false;
        }

        return sizeof(array_filter($_SESSION['alert_msgs'], function ($k) {
            return $k['level'] == 'danger';
        })) > 0;
    }
}

// PROD ONLY: keep only fatal, no more warning
if (amIOnProd()) {
    error_reporting(E_ERROR | E_PARSE);
    define('IS_PROD', true);
} else {
    define('IS_PROD', false);
}

define('REGISTRATION_MAIL_VALIDITY', $_ENV['REGISTRATION_MAIL_VALIDITY'] ?? 5);

define('MOVES_PER_PAGE', $config['trip_per_page']);
define('NEWS_PER_PAGE', $config['news_per_page']);
define('PICTURES_PER_GALLERY_PAGE', $config['pictures_per_gallery_page']);

define('EXPORT_DAY_LIMIT', $config['export_day_limit']);

define('SQL_HARD_LIMIT', $config['sql_hard_limit']);

define('SWISTAK_KEY', $config['swistak_key']);
define('SWISTAK_IV32', $config['swistak_iv32']);

// Email gateway
define('POP_HOSTNAME', $config['pop_hostname']);
define('POP_PORT', $config['pop_port']);
define('POP_TLS', $config['pop_tls']);
define('POP_USERNAME', $config['pop_username']);
define('POP_PASSWORD', $config['pop_password']);

// Sentry integration
define('SENTRY_DSN', $config['sentry_dsn']);
define('SENTRY_ENV', $config['sentry_env']);

// Piwik conf
define('PIWIK_URL', $config['piwik_url']);
define('PIWIK_SITE_ID', $config['piwik_site_id']);
define('PIWIK_TOKEN', $config['piwik_token']);

// Partners
define('CACHE_WPT_LINK_GC', $config['cache_wpt_link_gc']);
define('CACHE_WPT_LINK_N', $config['cache_wpt_link_n']);

// input validation
define('CONFIG_WAYPOINTY_MIN_LENGTH', $config['waypointy_min_length']);

date_default_timezone_set(CONFIG_TIMEZONE);
