<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class Waypoint extends Base {
    protected $db = 'DB';
    protected $table = 'gk-waypointy';
    protected $ttl = GK_SITE_CACHE_TTL_WAYPOINT;

    protected $fieldConf = [
        'waypoint' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ],
        'lat' => [
            'type' => Schema::DT_DOUBLE,
            'nullable' => true,
        ],
        'lon' => [
            'type' => Schema::DT_DOUBLE,
            'nullable' => true,
        ],
        'alt' => [
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
        'status' => [
            'type' => Schema::DT_INT1,
            'default' => '1',
            'nullable' => false,
        ],
        'updated_on_datetime' => [
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ],
    ];

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }

    public function get_lat($value) {
        return number_format(floatval($value), 5, '.', '');
    }

    public function get_lon($value) {
        return number_format(floatval($value), 5, '.', '');
    }

    public function asArray() {
        $response = [
            'waypoint' => $this->waypoint,
            'latitude' => $this->lat,
            'longitude' => $this->lon,
            'altitude' => $this->alt,
            'country' => $this->country_name,
            'countryCode' => $this->country,
            'name' => $this->name,
            'owner' => $this->owner,
            'status' => $this->status,
            'typeName' => $this->type,
            'link' => $this->link,
        ];

        return $response;
    }
}
