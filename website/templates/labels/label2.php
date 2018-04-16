<?php

// perform a search śćółżźńóó
require_once 'wybierz_jezyk.php'; // choose the user's language

$kret_id = $_POST['id'];
// autopoprawione...
$kret_nazwa = $_POST['nazwa'];
// autopoprawione...
$kret_opis = $_POST['opis'];
// autopoprawione...
$kret_owner = $_POST['owner'];
// autopoprawione...
$kret_szablon = $_POST['szablon'];
// autopoprawione...
$kret_tracking = $_POST['tracking'];
// autopoprawione...import_request_variables('p', 'kret_');

define('SMARTY_DIR', '/usr/share/php/smarty/libs/');
require_once SMARTY_DIR.'Smarty.class.php';

$smarty = new Smarty();
$smarty->template_dir = './';
$smarty->compile_dir = '../compile/';
$smarty->cache_dir = '../cache/';

$smarty->error_reporting = E_ALL;

$smarty->assign('nazwa', $kret_nazwa);
$smarty->assign('id', $kret_id);
$smarty->assign('owner', $kret_owner);
$smarty->assign('tracking', $kret_tracking);
$smarty->assign('opis', $kret_opis);
$smarty->assign('szablon_css', $kret_szablon.'/label.css');

$smarty->display($kret_szablon.'/label.html');
