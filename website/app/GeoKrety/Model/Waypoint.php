<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

class Waypoint extends Base {
    protected $db = 'DB';
    protected $table = 'gk-waypointy';
    protected $ttl = GK_SITE_CACHE_TTL_WAYPOINT;

    protected $fieldConf = array(
        'waypoint' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
        ),
        'lat' => array(
            'type' => Schema::DT_DOUBLE,
            'nullable' => true,
        ),
        'lon' => array(
            'type' => Schema::DT_DOUBLE,
            'nullable' => true,
        ),
        'alt' => array(
            'type' => Schema::DT_INT2,
            'default' => '-32768',
            'nullable' => false,
        ),
        'country' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'name' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'owner' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'type' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'country_name' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'link' => array(
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
        ),
        'status' => array(
            'type' => Schema::DT_INT1,
            'default' => '1',
            'nullable' => false,
        ),
        'updated_on_datetime' => array(
            'type' => Schema::DT_DATETIME,
            'default' => 'CURRENT_TIMESTAMP',
            'nullable' => false,
        ),
    );

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
        $response = array(
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
        );

        return $response;
    }
}
