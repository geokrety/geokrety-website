<?php

require_once 'db.php'; // modul bazodanowy
require_once 'templates/konfig.php';
require_once 'wybierz_jezyk.php'; // wybór języka
require_once SMARTY_DIR.'Smarty.class.php';
require_once 'longin_chceck.php';

require_once 'check_email_validity.php';

function create_tmp_dir($path) {
    if (!file_exists($path) && !mkdir($path, 0750, true)) {
        die("Fail to creating $path directory");
    }
}
create_tmp_dir($config['temp_dir_smarty_compile']);
create_tmp_dir($config['temp_dir_smarty_cache']);
create_tmp_dir($config['temp_dir_htmlpurifier_cache']);

require_once 'templates/htmlpurifier/library/HTMLPurifier.auto.php';
$HTMLPurifierconfig_conf = \HTMLPurifier_Config::createDefault();
$HTMLPurifierconfig_conf->set('Cache.SerializerPath', $config['temp_dir_htmlpurifier_cache']);
$HTMLPurifier = new HTMLPurifier($HTMLPurifierconfig_conf);
foreach ($_GET as $key => $value) {
    $_GET[$key] = $HTMLPurifier->purify($value);
}

$smarty_cache_id = basename($_SERVER['SCRIPT_NAME']);
$smarty_cache_filename = $smarty_cache_id.$lang.$template_login;

$smarty = new Smarty();
$smarty->template_dir = './templates/';
$smarty->compile_dir = $config['temp_dir_smarty_compile'];
$smarty->cache_dir = $config['temp_dir_smarty_cache'];
$smarty->plugins_dir[] = './templates/plugins/';
$smarty->compile_check = false; // use smarty_admin.php to clear compiled templates when necessary - http://www.smarty.net/docsv2/en/variable.compile.check.tpl

$longin_status = longin_chceck();
// quick and dirty check if someone is logged in
if ($longin_status['plain'] != null) {
    $smarty->assign('isLoggedIn', true);
} else {
    $smarty->assign('isLoggedIn', false);
}

if (isset($_GET['template']) && $_GET['template'] == 'm') {
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
