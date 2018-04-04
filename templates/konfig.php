<?php

date_default_timezone_set('Europe/Paris');

// config file for GeoKrety إ›ؤ‡إ„أ³إ‚إ¼إ؛ؤ‡
$config['adres'] = 'https://geokrety.org/';
define('CONFIG_CDN', 'https://cdn.geokrety.org');

// Google map Api key
$GOOGLE_MAP_KEY = 'xxx';

// MySQL --
// szukaj-ajax.php has internal config!

$config['sol'] = 'xxx';
$config['sol2'] = 'xxx';    // New storage passwords system

// define('SWISTAK_KEY', 'xxx');
// define('SWISTAK_IV32', 'xxx');

// Api2login hashes
$config['md5_string1'] = 'xxx';
$config['md5_string2'] = 'xxx';

// dodajniusa access
$config['news_password'] = 'xxx';

// export bypass
$kocham_kaczynskiego = 'xxx';

// jRating access token
$config['jrating_token'] = 'xxx';

$config['superusers'] = array('1', '6262', '26422');     // admin users

$config['obrazki'] = 'obrazki/';
$config['obrazki-male'] = 'obrazki-male/';
$config['obrazki-skasowane'] = 'obrazki-dowonu/';
$config['wykresy'] = 'templates/wykresy/';
$config['mapki'] = 'mapki/';
$config['generated'] = 'files/';
define('CONFIG_CDN_IMAGES', CONFIG_CDN.'/images');
define('CONFIG_CDN_ICONS', CONFIG_CDN_IMAGES.'/icons');
define('CONFIG_CDN_LOG_ICONS', CONFIG_CDN_IMAGES.'/log-icons');
define('CONFIG_CDN_PINS_ICONS', CONFIG_CDN_ICONS.'/pins');
define('CONFIG_CDN_API_ICONS', CONFIG_CDN_IMAGES.'/api/icons/16');
define('CONFIG_CDN_COUNTRY_FLAGS', CONFIG_CDN_IMAGES.'/country-codes');
define('CONFIG_CDN_LIBRARIES', CONFIG_CDN.'/libraries');
define('CONFIG_CDN_JS', CONFIG_CDN.'/js');
define('CONFIG_CDN_CSS', CONFIG_CDN.'/css');
define('CONFIG_CDN_MAPS', CONFIG_CDN.'/maps');

// Default timezone
date_default_timezone_set('Europe/Warsaw');
//date.timezone = 'Europe/Warsaw';

//js
$config['funkcje.js'] = '/funkcje.js';
$config['ajaxtooltip.js'] = CONFIG_CDN_LIBRARIES.'/ajaxtooltip/ajaxtooltip-1.min.js';
$config['colorbox.js'] = 'https://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.6.4/jquery.colorbox-min.js';
$config['colorbox.css'] = CONFIG_CDN_LIBRARIES.'/colorbox/colorbox-1.min.css';
define('CDN_BOOTSTRAP_DATEPICKER_JS', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js');
define('CDN_BOOTSTRAP_DATEPICKER_CSS', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker3.min.css');

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

$config_ile_wzorow_banerkow = 9;    // ile wzorأ³w banerkأ³w ze statystykami

@require dirname(__FILE__).'/konfig-local.php';
//this is important, because we may include konfig.php from other directory like /a/b/c/test.php
//in that case it is safest if we point to konfig-local using full path using dirname(__FILE__).

@require dirname(__FILE__).'/konfig-mysql.php';
//this is important, because we may include konfig.php from other directory like /a/b/c/test.php
//in that case it is safest if we point to konfig-local using full path using dirname(__FILE__).

if (!function_exists('DBPConnect')) {
    function DBPConnect()
    {
        $link = mysqli_connect(constant('CONFIG_HOST'), constant('CONFIG_USERNAME'), constant('CONFIG_PASS'));
        if (!$link) {
            $link = mysqli_connect(constant('CONFIG_HOST'), constant('CONFIG_USERNAME'), constant('CONFIG_PASS'));
            if (!$link) {
                die('DB ERROR: '.mysqli_errno($link));
            }
        }
        $link->set_charset(constant('CONFIG_CHARSET'));
        mysqli_select_db($link, constant('CONFIG_DB')) or die('DB ERROR: '.mysqli_errno($link));
        $link->query("SET time_zone = 'Europe/Paris'");

        return $link;
    }
}

if (!function_exists('DBConnect')) {
    function DBConnect()
    {
        $link = mysqli_connect(constant('CONFIG_HOST'), constant('CONFIG_USERNAME'), constant('CONFIG_PASS'), constant('CONFIG_DB'));
        if (!$link) {
            $link = mysqli_connect(constant('CONFIG_HOST'), constant('CONFIG_USERNAME'), constant('CONFIG_PASS'), constant('CONFIG_DB'));
            if (!$link) {
                die('DB ERROR: '.mysqli_errno($link));
            }
        }
        $link->set_charset(constant('CONFIG_CHARSET'));
        $link->query("SET time_zone = 'Europe/Paris'");

        return $link;
    }
}
