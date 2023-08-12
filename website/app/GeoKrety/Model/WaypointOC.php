<?php

namespace GeoKrety\Model;

use DateTime;
use DB\SQL\Schema;

/**
 * @property string name
 * @property string owner
 * @property string type
 * @property string country_name
 * @property string link
 * @property \DateTime added_on_datetime
 * @property \DateTime updated_on_datetime
 * @property int status
 * @property string position
 * @property string|null provider
 */
class WaypointOC extends BaseWaypoint {
    protected $table = 'gk_waypoints_oc';

    protected $fieldConf = [
        'waypoint' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'lat' => [
            'type' => Schema::DT_DOUBLE,
            'nullable' => false,
        ],
        'lon' => [
            'type' => Schema::DT_DOUBLE,
            'nullable' => false,
        ],
        'elevation' => [
            'type' => Schema::DT_INT2,
            'default' => '-32768',
            'nullable' => false,
        ],
        'country' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'name' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'owner' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'type' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'country_name' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'link' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ],
        'added_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
            'validate' => 'is_date',
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
        'status' => [
            'type' => Schema::DT_INT1,
            'default' => '1',
            'nullable' => false,
        ],
        'position' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'default' => null,
        ],
        'provider' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'default' => null,
        ],
    ];

    public function get_added_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function get_updated_on_datetime($value): ?\DateTime {
        return self::get_date_object($value);
    }

    public function asArray(): array {
        // TODO make this an entity - see also WaypointGC
        // Use the JsonSerialize?
        return [
            'waypoint' => $this->waypoint,
            'latitude' => $this->lat,
            'longitude' => $this->lon,
            'elevation' => $this->elevation,
            'country' => $this->country_name,
            'countryCode' => $this->country,
            'name' => $this->name,
            'owner' => $this->owner,
            'status' => $this->status,
            'typeName' => $this->type,
            'link' => $this->link,
            'provider' => $this->provider,
        ];
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'waypoint' => $this->waypoint,
            // 'elevation' => $this->elevation,
            // 'country' => $this->country,
            // 'position' => $this->position,
            // 'lat' => $this->lat,
            // 'lon' => $this->lon,
            // 'name' => $this->name,
            // 'owner' => $this->owner,
            // 'type' => $this->type,
            // 'country_name' => $this->country_name,
            // 'link' => $this->link,
            // 'added_on_datetime' => $this->added_on_datetime,
            // 'updated_on_datetime' => $this->updated_on_datetime,
            // 'status' => $this->status,
            // 'provider' => $this->provider,
        ];
    }
}
