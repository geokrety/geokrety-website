<?php

// update konkret trip : cache, csv, gpx
function konkret_mapka($kretid) {
    global $config;

    $mapDirectory = 'mapki/';
    if (isset($config['mapki'])) {
        $mapDirectory = $config['mapki'];
    }
    $tripService = new \Geokrety\Service\TripService($mapDirectory);
    $tripService->onTripUpdate($kretid);
}
