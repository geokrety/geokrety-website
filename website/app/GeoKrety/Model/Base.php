<?php

namespace GeoKrety\Model;

use DateTime;

abstract class Base extends \DB\Cortex implements \JsonSerializable {
    protected function get_date_object($value): ?\DateTime {
        if (is_a($value, '\Datetime')) {
            return $value;
        }
        if (is_null($value)) {
            return null;
        }

        $response = null;
        if (($response = \DateTime::createFromFormat(GK_DB_DATETIME_FORMAT, $value, new \DateTimeZone('UTC'))) === false) {
            if (($response = \DateTime::createFromFormat(GK_DB_DATETIME_FORMAT_WITHOUT_TZ, $value, new \DateTimeZone('UTC'))) === false) {
                if (($response = \DateTime::createFromFormat(GK_DB_DATETIME_FORMAT_MICROSECONDS, $value, new \DateTimeZone('UTC'))) === false) {
                    exit(sprintf('Invalid date format "%s" (%s | %s | %s)', $value, GK_DB_DATETIME_FORMAT, GK_DB_DATETIME_FORMAT_WITHOUT_TZ, GK_DB_DATETIME_FORMAT_MICROSECONDS));
                }
            }
        }

        return $response;
    }

    /**
     * @return \DB\Cortex|\DB\Cursor|false|mixed
     *
     * @throws \Exception
     */
    public function save() {
        try {
            return parent::save();
        } catch (\PDOException $e) {
            // Errors samples:

            // SQLSTATE[P0001]: Raise exception: 7 ERROR:  Move date (2020-08-22 13:30:00+00) time can not be before GeoKret birth (2020-08-22 15:30:00+00)
            // CONTEXT:  PL/pgSQL function moves_moved_on_datetime_checker() line 12 at RAISE

            // SQLSTATE[22001]: String data, right truncated: 7 ERROR: value too long for type character varying(5120)

            if (preg_match('/SQLSTATE\[\w+\]: .*ERROR:\W+(.*)\W*(:?\n.)*/', $e->getMessage(), $matches) !== false) {
                throw new \Exception($matches[1]);
            }
            throw new \Exception($e->getMessage());
        }
    }

    public static function now() {
        return (new \DateTime('now', new \DateTimeZone('UTC')))->format(GK_DB_DATETIME_FORMAT);
    }
}
