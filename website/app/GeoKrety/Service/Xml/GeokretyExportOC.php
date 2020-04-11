<?php

namespace GeoKrety\Service\Xml;

class GeokretyExportOC extends GeokretyBase {
    public function addGeokret(\GeoKrety\Model\Geokret &$geokret) {
        $gk = $this->xml->addChild('geokret');
        $gk->addAttribute('id', $geokret->gkid());
        $gk->addChildWithCDATA('name', $geokret->name);
        $gk->addChild('distancetravelled', $geokret->distance);
        if (!is_null($geokret->last_position)) {
            $gk->addChild('state', $geokret->last_position->move_type->isTheoricallyInCache() ? '1' : '0');
            if (!is_null($geokret->last_position->lat) && !is_null($geokret->last_position->lon)) {
                $position = $gk->addChild('position');
                $position->addAttribute('latitude', $geokret->last_position->lat);
                $position->addAttribute('longitude', $geokret->last_position->lon);
            }
            if (!is_null($geokret->last_position->waypoint) && !empty($geokret->last_position->waypoint)) {
                $wpts = $gk->addChild('waypoints');
                $wpt = $wpts->addChildWithCDATA('waypoint', $geokret->last_position->waypoint);
            }
        }
    }
}
