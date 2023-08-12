<?php

namespace GeoKrety\Service;

class DistanceFormatter {
    public const SUPPORTED_UNITS = ['metric' => 1, 'imperial' => 0.62137];
    public const UNITS = ['metric' => 'km', 'imperial' => 'mi'];

    /**
     * Format distance according to user preferences.
     *
     * @param float $distance The distance to format
     *
     * @return string The formatted distance
     *
     * @throws \Exception On invalid requested unit
     */
    public static function format(float $distance): string {
        $out_unit = \GeoKrety\Service\UserSettings::getForCurrentUser('DISTANCE_UNIT');

        if (!array_key_exists($out_unit, self::SUPPORTED_UNITS)) {
            throw new \Exception(sprintf(_('Invalid unit specified: %s'), $out_unit));
        }

        return sprintf('%d %s', $distance * self::SUPPORTED_UNITS[$out_unit], self::UNITS[$out_unit]);
    }
}
