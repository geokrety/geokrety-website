<?php

namespace GeoKrety\Validation;

use GeoKrety\Service\HTMLPurifier;

class Base {
    protected $hasErrors = false;

    // taken from https://stackoverflow.com/a/48798326/944936
    protected static function is_whitespace($string) {
        return preg_match('~^\p{Z}$~u', $string) || empty($string);
    }

    public static function isEmpty($value) {
        return is_null($value) || empty($value) || self::is_whitespace($value);
    }
}
