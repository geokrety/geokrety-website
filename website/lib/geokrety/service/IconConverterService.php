<?php

namespace Geokrety\Service;

/**
 * ValidationService : check Geokrety variables.
 */
class IconConverterService {
    public static function computeLogType($locationType, $lastUserId, $currentUser) {
        if ($locationType == '') {
            return '9';
        }
        if (($locationType == '1' or $locationType == '5') and $lastUserId == $currentUser) {
            return '8';
        }

        return $locationType;
    }

    public static function computeLocationType($logType) {
        return $logType == '' ? '9' : $logType;
    }
}
