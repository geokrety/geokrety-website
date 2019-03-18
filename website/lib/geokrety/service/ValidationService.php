<?php

namespace Geokrety\Service;

/**
 * ValidationService : check Geokrety variables.
 */
class ValidationService {
    public function checkValidFileNamePart($input) {
        if (preg_match('/[^a-z_0-9]/i', $input)) {
            throw new \InvalidArgumentException("$input must be an alphanumeric('a-z_0-9')");
        }
    }

    public function ensureNotEmptyArray($desc, $value) {
        if (!is_array($value) || empty($value)) {
            throw new \InvalidArgumentException("expected not empty $desc");
        }

        return $value;
    }

    public function ensureIntGTE($desc, $value, $minRange) {
        $intValue = intval($value);
        if (!is_int($intValue)) {
            throw new \InvalidArgumentException("Expected integer value for $desc");
        }
        if ($intValue < $minRange) {
            throw new \InvalidArgumentException("Expected integer value greater than or equals to $minRange for $desc");
        }

        return $intValue;
    }
}
