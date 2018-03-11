<?php

// perform a search ???���󳟿śćńół
require_once 'wybierz_jezyk.php'; // choose the user's language

$id = $_GET['id'];                      // id kreta
$szablon = $_GET['szablon'];    // który szablon = nazwa katalogu
$cut = $_GET['cut'];            // ile znaków z opisu obciąć

require_once '../../longin_chceck.php';
require '../konfig.php';   // config

$longin_status = longin_chceck();
$userid_longin = $longin_status['userid'];

$link = DBConnect();

$result = mysqli_query(
    $link, "SELECT `gk-geokrety`.`id`, `nr`, `gk-geokrety`.`nazwa`, `gk-geokrety`.`opis`, `gk-geokrety`.`owner`, `gk-users`.`user`, `gk-geokrety`.`data`, `gk-geokrety`.`typ`
FROM `gk-geokrety`
LEFT JOIN `gk-users` ON `gk-geokrety`.`owner` = `gk-users`.userid
WHERE `gk-geokrety`.`id`='$id' AND `gk-geokrety`.`owner`='$userid_longin'
LIMIT 1"
);

list($id, $tracking, $nazwa, $opis, $userid, $owner, $data, $typ) = mysqli_fetch_array($result);
mysqli_free_result($result);

$conaco = array("\n" => ' ', '  ' => ' ');

$opis = mb_substr(strtr(strip_tags($opis), $conaco), 0, $cut);
$id = sprintf('GK%04X', $id);

define('SMARTY_DIR', '/usr/share/php/smarty/libs/');
require_once SMARTY_DIR.'Smarty.class.php';

$smarty = new Smarty();
$smarty->template_dir = './';
$smarty->compile_dir = '../compile/';
$smarty->cache_dir = '../cache/';

$smarty->error_reporting = E_ALL;

$smarty->assign('nazwa', $nazwa);
$smarty->assign('id', $id);
$smarty->assign('owner', $owner);
$smarty->assign('tracking', $tracking);
$smarty->assign('opis', $opis);
$smarty->assign('szablon_css', $szablon.'/label.css');

$smarty->display($szablon.'/label.html');
