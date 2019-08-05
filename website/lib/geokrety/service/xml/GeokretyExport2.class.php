<?php

namespace Geokrety\Service\Xml;

class GeokretyExport2 extends GeokretyBase {

    public function addGeokret(\Geokrety\Domain\Konkret $geokret) {
        $gk = $this->xml->addChildWithCDATA('geokret', $geokret->name);
        $gk->addAttribute('id', $geokret->id);
        $gk->addAttribute('dist', $geokret->distance);
        $gk->addAttribute('lat', $geokret->lastPosition->lat);
        $gk->addAttribute('lon', $geokret->lastPosition->lon);
        $gk->addAttribute('waypoint', $geokret->lastPosition->waypoint);
        $gk->addAttribute('owner_id', $geokret->ownerId);
        $gk->addAttribute('state', $geokret->lastPosition->logType->isTheoricallyInCache() ? '1' : '0');
        $gk->addAttribute('type', $geokret->type);
        $gk->addAttribute('last_pos_id', $geokret->lastPositionId);
        $gk->addAttribute('last_log_id', $geokret->lastLogId);
        $gk->addAttribute('image', $geokret->avatarFilename);
    }
}
