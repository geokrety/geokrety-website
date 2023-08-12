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

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'waypoint' => $this->waypoint,
            // 'elevation' => $this->elevation,
            // 'country' => $this->country,
            // 'position' => $this->position,
            // 'lat' => $this->lat,
            // 'lon' => $this->lon,
        ];
    }
}
