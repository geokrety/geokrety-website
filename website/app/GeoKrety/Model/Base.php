<?php

namespace GeoKrety\Model;

class Base extends \DB\Cortex {
    protected function get_date_object($value) {
        if (is_a($value, '\Datetime')) {
            return $value;
        }

        return \DateTime::createFromFormat('Y-m-d H:i:s', $value, new \DateTimeZone('UTC'));
    }

    public static function now() {
        return (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    }
}
