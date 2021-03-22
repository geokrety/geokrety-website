<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property string service_id
 * @property int last_update
 * @property DateTime updated_on_datetime
 * @property int|null revision
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
    ];

    public function get_updated_on_datetime($value): ?DateTime {
        return self::get_date_object($value);
    }

    /**
     * @param $value
     *
     * @return DateTime|false
     */
    public function get_last_update_as_datetime() {
        return DateTime::createFromFormat(GK_DB_DATETIME_FORMAT_AS_INT, $this->last_update, new \DateTimeZone('UTC'));
    }

    public function asArray(): array {
        // TODO make this an entity - see also WaypointGC
        // Use the JsonSerialize?
        return [
            'service_id' => $this->service_id,
            'last_update' => $this->last_update,
            'updated_on_datetime' => $this->updated_on_datetime,
        ];
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'last_update' => $this->last_update,
            // 'updated_on_datetime' => $this->updated_on_datetime,
        ];
    }
}
