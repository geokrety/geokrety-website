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
    include_once 'recent_moves.php';
    $TRESC .= recent_moves("WHERE gk.owner='$user->id'", 50, _('Recent moves of my geokrets'), '', true);
}

// ----------------------------------------- user's inventory

elseif ($kret_co == '5') {
    $geokretR = new \Geokrety\Repository\KonkretRepository($dblink);
    list($geokrety, $geokretyTotal) = $geokretR->getInventoryByUserId($user->id, $orderBy, 'desc', $config['geokrety_per_page'], $page);
    $smarty->assign('geokrety', $geokrety);
    $smarty->assign('geokretyTotal', $geokretyTotal);
    $smarty->assign('geokretyPerPage', $config['geokrety_per_page']);
    // $przetrzymywane_krety_sql = "SELECT gk.id, gk.nr, gk.nazwa, gk.opis, gk.owner, gk.data, gk.typ, us.user
        // 	FROM `gk-geokrety` AS gk
        // 	LEFT JOIN `gk-ruchy` ru ON ( gk.ost_pozycja_id = ru.ruch_id )
        // 	LEFT JOIN `gk-users` us ON ( gk.owner = us.userid )
        // 	WHERE ( ru.logtype = '1' AND ru.user = '$user->id' )
        // 		OR ( ru.logtype = '5' AND ru.user = '$user->id' )
        // 		OR (gk.owner = '$user->id' AND gk.ost_pozycja_id = '0')
        // 	ORDER BY gk.id ASC";
    //
    // include_once 'szukaj_kreta.php';
    // include_once 'mygeokrets.php';
    // $TYTUL = sprintf(_("%s's Inventory"), $user->username);
    // $TRESC .= mygeokrets($kret_co, $user->id, 100, $TYTUL, $longin_status['userid']);
// } elseif ($kret_co == '6') {
}

// ------------------------------ SMARTY ------------------------------ //

$smarty->append('css', CDN_LEAFLET_CSS);
$smarty->append('javascript', CDN_LEAFLET_JS);
$smarty->append('javascript', CDN_LEAFLET_CENTERCROSS_JS);

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

$smarty->assign('content_template', 'user.tpl');
require_once 'smarty.php';
