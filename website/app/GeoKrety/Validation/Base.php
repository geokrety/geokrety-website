<?php

namespace GeoKrety\Validation;

class Base {
    protected bool $hasErrors = false;

    protected function flash(string $default, string $message = null, string $level = 'danger') {
        \Flash::instance()->addMessage($message ?? $default, $level);
    }

    // taken from https://stackoverflow.com/a/48798326/944936
    protected static function is_whitespace($string) {
        return preg_match('~^\p{Z}$~u', $string) || empty($string);
    }

    public static function isEmpty($value) {
        return is_null($value) || empty($value) || self::is_whitespace($value);
    }

    public static function isNotEmpty($value) {
        return !self::isEmpty($value);
    }
}
