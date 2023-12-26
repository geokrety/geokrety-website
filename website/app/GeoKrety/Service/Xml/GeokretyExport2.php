<?php

namespace GeoKrety\Service\Xml;

class GeokretyExport2 extends GeokretyBaseExport {
    public function __construct(bool $streamXML = false, ?string $compress = null, $filename = 'out.xml') {
        parent::__construct($streamXML, $compress, $filename);
        $this->xml->startElement('geokrety');
    }

    public function end() {
        $this->xml->endElement();
        parent::end();
    }

    public function addGeokret(\GeoKrety\Model\Geokret &$geokret) {
        $xml = $this->xml;

        $xml->startElement('geokret');
        $xml->writeAttribute('id', $geokret->gkid());
        $xml->writeAttribute('type', $geokret->type->getTypeId());

        if ($geokret->hasTouchedInThePast()) {
            $xml->writeAttribute('nr', $geokret->tracking_code);
        }
        if ($geokret->isMissing()) {
            $xml->writeAttribute('missing', $geokret->isMissing());
        }

        if ($geokret->owner) {
            $xml->writeAttribute('owner_id', $geokret->owner);
            $xml->writeAttribute('ownername', $geokret->owner_username ?? '');
        }
        if ($geokret->holder) {
            $xml->writeAttribute('holder_id', $geokret->holder);
            $xml->writeAttribute('holdername', $geokret->holder_username ?? '');
        }
        $xml->writeAttribute('dist', $geokret->distance);
        if (!is_null($geokret->position)) {
            $xml->writeAttribute('date', $geokret->moved_on_datetime->format('Y-m-d'));
            if ($geokret->move_type->isTheoricallyInCache()) {
                $xml->writeAttribute('lat', $geokret->lat);
                $xml->writeAttribute('lon', $geokret->lon);
                if (!is_null($geokret->waypoint)) {
                    $xml->writeAttribute('waypoint', $geokret->waypoint);
                }
            }
            $xml->writeAttribute('state', $geokret->move_type->getLogTypeId());
            $xml->writeAttribute('last_pos_id', $geokret->last_position);
            $xml->writeAttribute('last_log_id', $geokret->last_log);
        } else {
            $xml->writeAttribute('date', $geokret->created_on_datetime->format('Y-m-d'));
        }
        $xml->writeAttribute('places', $geokret->caches_count);
        if (!is_null($geokret->avatar_key)) {
            $xml->writeAttribute('image', $geokret->avatar_key);
        }
        $xml->writeCdata($geokret->name);
        $this->xml->endElement(); // geokret
    }
}
