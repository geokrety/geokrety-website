<?php

namespace GeoKrety\Model;

class WaypointGC extends BaseWaypoint {
    protected $table = 'gk_waypoints_gc';

    public function asArray(): array {
        // TODO make this an entity - see also WaypointOC
        return [
            'waypoint' => $this->waypoint,
            'latitude' => $this->lat,
            'longitude' => $this->lon,
            'elevation' => $this->elevation,
            'countryCode' => $this->country,
        ];
    }
}
