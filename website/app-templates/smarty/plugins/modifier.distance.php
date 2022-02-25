<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.distance.php
 * Type:     modifier
 * Name:     distance
 * Purpose:  outputs distance according to user preferences
 * -------------------------------------------------------------.
 */

use GeoKrety\Service\DistanceFormatter;

/**
 * @throws \Exception
 */
function smarty_modifier_distance(?int $distance, $unit = 'metric'): string {
    if (is_null($distance)) {
        return '';
    }

    return DistanceFormatter::format($distance, $unit);
}
