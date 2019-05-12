<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
$template = 'dialog/move_delete.tpl';

$move_id = $_GET['id'];
if (!ctype_digit($move_id)) {
    echo _('Oops! Something went wrong.').' [#'.__LINE__.']';
    exit;
}

$tripR = new \Geokrety\Repository\TripRepository(GKDB::getLink());
$trip = $tripR->getByTripId($move_id);
$smarty->assign('trip', $trip);

// $sql = "SELECT ru.ruch_id AS id, ru.data AS date,
//         ru.id AS geokret_id,
//         ru.koment AS comment, ru.zdjecia AS pictures_count,
//         ru.komentarze AS comments_count, ru.logtype As logtype,
//         ru.user AS author_id, us.user AS username, ru.username AS username_anonymous,
//         ru.lat, ru.lon, ru.waypoint,
//         ru.country, ru.alt, ru.droga AS distance,
//         ru.app, ru.app_ver
// FROM `gk-ruchy` ru
// LEFT JOIN `gk-users` AS us ON (ru.user = us.userid)
// WHERE ru.ruch_id = $move_id";
// $result = mysqli_query($link, $sql);
// $move = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
// $smarty->assign('move', $move);
// $geokret_id = $move['geokret_id'];
//
// // Load GK Details
// $sql = "SELECT id as id, nr as tracking_code, nazwa as name,
// opis as description, owner as owner_id, us.user as username,
// data as creation_date, typ as type, droga as distance,
// skrzynki as caches_count, zdjecia as pictures_count, avatarid as avatar_id,
// ost_pozycja_id AS last_position_id, ost_log_id AS last_log_id
// FROM `gk-geokrety` gk
// LEFT JOIN `gk-users` us ON gk.owner = us.userid
// WHERE gk.id='$geokret_id' LIMIT 1";
// $result = mysqli_query($link, $sql);
// $geokret = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
// $smarty->assign('geokret_details', $geokret);

require_once 'smarty.php';
