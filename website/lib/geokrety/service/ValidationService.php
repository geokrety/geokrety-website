<?php

namespace Geokrety\Service;

/**
 * ValidationService : check Geokrety variables.
 */
class ValidationService extends AbstractValidationService {
    public function checkValidFileNamePart($input) {
        if (preg_match('/[^a-z_0-9]/i', $input)) {
            throw new \InvalidArgumentException("$input must be an alphanumeric('a-z_0-9')");
        }
    }

    public function ensureNotEmpty($desc, $value) {
        if (empty($value) || self::is_whitespace($value)) {
            throw new \InvalidArgumentException("expected not empty $desc");
        }

        return $value;
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

    public static function ensureDateTime($desc, $value) {
        if (!is_a($value, '\DateTime')) {
            throw new \InvalidArgumentException("Expected \DateTime value for $desc");
        }

        return $value;
    }

    public function ensureOrderBy($desc, $value, $references, $defaultWay) {
        if (is_null($value)) {
            return array($references[0], $defaultWay);
        }

        $value_ = $value;
        $order = 'ASC';
        if ($value[0] == '-') {
            $value_ = substr($value, 1);
            $order = 'DESC';
        }

        if (!in_array($value_, $references)) {
            throw new \InvalidArgumentException("expected $desc to be in allowed values (".implode(', ', $references).')');
        }

        return [$value_, $order];
    }

    // taken from https://stackoverflow.com/a/48798326/944936
    public static function is_whitespace($string) {
        return preg_match('~^\p{Z}$~u', $string) || empty($string);
    }

    public static function noHtml($string) {
        $HTMLPurifierconfig_conf = \HTMLPurifier_Config::createDefault();
        $HTMLPurifierconfig_conf->set('Cache.SerializerPath', TEMP_DIR_HTMLPURIFIER_CACHE);
        $HTMLPurifierconfig_conf->set('HTML.Allowed', '');
        $HTMLPurifier = new \HTMLPurifier($HTMLPurifierconfig_conf);

        return $HTMLPurifier->purify(trim($string));
    }
}
