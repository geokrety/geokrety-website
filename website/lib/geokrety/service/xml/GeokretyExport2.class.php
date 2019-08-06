<?php

namespace Geokrety\Service\Xml;

class GeokretyExport2 extends GeokretyBase {
    public function __construct() {
        parent::__construct();
        $this->xmlGeokrety = $this->xml->addChild('geokrety');
    }

    public function addGeokret(\Geokrety\Domain\Konkret &$geokret) {
        $this->addGeokretWithTrackingCode($geokret);
    }

    public function addGeokretWithTrackingCode(\Geokrety\Domain\Konkret &$geokret, ?\Geokrety\Domain\User &$user = null) {
        $gk = $this->xmlGeokrety->addChildWithCDATA('geokret', $geokret->name);
        $gk->addAttribute('id', $geokret->id);
        $gk->addAttribute('type', $geokret->type);
        if (!is_null($user) && $user->id === $geokret->holderId) {
            $gk->addAttribute('nr', $geokret->trackingCode);
        }
        if ($geokret->missing) {
            $gk->addAttribute('missing', $geokret->missing);
        }
        $gk->addAttribute('owner_id', $geokret->ownerId);
        $gk->addAttribute('ownername', $geokret->ownerName);
        if ($geokret->holderId) {
            $gk->addAttribute('holder_id', $geokret->holderId);
            $gk->addAttribute('holdername', $geokret->holderName);
        }
        $gk->addAttribute('dist', $geokret->distance);
        if (!is_null($geokret->lastPosition)) {
            $gk->addAttribute('date', $geokret->lastPosition->getDate('Y-m-d'));
        } else {
            $gk->addAttribute('date', $geokret->getDatePublished('Y-m-d'));
        }
        if ($geokret->lastPosition) {
            if ($geokret->lastPosition->logType->isTheoricallyInCache()) {
                $gk->addAttribute('lat', $geokret->lastPosition->lat);
                $gk->addAttribute('lon', $geokret->lastPosition->lon);
                $gk->addAttribute('waypoint', $geokret->lastPosition->waypoint);
            }
            $gk->addAttribute('state', $geokret->lastPosition->logType->getLogTypeId());
            $gk->addAttribute('last_pos_id', $geokret->lastPositionId);
            $gk->addAttribute('last_log_id', $geokret->lastLogId);
        }
        $gk->addAttribute('places', $geokret->cachesCount);
        if ($geokret->avatarFilename) {
            $gk->addAttribute('image', $geokret->avatarFilename);
        }
    }
}
