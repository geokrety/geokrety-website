<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

const SUPPORTED_UNITS = ['km' => 1, 'mi' => 0.62137];

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.distance.php
 * Type:     modifier
 * Name:     distance
 * Purpose:  outputs distance acording to user preferences
 * -------------------------------------------------------------
 */
function smarty_modifier_distance(?int $distance, $unit = 'km'): string {
    if (is_null($distance)) {
        return '';
    }
    if (!array_key_exists($unit, SUPPORTED_UNITS)) {
        exit(sprintf('Unknown unit: %s', $unit));
    }
    $_distance = $distance * SUPPORTED_UNITS[$unit];

    return sprintf('%d %s', $_distance, strtolower($unit));
}
