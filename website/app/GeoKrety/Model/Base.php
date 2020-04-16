<?php

namespace GeoKrety\Model;

use DateTime;
use JsonSerializable;

abstract class Base extends \DB\Cortex implements JsonSerializable {
    protected function get_date_object($value): ?DateTime {
        if (is_a($value, '\Datetime')) {
            return $value;
        }
        if (is_null($value)) {
            return null;
        }

        $response = null;
        if (($response = DateTime::createFromFormat(GK_DB_DATETIME_FORMAT, $value, new \DateTimeZone('UTC'))) === false) {
            if (($response = DateTime::createFromFormat(GK_DB_DATETIME_FORMAT_WITHOUT_TZ, $value, new \DateTimeZone('UTC'))) === false) {
                die("Invalid date format $value (".GK_DB_DATETIME_FORMAT.'/'.GK_DB_DATETIME_FORMAT_WITHOUT_TZ.')');
            }
        }

        return $response;
    }

    public static function now() {
        return (new DateTime('now', new \DateTimeZone('UTC')))->format(GK_DB_DATETIME_FORMAT);
    }
}
