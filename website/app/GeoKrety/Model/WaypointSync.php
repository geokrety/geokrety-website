<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property string service_id
 * @property int|null       $revision
 * @property \DateTime      $updated_on_datetime
 * @property \DateTime|null $last_success_datetime
 * @property \DateTime|null $last_error_datetime
 * @property int            $error_count
 * @property int            $wpt_count
 * @property string|null    $last_error
 */
class WaypointSync extends BaseWaypoint {
    protected $table = 'gk_waypoints_sync';

    protected $fieldConf = [
        'service_id' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'revision' => [
            'type' => Schema::DT_INT1,
            'default' => null,
            'nullable' => true,
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
        'last_success_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => null,
            'nullable' => true,
        ],
        'last_error_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => null,
            'nullable' => true,
        ],
        'error_count' => [
            'type' => Schema::DT_INT1,
            'default' => 0,
            'nullable' => false,
        ],
        'wpt_count' => [
            'type' => Schema::DT_INT1,
            'default' => 0,
            'nullable' => false,
        ],
        'last_error' => [
            'type' => Schema::DT_TEXT,
            'default' => null,
            'nullable' => true,
        ],
    ];

    public function get_updated_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    /**
     * @return \DateTime|false
     */
    public function get_last_update_as_datetime() {
        return \DateTime::createFromFormat(GK_DB_DATETIME_FORMAT_AS_INT, $this->revision, new \DateTimeZone('UTC'));
    }

    /**
     * @return \DateTime
     */
    public function get_last_success_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    /**
     * @return \DateTime
     */
    public function get_last_error_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function asArray(): array {
        // TODO make this an entity - see also WaypointGC
        // Use the JsonSerialize?
        return [
            'service_id' => $this->service_id,
            'revision' => $this->revision,
            'updated_on_datetime' => $this->updated_on_datetime,
        ];
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'revision' => $this->revision,
            // 'updated_on_datetime' => $this->updated_on_datetime,
        ];
    }
}
