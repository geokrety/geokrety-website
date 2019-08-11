<?php

namespace GeoKrety\Validation;

use GeoKrety\Service\HTMLPurifier;

class Base {
    protected $hasErrors = false;
    protected $obj = null;

    protected function checkNotNull(string $attribute, string $message = null) {
        if (self::isEmpty($this->obj->$attribute)) {
            $this->hasErrors = true;
            $this->flash(sprintf(_('\'%s\' could not be empty.'), $attribute), $message);

            return false;
        }

        return true;
    }

    protected function checkInArray(int $attribute, array $array, string $message = null) {
        echo var_dump($attribute);
        if (!in_array($this->obj->$attribute, $array, TRUE)) {
            $this->hasErrors = true;
            $this->flash(sprintf(_('\'%s\' not in allowed values.'), $attribute), $message);

            return false;
        }

        return true;
    }

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
