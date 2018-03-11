<?php

require_once 'db.php'; // modul bazodanowy
require 'templates/konfig.php';
require_once 'wybierz_jezyk.php'; // wybór języka
require_once SMARTY_DIR.'Smarty.class.php';
require_once 'longin_chceck.php';

require_once 'check_email_validity.php';

require_once 'templates/htmlpurifier/library/HTMLPurifier.auto.php';
$HTMLPurifierconfig_conf = HTMLPurifier_Config::createDefault();
$HTMLPurifier = new HTMLPurifier($HTMLPurifierconfig_conf);
foreach ($_GET as $key => $value) {
    $_GET[$key] = $HTMLPurifier->purify($value);
}

$smarty_cache_id = basename($_SERVER['SCRIPT_NAME']);

$longin_status = longin_chceck();
// szybkie i brudne sprawdzenie, czy ktoś jest zalogowany
if ($longin_status['plain'] != null) {
    $template_login = 'krety_logged_in.html';
} else {
    $template_login = 'krety_not_logged_in.html';
}

$smarty_cache_filename = $smarty_cache_id.$lang.$template_login;

$smarty = new Smarty();
$smarty->template_dir = './templates/';
$smarty->compile_dir = './templates/compile/';
$smarty->cache_dir = './templates/cache/';
$smarty->plugins_dir[] = './templates/plugins/';
$smarty->compile_check = false; // use smarty_admin.php to clear compiled templates when necessary - http://www.smarty.net/docsv2/en/variable.compile.check.tpl

if ($_GET['template'] == 'm') {
    $template = 'krety-m.html';
} else {
    $template = 'krety.html';
}

if (($smarty_cache_this_page > 0) and isset($smarty_cache_filename)) {  // czy jest znacznik na stronie, żeby keszowac te strone
    $smarty->caching = 2; // lifetime is per cache - http://www.smarty.net/docsv2/en/variable.cache.lifetime.tpl
    $smarty->cache_lifetime = $smarty_cache_this_page;
    if ($smarty->is_cached($template, $smarty_cache_filename)) {
        $smarty->display($template, $smarty_cache_filename);
        exit;
    }
} else {
    $smarty->caching = 0; // caching is off

    // Enable alert message only if page is not cached.
    $alert_msgs = array();
    if ($longin_status['plain'] != null) {
        $alert_msgs = check_email_validity($longin_status['userid'], $alert_msgs);
    }
}
