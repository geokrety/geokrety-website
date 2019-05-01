<?php

namespace Geokrety\Service;

/**
 * TripService : manage geokrety trip with a cache version.
 */
class TripToGPXConverter {
    // common validation service
    private $validationService;

    private $geokretyId;
    private $trips;

    public function __construct($geokretyId, $trips) {
        $this->validationService = new ValidationService();
        $this->geokretyId = $this->validationService->ensureIntGTE('geokretyId', $geokretyId, 1);
        $this->trips = $this->validationService->ensureNotEmptyArray('trips', $trips);
    }

    public function generateGPX() {
        $geokretyId = $this->geokretyId;
        $trips = $this->trips;

        $generationDateTime = date('Y-m-d').'T'.date('H:i:s').'Z';
        $minLat = $trips[0]->lat;
        $minLon = $trips[0]->lon;
        $maxLat = $trips[0]->lat;
        $maxLon = $trips[0]->lon;

        foreach ($trips as $trip) {
            $lat = $trip->lat;
            $lon = $trip->lon;
            $alt = $trip->alt;

            $minLat = min($minLat, $lat);
            $minLon = min($minLon, $lon);
            $maxLat = max($maxLat, $lat);
            $maxLon = max($maxLon, $lon);

            $ruchData = $trip->ruchData->format('c');
            $waypoint = $trip->waypoint;
            $waypointName = $trip->waypointName;
            $logType = $trip->logType->getLogTypeString();
            $username = $trip->username;
            $byUsername = sprintf(_('by %s'), $username);
            // keep light GPX file // $comment = $trip->comment;
            $link = $trip->waypointLink;
            $cacheDetails = '';

            // GPX - URL
            $gpxUrl = '';
            if ($link != '') {
                $cacheDetails = sprintf(_('cache %s details'), $waypoint);
                $gpxUrl = '<url>'.$link.'</url><urlname>'.$cacheDetails.'</urlname>';
            }
            // GPX - NAME
            $gpxName = _('trip step'); // default value when no cache code
            if ($waypoint != '') {
                $gpxName = "$waypoint $waypointName";
            }

            // GPX - TRACK
            $gpx_track = <<<EOTRACK
      <trkpt lat="$lat" lon="$lon"><ele>$alt</ele></trkpt>
$gpx_track
EOTRACK;

            // TODO would be nice to convert to real Xml library builder.
            // We've also have \Service\Xml\… for such task

            // GPX - WAYPOINT
            $gpx_wpt = <<<EOWPT
  <wpt lat="$lat" lon="$lon">'
    <ele>$alt</ele>
    <time>$ruchData</time>
    <name><![CDATA[$gpxName]]></name>
    <desc><![CDATA[
    ($logType) $byUsername
    ]]></desc>
    $gpxUrl<sym>Geocache</sym>
  </wpt>
$gpx_wpt
EOWPT;
        } // end foreach

        // GPX - CONTENT
        $gpx_content = <<<EOXML
<?xml version="1.0" encoding="UTF-8"?>
<gpx version="1.0" creator="Geokrety.org" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.topografix.com/GPX/1/0" xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">
  <time>$generationDateTime</time>
  <bounds minlat="$minLat" minlon="$minLon" maxlat="$maxLat" maxlon="$maxLon"/>
  <trk>
    <name>GK $geokretyId</name>
    <trkseg>
$gpx_track    </trkseg>
  </trk>
$gpx_wpt</gpx>
EOXML;

        return $gpx_content;
    }

    public function render($filename = 'trip.gpx') {
        $debug = false;
        $gpx_content = $this->generateGPX();

        header('Access-Control-Allow-Origin: *');
        if (!$debug) {
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Content-Type: application/gpx+xml; charset=UTF-8');
        }
        http_response_code(200);
        if ($debug) {
            echo '<pre>'.htmlspecialchars($gpx_content).'</pre>';

            return;
        }
        echo $gpx_content;
    }

    public function generateFile($gpxFile) {
        $gpx_content = $this->generateGPX();

        // raw xml
        $file = fopen($gpxFile, 'w');
        fwrite($file, $gpx_content);
        fclose($file);

        // zapis CSV (gzipped)
        $gzip = gzopen($gpxFile.'.gz', 'w');
        gzwrite($gzip, $gpx_content);
        gzclose($gzip);
    }
}
