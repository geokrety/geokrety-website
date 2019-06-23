<?php

use \Geokrety\Repository\WaypointyRepository;
use \Geokrety\Domain\Waypoint;

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

$waypointR = new WaypointyRepository(GKDB::getLink());
$waypoint = $waypointR->getByWaypoint($_GET['wpt']);

if (is_null($waypoint)) {
    http_response_code(404);
    if (Waypoint::isGCWaypoint($_GET['wpt'])) {
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
    'countryCode' => strtoupper($waypoint->countryCode),
    'name' => $waypoint->name,
    'owner' => $waypoint->ownerName,
    'type' => $waypoint->type,
    'cacheType' => $waypoint->type,
    'cacheLink' => $waypoint->link,
    'isGCWaypoint' => Waypoint::isGCWaypoint($_GET['wpt']),
);
echo json_encode($response);
