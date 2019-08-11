<?php

namespace GeoKrety\Service\Xml;

class GeokretyExport2 extends GeokretyBase {
    public function __construct() {
        parent::__construct();
        $this->xmlGeokrety = $this->xml->addChild('geokrety');
    }

    public function addGeokret(\GeoKrety\Model\Geokret &$geokret) {
        $this->addGeokretWithTrackingCode($geokret);
    }

    public function addGeokretWithTrackingCode(\GeoKrety\Model\Geokret &$geokret, ?\GeoKrety\Model\User &$user = null) {
        $gk = $this->xmlGeokrety->addChildWithCDATA('geokret', $geokret->name);
        $gk->addAttribute('id', $geokret->id);
        $gk->addAttribute('type', $geokret->type->getTypeId());
        if (!is_null($user) && $user->id === $geokret->holder->id) {
            $gk->addAttribute('nr', $geokret->tracking_code);
        }
        if ($geokret->missing) {
            $gk->addAttribute('missing', $geokret->missing);
        }
        $gk->addAttribute('owner_id', $geokret->owner->id);
        $gk->addAttribute('ownername', $geokret->owner->username);
        if ($geokret->holder) {
            $gk->addAttribute('holder_id', $geokret->holder->id);
            $gk->addAttribute('holdername', $geokret->holder->username);
        }
        $gk->addAttribute('dist', $geokret->distance);
        if (!is_null($geokret->last_position)) {
            $gk->addAttribute('date', $geokret->last_position->created_on_datetime->format('Y-m-d'));
            if ($geokret->last_position->logtype->isTheoricallyInCache()) {
                $gk->addAttribute('lat', $geokret->last_position->lat);
                $gk->addAttribute('lon', $geokret->last_position->lon);
                if (!is_null($geokret->last_position->waypoint) && !empty($geokret->last_position->waypoint)) {
                    $gk->addAttribute('waypoint', $geokret->last_position->waypoint);
                }
            }
            $gk->addAttribute('state', $geokret->last_position->logtype->getLogTypeId());
            $gk->addAttribute('last_pos_id', $geokret->last_position->id);
            $gk->addAttribute('last_log_id', $geokret->last_log->id);
        } else {
            $gk->addAttribute('date', $geokret->created_on_datetime->format('Y-m-d'));
        }
        $gk->addAttribute('places', $geokret->caches_count);
        // TODO
        // if ($geokret->avatar->filename) {
        //     $gk->addAttribute('image', $geokret->avatar->filename);
        // }
    }
}
