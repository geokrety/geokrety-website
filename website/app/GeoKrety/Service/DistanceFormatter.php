<?php

namespace GeoKrety\Service;

use Exception;

class DistanceFormatter {
    public const SUPPORTED_UNITS = ['metric' => 1, 'imperial' => 0.62137];
    public const UNITS = ['metric' => 'km', 'imperial' => 'mi'];

    /**
     * Format distance according to user preferences.
     *
     * @param float  $distance The distance to format
     * @param string $in_unit  The input unit
     *
     * @return string The formatted distance
     *
     * @throws Exception On invalid requested unit
     */
    public static function format(float $distance, string $in_unit = 'metric'): string {
        // Right now, there is not users preferences configuration
        // we assume everyone want metric units.
        // TODO: change this when implementing users preferences
        $out_unit = 'metric';

        if (!array_key_exists($in_unit, self::SUPPORTED_UNITS)) {
            throw new Exception(sprintf(_('Invalid unit specified: %s'), $in_unit));
        }

        return sprintf('%d %s', $distance * self::SUPPORTED_UNITS[$in_unit], self::UNITS[$out_unit]);
    }
}
