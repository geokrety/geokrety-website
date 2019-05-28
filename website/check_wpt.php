<?php

require_once '__sentry.php';

$waypoint = new \Geokrety\Repository\WaypointyRepository(GKDB::getLink());
$hasResult = $waypoint->getByWaypoint($_POST['wpt']);


if ($_POST['validateOnly'] == 'true') {
    if ($hasResult) {
        die('"true"');
    }
    if ($waypoint->isGCWaypoint($_POST['wpt'])) {
        if (isset($_POST['coordinates']) && !empty($_POST['coordinates'])) {
            include_once 'cords_parse.php';
            $coords_parse = cords_parse($_POST['coordinates']);
            if ($coords_parse['error'] == '') {
                die('"true"'); // Json valid
            }
        }
        die(_('"This is a Geocaching.com cache that no one logged yet on GeoKrety.org. Please copy/paste cache coordinates in the \'Coordinates\' field below."'));
    }
    die(_('"Sorry, but this waypoint is not (yet) in our database."'));
}

if (!$hasResult) {

    if (isset($_POST['coordinates']) && !empty($_POST['coordinates'])) {
        include_once 'cords_parse.php';
        $coords_parse = cords_parse($_POST['coordinates']);
        if ($coords_parse['error'] == '') {
            die($coords_parse[0] .' '. $coords_parse[1]);
        }
    }

    http_response_code(404);
    if ($waypoint->isGCWaypoint($_POST['wpt'])) {
        die(_('"This is a Geocaching.com cache that no one logged yet on GeoKrety.org. Please copy/paste cache coordinates in the field below."'));
    }
    die(_('"Sorry, but this waypoint is not (yet) in our database."')); // Json valid
}

die("$waypoint->lat $waypoint->lon");
