<?php

namespace Geokrety\Service\Xml;

class GeokretyExportOC extends GeokretyBase {

    public function addGeokret(\Geokrety\Domain\Konkret &$geokret) {
        $gk = $this->xml->addChild('geokret');
        $gk->addAttribute('id', $geokret->id);
        $gk->addChildWithCDATA('name', $geokret->name);
        $gk->addChild('distancetravelled', $geokret->distance);
        $gk->addChild('state', $geokret->lastPosition->logType->isTheoricallyInCache() ? '1' : '0');
        $position = $gk->addChild('position');
        $position->addAttribute('latitude', $geokret->lastPosition->lat);
        $position->addAttribute('longitude', $geokret->lastPosition->lon);
        $wpts = $gk->addChild('waypoints');
        $wpt = $wpts->addChildWithCDATA('waypoint', $geokret->lastPosition->waypoint);
    }
}
