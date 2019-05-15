<?php

// config file for GeoKrety
$config['prod_server_name'] = isset($_ENV['PROD_SERVER_NAME']) ? $_ENV['PROD_SERVER_NAME'] : 'geokrety.org';
$config['adres'] = isset($_ENV['SERVER_URL']) ? $_ENV['SERVER_URL'] : $config['prod_server_name'];

// MySQL config
$config['host'] = isset($_ENV['DB_HOSTNAME']) ? $_ENV['DB_HOSTNAME'] : 'db';
$config['username'] = isset($_ENV['DB_USERNAME']) ? $_ENV['DB_USERNAME'] : 'xxx';
$config['pass'] = isset($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : 'xxx';
$config['db'] = isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'xxx';
$config['charset'] = isset($_ENV['DB_CHARSET']) ? $_ENV['DB_CHARSET'] : 'utf8';

// CDN url
$config['cdn_url'] = isset($_ENV['CDN_SERVER_URL']) ? $_ENV['CDN_SERVER_URL'] : 'https://cdn.geokrety.org';

// Google map Api key
$GOOGLE_MAP_KEY = isset($_ENV['GOOGLE_MAP_KEY']) ? $_ENV['GOOGLE_MAP_KEY'] : 'xxx';

// reCaptcha Api key
$GOOGLE_RECAPTCHA_PUBLIC_KEY = isset($_ENV['GOOGLE_RECAPTCHA_PUBLIC_KEY']) ? $_ENV['GOOGLE_RECAPTCHA_PUBLIC_KEY'] : 'xxx';
$GOOGLE_RECAPTCHA_SECRET_KEY = isset($_ENV['GOOGLE_RECAPTCHA_SECRET_KEY']) ? $_ENV['GOOGLE_RECAPTCHA_SECRET_KEY'] : 'xxx';

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
$config['pop_tls'] = isset($_ENV['POP_TLS']) ? $_ENV['POP_TLS'] : true;
$config['pop_username'] = isset($_ENV['POP_USERNAME']) ? $_ENV['POP_USERNAME'] : 'xxx';
$config['pop_password'] = isset($_ENV['POP_PASSWORD']) ? $_ENV['POP_PASSWORD'] : 'xxx';

// Sentry integration
$config['sentry_dsn'] = isset($_ENV['SENTRY_DSN']) ? $_ENV['SENTRY_DSN'] : 'https://xx:yyy@zzz/1';
$config['sentry_env'] = isset($_ENV['SENTRY_ENV']) ? $_ENV['SENTRY_ENV'] : 'development';

// Piwik conf
$config['piwik_url'] = isset($_ENV['PIWIK_URL']) ? $_ENV['PIWIK_URL'] : '';
$config['piwik_site_id'] = isset($_ENV['PIWIK_SITE_ID']) ? $_ENV['PIWIK_SITE_ID'] : '';
$config['piwik_token'] = isset($_ENV['PIWIK_TOKEN']) ? $_ENV['PIWIK_TOKEN'] : '';

// Partners
$config['geocaching_cache_wp'] = 'https://www.geocaching.com/seek/cache_details.aspx?wp=';

// map api url
$config['geokrety_map_url'] = isset($_ENV['GEOKRETY_MAP_URL']) ? $_ENV['GEOKRETY_MAP_URL'] : 'https://api.geokretymap.org';
define('GEOKRETY_MAP_URL', $config['geokrety_map_url']);

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

define('CONFIG_PROD_SERVER_NAME', $config['prod_server_name']);
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

//js
$config['funkcje.js'] = '/funkcje.js';
$config['ajaxtooltip.js'] = CONFIG_CDN_LIBRARIES.'/ajaxtooltip/ajaxtooltip-1.min.js';
$config['securimage'] = 'templates/libraries/securimage-3.6.7/';
define('CDN_BOOTSTRAP_DATEPICKER_JS', CONFIG_CDN_LIBRARIES.'/bootstrap-datepicker/1.8.0/bootstrap-datepicker.min.js');
define('CDN_BOOTSTRAP_DATEPICKER_CSS', CONFIG_CDN_LIBRARIES.' /libs/bootstrap-datepicker/1.8.0/bootstrap-datepicker3.min.css');

define('CDN_COLORBOX_JS', CONFIG_CDN_LIBRARIES.'/colorbox/1.6.4/jquery.colorbox-min.js');
define('CDN_COLORBOX_CSS', CONFIG_CDN_LIBRARIES.'/colorbox/colorbox-1.min.css');

define('CDN_LEAFLET_JS', CONFIG_CDN_LIBRARIES.'/leaflet/1.4.0/leaflet.js');
define('CDN_LEAFLET_CSS', CONFIG_CDN_LIBRARIES.'/leaflet/1.4.0/leaflet.css');
define('CDN_LEAFLET_CENTERCROSS_JS', CONFIG_CDN_LIBRARIES.'/Leaflet.CenterCross/0.0.8/leaflet.CenterCross.js');

define('CDN_SLIDEOUT_JS', CONFIG_CDN_LIBRARIES.'/slideout/1.0.1/slideout.min.js');

define('CDN_JQUERY_VALIDATION_JS', CONFIG_CDN_LIBRARIES.'/jquery-validate/1.19.0/jquery.validate.min.js');

define('CDN_BOOTSTRAP_SLIDER_JS', CONFIG_CDN_LIBRARIES.'/bootstrap-slider/10.6.1/bootstrap-slider.min.js');
define('CDN_BOOTSTRAP_SLIDER_CSS', CONFIG_CDN_LIBRARIES.'/bootstrap-slider/10.6.1/bootstrap-slider.min.css');

define('CDN_ZXCVBN_JS', CONFIG_CDN_LIBRARIES.'/zxcvbn/4.4.2/zxcvbn.min.js');
define('CDN_STRENGTHIFY_JS', CONFIG_CDN_LIBRARIES.'/strengthify/0.5.8/jquery.strengthify.min.js');
define('CDN_STRENGTHIFY_CSS', CONFIG_CDN_LIBRARIES.'/strengthify/0.5.8/strengthify.min.css');

// Default timezone
$config['timezone'] = isset($_ENV['TIMEZONE']) ? $_ENV['TIMEZONE'] : 'Europe/Paris';

// Temp directories
$config['temp_dir_smarty_compile'] = isset($_ENV['TEMP_DIR_SMARTY_COMPILE']) ? $_ENV['TEMP_DIR_SMARTY_COMPILE'] : '/tmp/templates/compile/';
$config['temp_dir_smarty_cache'] = isset($_ENV['TEMP_DIR_SMARTY_CACHE']) ? $_ENV['TEMP_DIR_SMARTY_CACHE'] : '/tmp/templates/cache/';
$config['temp_dir_htmlpurifier_cache'] = isset($_ENV['TEMP_DIR_HTMLPURIFIER_CACHE']) ? $_ENV['TEMP_DIR_HTMLPURIFIER_CACHE'] : '/tmp/htmlpurifier/cache/';

// Smarty
define('SMARTY_DIR', '/usr/share/php/smarty/libs/');

// language .po directory
define('BINDTEXTDOMAIN_PATH', __DIR__.'/../rzeczy/lang');

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
$config_jezyk_nazwa['cz'] = 'Česky';
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
//$config_jezyk_nazwa[''] = '';

// mb_internal_encoding("UTF-8");

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

$config['welcome'] = 'Welcome to GeoKrety.org!';
$config['punchline'] = 'Open source item tracking for all caching platforms';
$config['intro'] = 'This service is similar to TravelBug™ or GeoLutins and aims at tracking things you put to geocache containers… <a href="%1">read more</a>.';
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
            $_SESSION['alert_msgs'][] = array(
                'level' => 'warning',
                'message' => _('Please login first!'),
            );

            include_once 'defektoskop.php';
            errory_add('anonymous - longin_fwd', 4, 'Page:'.$_SERVER['REQUEST_URI']);
            setcookie('longin_fwd', base64_encode($_SERVER['REQUEST_URI']), time() + 120);
            header('Location: /longin.php');
            die();
        }

        return true;
    }
}

// PROD ONLY: keep only fatal, no more warning
if (amIOnProd()) {
    error_reporting(E_ERROR | E_PARSE);
    define('IS_PROD', true);
} else {
    define('IS_PROD', false);
}

define('MOVES_PER_PAGE', $config['trip_per_page']);
define('PICTURES_PER_GALLERY_PAGE', $config['pictures_per_gallery_page']);

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
define('GEOCACHING_CACHE_WP', $config['geocaching_cache_wp']);

// input validation
define('CONFIG_WAYPOINTY_MIN_LENGTH', $config['waypointy_min_length']);

date_default_timezone_set(CONFIG_TIMEZONE);
