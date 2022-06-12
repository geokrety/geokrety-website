<?php

namespace GeoKrety\Service\Xml;

use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;

abstract class GeokretyBaseExport extends Base {
    abstract public function addGeokret(Geokret &$geokret);

    public function addGeokrety(array $geokrety) {
        foreach ($geokrety as $geokret) {
            $this->addGeokret($geokret);
        }
    }

    protected function addUser(Move &$move): void {
        $xml = $this->xml;
        $xml->startElement('user');
        if (!is_null($move->author)) {
            $xml->writeAttribute('id', $move->author->id);
            $xml->writeCdata($move->author->username);
        } else {
            $xml->writeAttribute('id', null);
            $xml->writeCdata($move->username);
        }
        $xml->endElement(); // user
    }

    public function addPictures(Geokret &$geokret) {
        if ($geokret->pictures_count < 1) {
            return;
        }
        $xml = $this->xml;
        $xml->startElement('pictures');
        foreach ($geokret->avatars as $picture) {
            $xml->startElement('picture');
            $xml->writeAttribute('id', $picture->id);
            $xml->writeAttribute('type_id', $picture->type->getTypeId());
            $xml->writeAttribute('type', $picture->type->getTypeString());
            $xml->writeAttribute('author_id', $picture->author->id);
            $xml->writeAttribute('author', $picture->author->username);
            $xml->writeAttribute('url', $picture->url);
            $xml->writeAttribute('thumbnail_url', $picture->thumbnail_url);
            if ($picture->isMainAvatar()) {
                $xml->writeAttribute('main', 'true');
            }
            $xml->endElement(); // picture
        }
        $xml->endElement(); // pictures
    }
}
