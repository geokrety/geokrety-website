<?php

namespace GeoKrety\Model;

class Waypoint extends Base {
    protected $db = 'DB';
    protected $table = 'gk-waypointy';
    protected $ttl = GK_SITE_CACHE_TTL_WAYPOINT;

    public function get_updated_on_datetime($value) {
        return self::get_date_object($value);
    }
}
