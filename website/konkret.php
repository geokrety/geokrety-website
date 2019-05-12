<?php

require_once '__sentry.php';

// this page shows details of a GeoKret

if (count($_GET) == 0) {
    header('Location: /');
}

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$kret_gk = $_GET['gk'];
$kret_id = $_GET['id'];
$kret_nocache = $_GET['nocache'];
$page = ctype_digit($_GET['page']) ? $_GET['page'] : 1;

// -------------------------------------- filtering --------------------------
if (isset($kret_gk)) {
    if (preg_match('#^[a-f0-9]{4,5}$#i', substr($kret_gk, 2, 5))) {
        $kret_id = hexdec(substr($kret_gk, 2, 5));
    } else {
        include_once 'defektoskop.php';
        $TRESC = defektoskop(_('No such GeoKret!'), true, 'bad value of GK parameter', 6, 'BAD_INPUT');
        include_once 'smarty.php';
        exit;
    }
}

// ------------------------------------ geokret details ---------------------

if (!ctype_digit($kret_id)) {
    include_once 'defektoskop.php';
    $TRESC = defektoskop(_('No such GeoKret!'), true, 'bad value of ID parameter', 6, 'BAD_INPUT');
    include_once 'smarty.php';
    exit;
}

$link = GKDB::getLink();
$kret_gk = sprintf('GK%04X', $kret_id);
require_once 'czy_obserwowany.php'; //geokret_watchers

// GeoKret details
$gkR = new \Geokrety\Repository\KonkretRepository($link);
$geokret = $gkR->getById($kret_id);
if (is_null($geokret)) {
    include_once 'defektoskop.php';
    $TRESC = defektoskop(_('No such GeoKret!'), true, 'there is no such mole', 3, 'WRONG_DATA');
    include_once 'smarty.php';
    exit;
}
$smarty->assign('geokret_details', $geokret);
$smarty->assign('geokret_already_seen', $geokret->hasCurrentUserSeenGeokretId());

// Country track
$countryTrack = $geokret->cachesCount ? $gkR->getCountryTrack($geokret->id) : null;
$smarty->assign('country_track', $countryTrack);

// pictures
$pictureR = new \Geokrety\Repository\PictureRepository($link);
$pictures = $pictureR->getByGeokretId($geokret->id);
$smarty->assign('geokret_pictures', $pictures);

// Altitude
if (is_file('templates/wykresy/'.$geokret->id.'-m.png') and is_file('templates/wykresy/'.$geokret->id.'-m.png')) {
    $smarty->assign('geokret_altitude_profile', true);
}

// Watchers
$smarty->assign('geokret_watchers', czy_obserwowany($geokret->id, $userid_longin));

//-------------------------------------------- MAP ------------------------------- //

if ($geokret->cachesCount > 0) {
    $smarty->append('css', CDN_LEAFLET_CSS);
    $smarty->append('javascript', CDN_LEAFLET_JS);
    $smarty->append('javascript', '/konkret.js');

    $onLoadErrorHtmlMessage = '<center><b>'._('unable to initialize map').'</b></center>';
    $lastSeenMessage = '<b>'._('last seen here !').'</b>';
    $jquery = <<<EOD
let onLoadErrorHtmlMessage = "$onLoadErrorHtmlMessage";
let lastSeenMessage = "$lastSeenMessage";
let mapGeokretyId = $kret_id;
initMapForGeokrety(mapGeokretyId, onLoadErrorHtmlMessage, lastSeenMessage);
EOD;
    $smarty->append('jquery', $jquery);

    // manage map data cache and files
    $mapDirectory = 'mapki/';
    if (isset($config['mapki'])) {
        $mapDirectory = $config['mapki'];
    }
    $tripService = new \Geokrety\Service\TripService($mapDirectory);
    if (isset($kret_nocache)) { // we enforce to regenerate cache
        $tripService->evictTripCache($kret_id);
    }
    $tripService->ensureGeneratedFiles($kret_id);
    $smarty->assign('trip_gpx', $tripService->getTripGpxFilename($kret_id));
    $smarty->assign('trip_csv', $tripService->getTripCsvFilename($kret_id));
}

// ----------------------------------------------Ruchy-----------------------------

// how many moves in total
$tripR = new \Geokrety\Repository\TripRepository($link);
$total_move_count = $tripR->countTotalMoveByGeokretId($geokret->id);
$smarty->assign('total_move_count', $total_move_count);

// Pagination total number of pages
$max_page = ceil($total_move_count / MOVES_PER_PAGE);
if ($page > $max_page) {
    $page = $max_page || 1;
}
$move_start = ($page - 1) * MOVES_PER_PAGE;

$moves = $tripR->getAllTripByGeokretyId($geokret->id, $move_start, MOVES_PER_PAGE);
$smarty->assign('moves', $moves);

// Extract moves ids
$moves_ids = array_map(function ($move) {
    return $move->ruchId;
}, $moves);

// Moves comments
$tripCommentR = new  \Geokrety\Repository\TripCommentRepository($link);
$moves_comments = $tripCommentR->getByTripIds($moves_ids);
$smarty->assign('moves_comments', $moves_comments);

// ----------------------------------------------JSON-LD---------------------------
// schema used: http://schema.org/Sculpture

$gkName = $config['adres'];
$gkUrl = $config['adres']."konkret.php?id=$kret_id";
$gkOwnerUrl = $config['adres'].'mypage.php?id='.$geokret->ownerId;
$gkLogoUrl = $config['cdn_url'].'/images/banners/geokrety.png';
$gkkeywords = $config['keywords'].",$kret_gk,".$cotozakret[$geokret->type];

$ldHelper = new LDHelper($gkName, $config['adres'], $gkLogoUrl);
$konkret = new \Geokrety\Domain\Konkret();
$konkret->name = $kret_gk.' - '.$geokret->name;
$konkret->description = $geokret->description;
$konkret->url = $gkUrl;
// $konkret->author = $geokret['username']; // TODO
$konkret->authorUrl = $gkOwnerUrl;
$konkret->datePublished = date('c', strtotime($geokret->datePublished));
// if (isset($avatar)) {
//     $konkret->imageUrl = CONFIG_CDN_OBRAZKI_MALE.'/'.$avatar['filename']; // TODO
// }
//$konkret->keywords = $gkkeywords;
// // rate
// if (isset($ratingCount) && $ratingCount > 0) {
//     $konkret->ratingCount = $ratingCount;
//     $konkret->ratingAvg = $ratingAvg;
// }
// comments
// TODO
if ($konkretLogsCount > 0) {
    $konkret->konkretLogs = $konkretLogs;
}
$smarty->assign('ldjson', $ldHelper->helpKonkret($konkret));

// ----------------------------------------------JSON-LD-(end)---------------------

$TYTUL = $geokret->name;

$smarty->assign('content_template', 'geokret.tpl');

require_once 'smarty.php';
