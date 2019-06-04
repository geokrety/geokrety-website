<?php

require_once '__sentry.php';
header('Content-Type: application/json');

if (empty($_GET['wpt'])) {
    http_response_code(400);
    die(_('"Waypoint seems empty."'));
}

$waypoint = new \Geokrety\Repository\WaypointyRepository(GKDB::getLink());
$hasResult = $waypoint->getByWaypoint($_GET['wpt']);

// if ($_GET['validateOnly'] == 'true') {
//     if ($hasResult) {
//         die('"true"');
//     }
//     if ($waypoint->isGCWaypoint($_GET['wpt'])) {
//         if (isset($_GET['coordinates']) && !empty($_GET['coordinates'])) {
//             include_once 'cords_parse.php';
//             $coords_parse = cords_parse($_GET['coordinates']);
//             if ($coords_parse['error'] == '') {
//                 die('"true"'); // Json valid
//             }
//         }
//         die(_('"This is a Geocaching.com cache that no one logged yet on GeoKrety.org. Please copy/paste cache coordinates in the \'Coordinates\' field below."'));
//     }
//     die(_('"Sorry, but this waypoint is not (yet) in our database."'));
// }

if (!$hasResult) {

    if (!isset($_GET['coordinates']) || empty($_GET['coordinates'])) {
        http_response_code(404);
        if ($waypoint->isGCWaypoint($_GET['wpt'])) {
            die(_('"This is a Geocaching.com cache that no one logged yet on GeoKrety.org. To ensure correct travel of this GeoKret, please copy/paste cache coordinates in the \'Coordinates\' field."'));
        }
        die(_('"Sorry, but this waypoint is not (yet) in our database."'));
    }

    include_once 'cords_parse.php';
    $coords_parse = cords_parse($_GET['coordinates']);
    if ($coords_parse['error'] == '') {
        print_r($coords_parse);
        // die($coords_parse[0] .' '. $coords_parse[1]);
        echo json_encode($coords_parse);
    }

}

$response = array(
    'waypoint' => strtoupper($waypoint->waypoint),
    'latitude' => $waypoint->lat,
    'longitude' => $waypoint->lon,
    'altitude' => $waypoint->alt,
    'country' => $waypoint->country,
    'countryCode' => strtoupper($waypoint->country_code),
    'name' => $waypoint->name,
    'owner' => $waypoint->owner,
    'type' => $waypoint->typ,
    'cacheType' => $waypoint->cache_type,
    'cacheLink' => $waypoint->cache_link,
    'isGCWaypoint' => $waypoint->isGCWaypoint($_GET['wpt']),
);
echo json_encode($response);
