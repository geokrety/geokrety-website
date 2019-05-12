<?php

namespace Geokrety\Service;

/**
 * JsonService : manage json encoding.
 *
 * credit: https://secure.php.net/manual/en/function.json-last-error.php (comments)
 */
class JsonService {
    public function safe_json_encode($value, $options = 0, $depth = 512) {
        print_r($value);
        $encoded = json_encode($value, $options, $depth);
        if ($encoded === false && $value && json_last_error() == JSON_ERROR_UTF8) {
            $encoded = json_encode($this->utf8ize($value), $options, $depth);
        }

        return $encoded;
    }

    public function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                unset($d[$k]);
                $d[$this->utf8ize($k)] = $this->utf8ize($v);
            }
        } elseif (is_object($d)) {
            $objVars = get_object_vars($d);
            foreach ($objVars as $key => $value) {
                $d->$key = $this->utf8ize($value);
            }
        } elseif (is_string($d)) {
            return iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($d));
        }

        return $d;
    }

    public function getLastJsonError() {
        switch (json_last_error()) {
            case JSON_ERROR_NONE: return 'JSON_ERROR_NONE';
            case JSON_ERROR_DEPTH: return 'JSON_ERROR_DEPTH';
            case JSON_ERROR_STATE_MISMATCH: return 'JSON_ERROR_STATE_MISMATCH';
            case JSON_ERROR_CTRL_CHAR: return 'JSON_ERROR_CTRL_CHAR';
            case JSON_ERROR_SYNTAX: return 'JSON_ERROR_SYNTAX';
            case JSON_ERROR_UTF8: return 'JSON_ERROR_UTF8';
            case JSON_ERROR_RECURSION: return 'JSON_ERROR_RECURSION';
            case JSON_ERROR_INF_OR_NAN: return 'JSON_ERROR_INF_OR_NAN';
            case JSON_ERROR_UNSUPPORTED_TYPE: return 'JSON_ERROR_UNSUPPORTED_TYPE';
            default: return 'Unknown error';
        }
    }
}
