<?php

namespace GeoKrety\Service\Xml;

class GeokretyExportOC extends GeokretyBaseExport {
    public function addGeokret(\GeoKrety\Model\Geokret &$geokret) {
        $xml = $this->xml;

        $xml->startElement('geokret');
        $xml->writeAttribute('id', $geokret->gkid());
        $xml->startElement('name');
        $xml->writeCdata($geokret->name);
        $xml->endElement(); // name

        $xml->writeElement('distancetravelled', $geokret->distance);

        if (!is_null($geokret->last_position)) {
            $xml->writeElement('state', $geokret->move_type->isTheoricallyInCache() ? '1' : '0');
            if (!is_null($geokret->lat) && !is_null($geokret->lon)) {
                $xml->startElement('position');
                $xml->writeAttribute('latitude', $geokret->lat);
                $xml->writeAttribute('longitude', $geokret->lon);
                $xml->endElement(); // position
            }
            if (!is_null($geokret->waypoint) && !empty($geokret->waypoint)) {
                $xml->startElement('waypoints');
                $xml->startElement('waypoint');
                $xml->writeCdata($geokret->waypoint);
                $xml->endElement(); // waypoint
                $xml->endElement(); // waypoints
            }
        }

        $xml->endElement(); // geokret
    }
}
