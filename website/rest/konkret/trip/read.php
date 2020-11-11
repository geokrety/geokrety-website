<?php
/**
 * Trip ReST Controller.
 */
require_once '../../../__sentry.php';

$mapDirectory = '../../../mapki/';
if (isset($config['mapki'])) {
    $mapDirectory = '../../../'.$config['mapki'];
}

// parameters
$format = $_GET['format'];
$geokretyId = $_GET['id'];

$action = 'konkret trip read';
$supportedFormats = ['json', 'csv', 'gpx'];

try {
    if (!in_array($format, $supportedFormats)) {
        $format = 'json';
    }
    if (!$geokretyId) {
        throw new \Exception('id is required');
    }

    $geokretyId = intval($geokretyId);
    $limit = 250;

    // query data
    $link = GKDB::getLink();
    $tripService = new \Geokrety\Service\TripService($mapDirectory);
    $trips = $tripService->getTrip($geokretyId, $limit);

    // render result
    switch ($format) {
        case 'json':
            $response = new \Geokrety\Domain\GetResponse($trips);
            $response->writeJson(200);
            break;
        case 'gpx':
            $gpxFilename = 'trip_'.$geokretyId.'.gpx';
            $tripService->renderGpx($geokretyId, $trips, $gpxFilename);
            break;
        case 'csv':
            $csvFilename = 'trip_'.$geokretyId.'.csv';
            $tripService->renderCsv($geokretyId, $trips, $csvFilename);
            break;
        default:
            throw new Exception("format '$format' not supported, expected one of $supportedFormats");
    }
} catch (\Exception $exception) {
    // handle errors
    $errorResponse = new \Geokrety\Domain\ErrorResponse($action, $exception);
    $errorResponse->write($format);
}
