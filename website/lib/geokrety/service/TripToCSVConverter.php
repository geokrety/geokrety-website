<?php

namespace Geokrety\Service;

/**
 * TripService : manage geokrety trip with a cache version.
 */
class TripToCSVConverter {
    // common validation service
    private $validationService;

    private $geokretyId;
    private $trips;

    public function __construct($geokretyId, $trips) {
        $this->validationService = new ValidationService();
        $this->geokretyId = $this->validationService->ensureIntGTE('geokretyId', $geokretyId, 1);
        $this->trips = $this->validationService->ensureNotEmptyArray('trips', $trips);
    }

    public function escapeComment($comment) {
        $comment = str_replace('"', '\'', $comment);
        $comment = str_replace(';', ',', $comment);
        $comment = str_replace('\r', ' ', $comment);
        $comment = str_replace('\n', ' ', $comment);
        $comment = substr($comment, 0, 100);

        return $comment;
    }

    public function generateCSV() {
        $trips = $this->trips;

        foreach ($trips as $trip) {
            $lat = $trip->lat;
            $lon = $trip->lon;
            $alt = $trip->alt;

            $ruchId = $trip->ruchId;
            $waypoint = $trip->waypoint;
            $ruchData = $trip->ruchData;
            $comment = self::escapeComment($trip->comment);
            $logType = $trip->getLogTypeString();
            $country = $trip->country;
            $distance = $trip->distance;
            $username = $trip->username;
            $waypointName = $trip->waypointName;
            $link = $trip->waypointLink;

            // CSV RAW
            $csv_raw = <<<EOROW
$ruchId;$lat;$lon;"$waypoint";$ruchData;"$comment";"$logType";"$country";$alt;$distance;"$username";"$waypointName";$link
$csv_raw
EOROW;
        } // end foreach

        // CSV - CONTENT
        $csv_content = <<<EOCSV
ruch_id;lat;lon;waypoint;data;comment(extract);logType;country;alt;distance;username;waypointName;link
$csv_raw
EOCSV;

        return $csv_content;
    }

    public function render($filename = 'trip.csv') {
        $debug = false;
        $csv_content = $this->generateCSV();

        header('Access-Control-Allow-Origin: *');
        if (!$debug) {
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Content-Type: text/csv; charset=UTF-8');
        }
        http_response_code(200);
        if ($debug) {
            echo '<pre>'.htmlspecialchars($csv_content).'</pre>';

            return;
        }
        echo $csv_content;
    }

    public function generateFile($csvFile) {
        $csv_content = $this->generateCSV();
        // zapis CSV (gzipped)
        $gzip = gzopen($csvFile, 'w');
        gzwrite($gzip, $csv_content);
        gzclose($gzip);
    }
}
