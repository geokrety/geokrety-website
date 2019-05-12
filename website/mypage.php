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
$smarty->assign('user', $user);
$smarty->assign('badges', $user->getBadges());
$smarty->assign('statsCreatedGeokrety', $user->getStatsGeokretyCreated());
$smarty->assign('statsMovedGeokrety', $user->getStatsGeokretyMoved());

$TYTUL = _('My Page')." - $user->username";

if ($kret_co == '1') {
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
$smarty->append('javascript', CDN_LEAFLET_CENTERCROSS_JS);

if ($user->hasCoordinates()) {
    $jquery = <<<EOD
var map = L.map("mapid");
var osmUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
var osmAttrib = "Map data © <a href=\"https://www.openstreetmap.org\">OpenStreetMap</a> contributors";
var osm = new L.TileLayer(osmUrl, {
  minZoom: 0,
  maxZoom: 12,
  attribution: osmAttrib
});

// start the map // TODO: what to do if user has no coordinates?
map.setView(new L.LatLng($user->latitude, $user->longitude), 6);
map.addLayer(osm);


// var control = L.control.centerCross({show: true, position: "topright"});
// map.addControl(control);

// var bounds = [[44.31307, 4.70770], [44.31107, 4.70570]];
// L.rectangle(bounds, {color: "#ff7800", weight: 1}).addTo(map);
EOD;
    $smarty->append('jquery', $jquery);
}

$smarty->assign('content_template', 'user.tpl');
require_once 'smarty.php';
