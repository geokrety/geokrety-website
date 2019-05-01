<?php

require_once '__sentry.php';
header('Content-Type: application/json; charset=utf-8');

function error($message) {
    die(json_encode(array('error' => $message)));
}

if (empty($_GET['query'])) {
    http_response_code(400);
    error(_('Waypoint seems empty.'));
}

if (strlen($_GET['query']) < 4 || strlen($_GET['query']) > 20) {
    http_response_code(400);
    error(sprintf(_('Waypoint length is invalid. It should be between %d and %d characters long.'), 4, 20));
}

$waypointR = new \Geokrety\Repository\WaypointyRepository(GKDB::getLink());
$waypoints = $waypointR->getByName($_GET['query'], 10);
echo json_encode($waypoints, JSON_UNESCAPED_UNICODE);
