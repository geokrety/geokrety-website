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
            return DEFAULT_ELEVATION;
        }

        return $content;
    }
}
