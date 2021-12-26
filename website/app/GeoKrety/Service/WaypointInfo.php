<?php

namespace GeoKrety\Service;

/**
 * WaypointInfo : Some waypoint helpers.
 */
class WaypointInfo {
    public const PREFIX_OC = ['OC', 'OP', 'OK', 'GE', 'OZ', 'OU', 'ON', 'OL', 'OJ', 'OS', 'GD', 'GA', 'VI', 'MS', 'TR', 'EX', 'GR', 'RH', 'OX', 'OB', 'OR', 'LT', 'LV'];
    public const PREFIX_GC = ['GC']; // Geocaching
    public const PREFIX_OTHER_1 = ['N']; // Navicache
    public const PREFIX_OTHER_3 = ['WPG'];

    public static function isOC($waypoint) {
        return in_array(substr(strtoupper($waypoint), 0, 2), self::PREFIX_OC);
    }

    public static function isGC($waypoint) {
        return in_array(substr(strtoupper($waypoint), 0, 2), self::PREFIX_GC);
    }

    public static function isWPG($waypoint) {
        return in_array(substr(strtoupper($waypoint), 0, 3), self::PREFIX_OTHER_3);
    }

    public static function isNavicache($waypoint) {
        return in_array(substr(strtoupper($waypoint), 0, 3), self::PREFIX_OTHER_1);
    }

    public static function isImportedWaypoint($waypoint) {
        return self::isOC($waypoint) or self::isWPG($waypoint) or self::isNavicache($waypoint);
    }

    public static function getLink($waypoint) {
        return sprintf(GK_SERVICE_GO2GEO_URL, $waypoint);
    }

    public static function getLinkPosition($lat, $lon) {
        return sprintf(GK_SERVICE_GC_SEARCH_NEAREST_URL, $lat, $lon);
    }
}
