<?php

require_once 'templates/konfig.php';
require_once 'wybierz_jezyk.php'; // language selection
require_once SMARTY_DIR.'Smarty.class.php';
require_once 'longin_chceck.php';

require_once 'check_email_validity.php';

function create_tmp_dir($path) {
    if (!file_exists($path) && !mkdir($path, 0750, true)) {
        die("Fail to creating $path directory");
    }
}
create_tmp_dir(TEMP_DIR_SMARTY_COMPILE);
create_tmp_dir(TEMP_DIR_SMARTY_CACHE);
create_tmp_dir(TEMP_DIR_HTMLPURIFIER_CACHE);

$HTMLPurifierconfig_conf = \HTMLPurifier_Config::createDefault();
$HTMLPurifierconfig_conf->set('Cache.SerializerPath', TEMP_DIR_HTMLPURIFIER_CACHE);
$HTMLPurifier = new \HTMLPurifier($HTMLPurifierconfig_conf);
foreach ($_GET as $key => $value) {
    $_GET[$key] = $HTMLPurifier->purify(html_entity_decode($value));
}
foreach ($_POST as $key => $value) {
    $_POST[$key] = $HTMLPurifier->purify(html_entity_decode($value));
}

$smarty_cache_id = basename($_SERVER['SCRIPT_NAME']);
$smarty_cache_filename = $smarty_cache_id.$lang.$template_login;

$smarty = new SmartyBC();
$smarty->escape_html = true;
$smarty->template_dir = './templates/smarty/';
$smarty->compile_dir = TEMP_DIR_SMARTY_COMPILE;
$smarty->cache_dir = TEMP_DIR_SMARTY_CACHE;
$smarty->addPluginsDir('./templates/plugins/');
if (amIOnProd()) {
    $smarty->compile_check = false; // use smarty_admin.php to clear compiled templates when necessary - http://www.smarty.net/docsv2/en/variable.compile.check.tpl
} else {
    $smarty->compile_check = true;
}
$smarty->assign('content_template', false); // Store included template name
$smarty->assign('css', array()); // Store dynamic css filename to load
$smarty->assign('javascript', array()); // Store dynamic javascript filename to load
$smarty->assign('js_template', array()); // Store dynamic javascript filename to load as javascript content
$smarty->assign('jquery', array()); // Store page jquery
$smarty->assign('isSuperUser', false);
$smarty->registerClass('Carbon', '\Carbon\Carbon');

// DEBUG DO NOT MERGE THAT
        $smarty->clear_all_cache();
        $smarty->clear_compiled_tpl();
// DEBUG DO NOT MERGE THAT

$smarty->assign('site_welcome', $config['welcome']);
$smarty->assign('site_punchline', $config['punchline']);
$smarty->assign('site_intro', $config['intro']);
$smarty->assign('site_keywords', $config['keywords']);

$smarty->assign('geokrety_types', $cotozakret);
$smarty->assign('geokrety_moves_types', $cotozalog);
// $smarty->assign('languages', $config_jezyk_nazwa);

$longin_status = longin_chceck();
if ($longin_status['plain'] != null) {
    $userid_longin = $longin_status['userid'];
    $smarty->assign('currentUser', $longin_status['userid']);
    $smarty->assign('isLoggedIn', true);
    $_SESSION['currentUser'] = $longin_status['userid'];
    $_SESSION['isLoggedIn'] = true;
    if (in_array($longin_status['userid'], $config['superusers'])) {
        $smarty->assign('isSuperUser', true);
        $_SESSION['isSuperUser'] = true;
    }
} else {
    $smarty->assign('currentUser', false);
    $smarty->assign('isLoggedIn', false);
    $smarty->assign('isSuperUser', false);
    $_SESSION['currentUser'] = false;
    $_SESSION['isLoggedIn'] = false;
    $_SESSION['isSuperUser'] = false;
}

if (isset($_GET['template']) && $_GET['template'] == 'm') {
    $template = 'krety-m.html';
} else {
    $template = 'krety.tpl';
}

// DEBUG kumy
$smarty_cache_this_page = 0;
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
