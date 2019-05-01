<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Search');

$smarty->assign('content_template', 'forms/search.tpl');

$g_country = $_GET['country'];
$g_gk = $_GET['gk'];
$g_nr = $_GET['nr'];
$g_owner = $_GET['owner'];
$g_wpt = $_GET['wpt'];
$g_nazwa = $_GET['nazwa'];

require_once 'szukaj_kreta.php';

$link = GKDB::getLink();

if (!empty($g_gk)) {
    $gk = hexdec(substr($g_gk, 2, 5));
    if (!ctype_digit($gk)) {
        danger(_('Invalid GeoKret id'));
        include_once 'smarty.php';
        die();
    }
    $geokretyR = new Geokrety\Repository\KonkretRepository($link);
    $geokret = $geokretyR->getById($gk);
    $smarty->assign('geokrety', array($geokret));
} elseif (!empty($g_nazwa)) {
    $geokretyR = new Geokrety\Repository\KonkretRepository($link);
    $geokrety = $geokretyR->getByName($g_nazwa);
    $smarty->assign('geokrety', $geokrety);
} elseif (!empty($g_owner)) {
    $userR = new Geokrety\Repository\UserRepository($link);
    $users = $userR->getByUsernameOrId($g_owner);
    $smarty->assign('users', $users);
} elseif (!empty($g_wpt)) {
    $geokretyR = new Geokrety\Repository\KonkretRepository($link);
    $geokrety = $geokretyR->getByVisitedCache($g_wpt);
    $smarty->assign('geokrety', $geokrety);
}

// ------------------------------ SMARTY ------------------------------ //
require_once 'smarty.php';
