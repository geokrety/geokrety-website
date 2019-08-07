<?php

namespace Geokrety\Service;

const DEFAULT_ELEVATION = -2000;

/**
 * ElevationService : return elevation from coordinates.
 */
class ElevationService extends AbstractValidationService {
    public static function getElevation($coordinates) {
        if (is_null($coordinates)) {
            return DEFAULT_ELEVATION;
        }
        if (!is_array($coordinates)) {
            throw new \InvalidArgumentException('coordinates parameter is expected to be an array');
        }
        if (!array_key_exists('lat', $coordinates) || !array_key_exists('lon', $coordinates)) {
            return DEFAULT_ELEVATION;
        }

        $url = sprintf(SERVICE_ELEVATION_GEOCODER, $coordinates['lat'], $coordinates['lon']);
        $content = file_get_contents($url);
        if ($content === false || empty($content)) {
            // fallback
            $url = sprintf(SERVICE_ELEVATION_GEOCODER_GOOGLE, $coordinates['lat'], $coordinates['lon'], GOOGLE_MAP_KEY);
            $content = file_get_contents($url);

            // Really give up
            if ($content === false || empty($content)) {
                return null;
            }

            // Process retrieved data from google
            $jsondata = json_decode($content, true);
            if (is_array($jsondata) and $jsondata['status'] == 'OK') {
                $content = $jsondata['results']['0']['elevation'];
            }
        }

        return $content;
    }
}
