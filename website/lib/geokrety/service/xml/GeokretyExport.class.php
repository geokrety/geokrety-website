<?php

namespace Geokrety\Service\Xml;

class GeokretyExport extends GeokretyBase {

    public function addGeokret(\Geokrety\Domain\Konkret $geokret) {
        $gk = $this->xml->addChild('geokret');
        $gk->addAttribute('id', $geokret->id);
        $gk->addChildWithCDATA('name', $geokret->name);
        $gk->addChildWithCDATA('description', $geokret->description);
        $owner = $gk->addChildWithCDATA('owner', $geokret->ownerName);
        $owner->addAttribute('id', $geokret->ownerId);
        $gk->addChild('datecreated', $geokret->getDatePublished());
        $gk->addChild('distancetravelled', $geokret->distance);
        $gk->addChild('state', $geokret->lastPosition->logType->isTheoricallyInCache() ? '1' : '0');
        $gk->addChild('missing', $geokret->missing);
        $position = $gk->addChild('position');
        $position->addAttribute('latitude', $geokret->lastPosition->lat);
        $position->addAttribute('longitude', $geokret->lastPosition->lon);
        $wpts = $gk->addChild('waypoints');
        $wpts->addChildWithCDATA('waypoint', $geokret->lastPosition->waypoint);
        $type = $gk->addChildWithCDATA('type', $geokret->getTypeString());
        $type->addAttribute('id', $geokret->type);
    }

    public function addMove(\Geokrety\Domain\TripStep $tripStep) {
        $trip = $this->xml->addChild('moves');
        $trip->addAttribute('id', $tripStep->ruchId);
        $gk = $trip->addChildWithCDATA('geokret', $tripStep->geokret->name);
        $gk->addAttribute('id', $tripStep->geokret->id);
        $position = $trip->addChild('position');
        $position->addAttribute('latitude', $tripStep->lat);
        $position->addAttribute('longitude', $tripStep->lon);
        $wpts = $trip->addChild('waypoints');
        $wpts->addChildWithCDATA('waypoint', $tripStep->waypoint);
        $dates = $trip->addChild('date');
        $dates->addAttribute('moved', $tripStep->getDate());
        $dates->addAttribute('logged', $tripStep->getDateLogged());
        $user = $trip->addChildWithCDATA('user', $tripStep->author()->username);
        $user->addAttribute('id', $tripStep->author()->id);
        $trip->addChildWithCDATA('comment', $tripStep->comment);
        $logtype = $trip->addChildWithCDATA('logtype', $tripStep->logType->getLogTypeString());
        $logtype->addAttribute('id', $tripStep->logType->getLogTypeId());
    }
}
