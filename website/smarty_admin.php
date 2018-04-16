<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$userid = $longin_status['userid'];
if (!in_array($userid, $config['superusers'])) {
    exit;
}

$TYTUL = 'Erase smarty templates';

require 'templates/konfig.php';
require_once SMARTY_DIR.'Smarty.class.php';

$smarty = new Smarty();
$smarty->template_dir = './templates/';
$smarty->compile_dir = './templates/compile/';
$smarty->cache_dir = './templates/cache/';
$smarty->plugins_dir[] = './templates/plugins/';

$TRESC = '';

if (isset($_POST['formname'])) {
    if ($_POST['formname'] == 'clear_all_cache') {
        echo 'Clearing cache ... ';
        $smarty->clear_all_cache();
        sleep(2);
        echo 'DONE<br />';
    }

    if ($_POST['formname'] == 'clear_compiled_tpl') {
        echo 'Clearing compiled templates ... ';
        $smarty->clear_compiled_tpl();
        sleep(2);
        echo 'DONE<br />';
    }
}
$me = $_SERVER['PHP_SELF'];
$TRESC .= '<table>';
$TRESC .= "<tr><td><form action='$me' method='post'><input type='hidden' name='formname' value='clear_all_cache'/><input type='submit' value='clear_all_cache' /></form></td></tr>";
$TRESC .= "<tr><td><form action='$me' method='post'><input type='hidden' name='formname' value='clear_compiled_tpl'/><input type='submit' value='clear_compiled_tpl' /></form></td></tr>";
$TRESC .= '</table>';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
