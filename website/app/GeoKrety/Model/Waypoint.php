<?php

namespace GeoKrety\Model;

class Waypoint extends Base {
    protected $db = 'DB';
    protected $table = 'gk-waypointy';
    protected $ttl = GK_SITE_CACHE_TTL_WAYPOINT;

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
