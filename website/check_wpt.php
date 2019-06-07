<?php

require_once '__sentry.php';
header('Content-Type: application/json');

function error($message) {
    die(json_encode(array("error" => $message)));
}

if (empty($_GET['wpt'])) {
    http_response_code(400);
    error(_('Waypoint seems empty.'));
}

if (strlen($_GET['wpt']) < 4 || strlen($_GET['wpt']) > 20) {
    http_response_code(400);
    error(sprintf(_('Waypoint length is invalid. It should be between %d and %d characters long.'), 4, 20));
}

$waypoint = new \Geokrety\Repository\WaypointyRepository(GKDB::getLink());
$hasResult = $waypoint->getByWaypoint($_GET['wpt']);

if (!$hasResult) {
    http_response_code(404);
    if ($waypoint->isGCWaypoint($_GET['wpt'])) {
        error(_('This is a Geocaching.com cache that no one logged yet on GeoKrety.org. To ensure correct travel of this GeoKret, please copy/paste cache coordinates in the \'Coordinates\' field.'));
    }
    error(_('Sorry, but this waypoint is not (yet) in our database.'));
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
