<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$kret_co = $_GET['co'];
$kret_userid = $_GET['userid'];
$orderBy = $_GET['orderBy'];
$page = $_GET['page'] > 0 ? $_GET['page'] : 1;

// if the anonymous enters the page without userid in url then we will refer you to login
if (!isset($kret_userid)) {
    if ($longin_status['userid'] == null) {
        header('Location: longin.php');
        exit;
    } else {
        $kret_userid = $longin_status['userid'];
    }
}

if (!empty($kret_co) && !ctype_digit($kret_co)) {
    include_once 'defektoskop.php';
    $TRESC = defektoskop(_('No such user subpage!'), true, 'bad value of CO parameter', 6, 'BAD_INPUT');
    include_once 'smarty.php';
    exit;
}
$smarty->assign('user_subpage', $kret_co);

if (!ctype_digit($kret_userid)) {
    include_once 'defektoskop.php';
    $TRESC = defektoskop(_('No such User!'), true, 'bad value of ID parameter', 6, 'BAD_INPUT');
    include_once 'smarty.php';
    exit;
}

$dblink = GKDB::getLink();

$userR = new \Geokrety\Repository\UserRepository($dblink);
$user = $userR->getById($kret_userid);
if (is_null($user)) {
    include_once 'defektoskop.php';
    $TRESC = defektoskop(_('No such User!'), true, 'there is no such user', 3, 'WRONG_DATA');
    include_once 'smarty.php';
    exit;
}

$smarty->assign('user', $user);

$TYTUL = _('My Page')." - $user->username";

if (empty($kret_co)) {
    $smarty->assign('badges', $user->getBadges());
    $smarty->assign('statsCreatedGeokrety', $user->getStatsGeokretyCreated());
    $smarty->assign('statsMovedGeokrety', $user->getStatsGeokretyMoved());
}

// ------------------------------------------------------------------- owned geokrets

elseif ($kret_co == '1') {
    $geokretR = new \Geokrety\Repository\KonkretRepository($dblink);
    list($geokrety, $geokretyTotal) = $geokretR->getOwnedByUserId($user->id, $orderBy, 'desc', $config['geokrety_per_page'], $page);
    $smarty->assign('geokrety', $geokrety);
    $smarty->assign('geokretyTotal', $geokretyTotal);
    $smarty->assign('geokretyPerPage', $config['geokrety_per_page']);
}

// ------------------------------------------------------------------- observed geokrets

elseif ($kret_co == '2') {
    $geokretR = new \Geokrety\Repository\KonkretRepository($dblink);
    list($geokrety, $geokretyTotal) = $geokretR->getWatchedByUserId($user->id, $orderBy, 'desc', $config['geokrety_per_page'], $page);
    $smarty->assign('geokrety', $geokrety);
    $smarty->assign('geokretyTotal', $geokretyTotal);
    $smarty->assign('geokretyPerPage', $config['geokrety_per_page']);
} // observed geokrets

// --------------------------------------------------------------------- my recent moves

elseif ($kret_co == '3') {
    $tripR = new \Geokrety\Repository\TripRepository($dblink);
    list($trip, $tripTotal) = $tripR->getAllTripByAuthorId($user->id, $orderBy, 'desc', $config['trip_per_page'], $page);
    $smarty->assign('trip', $trip);
    $smarty->assign('tripTotal', $tripTotal);
    $smarty->assign('tripPerPage', $config['trip_per_page']);
}

// --------------------------------------------------------------------- recent moves of MY geokrets

elseif ($kret_co == '4') {
    $tripR = new \Geokrety\Repository\TripRepository($dblink);
    list($trip, $tripTotal) = $tripR->getAllTripByOwnerId($user->id, $orderBy, 'desc', $config['trip_per_page'], $page);
    $smarty->assign('trip', $trip);
    $smarty->assign('tripTotal', $tripTotal);
    $smarty->assign('tripPerPage', $config['trip_per_page']);
}

// ----------------------------------------- user's inventory

elseif ($kret_co == '5') {
    $geokretR = new \Geokrety\Repository\KonkretRepository($dblink);
    list($geokrety, $geokretyTotal) = $geokretR->getInventoryByUserId($user->id, $orderBy, 'desc', $config['geokrety_per_page'], $page);
    $smarty->assign('geokrety', $geokrety);
    $smarty->assign('geokretyTotal', $geokretyTotal);
    $smarty->assign('geokretyPerPage', $config['geokrety_per_page']);
}

// ------------------------------ SMARTY ------------------------------ //

$smarty->append('css', CDN_LEAFLET_CSS);
$smarty->append('javascript', CDN_LEAFLET_JS);
$smarty->append('javascript', CDN_LEAFLET_AJAX_JS);

if ($user->hasCoordinates()) {
    $smarty->append('js_template', 'js/user_map_home.tpl.js');
}

$smarty->assign('content_template', 'user.tpl');
require_once 'smarty.php';
