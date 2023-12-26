<?php

namespace GeoKrety\Service\Xml;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Markdown;

class GeokretyExport extends GeokretyBaseExport {
    public function addGeokret(Geokret &$geokret) {
        $xml = $this->xml;

        $xml->startElement('geokret');
        $xml->writeAttribute('id', $geokret->gkid());

        $xml->startElement('name');
        $xml->writeCData($geokret->name);
        $xml->endElement();

        $xml->startElement('description');
        $xml->writeCData(Markdown::toText($geokret->mission));
        $xml->endElement();

        $xml->startElement('description_html');
        $xml->writeCData(Markdown::toHtml($geokret->mission));
        $xml->endElement();

        $xml->startElement('description_markdown');
        $xml->writeCData($geokret->mission);
        $xml->endElement();

        if (!is_null($geokret->owner)) {
            $xml->startElement('owner');

            $xml->writeAttribute('id', $geokret->owner);

            $xml->startCData();
            $xml->text($geokret->owner_username);
            $xml->endCdata();

            $xml->endElement(); // owner
        }

        $xml->writeElement('datecreated', $geokret->created_on_datetime->format('Y-m-d H:i:s'));
        $xml->writeElement('datecreated_Iso8601', $geokret->created_on_datetime->format(\DateTime::ATOM));

        $xml->writeElement('distancetravelled', $geokret->distance);
        $xml->writeAttribute('note', 'legacy bug compatibility');
        $xml->writeElement('distancetraveled', $geokret->distance);
        if (!is_null($geokret->last_position)) {
            $xml->writeElement('state', $geokret->move_type->getLogTypeId());
        }
        $xml->writeElement('missing', (int) $geokret->isMissing());

        $xml->startElement('position');
        if (!is_null($geokret->last_position)) {
            $xml->writeAttribute('latitude', $geokret->lat ?? '');
            $xml->writeAttribute('longitude', $geokret->lon ?? '');
        }
        $xml->endElement(); // position

        $xml->startElement('waypoints');
        $xml->startElement('waypoint');
        $xml->writeCdata($geokret->waypoint ?? '');
        $xml->endElement(); // waypoint
        $xml->endElement(); // waypoints

        $xml->startElement('type');
        $xml->writeAttribute('id', $geokret->type->getTypeId());
        $xml->writeCdata($geokret->type->getTypeString());
        $xml->endElement(); // type

        $xml->endElement(); // geokret
    }

    public function addMove(\GeoKrety\Model\Move &$move) {
        $xml = $this->xml;

        $xml->startElement('moves');
        $xml->writeAttribute('id', $move->id);

        $xml->startElement('geokret');
        // Not following relation prevent a memory leak bug
        $geokret = new Geokret();
        $geokret->load(['id = ?', $move->getRaw('geokret')]);
        $xml->writeAttribute('id', $geokret->id);
        $xml->writeCdata($geokret->name);
        $xml->endElement(); // geokret

        $xml->startElement('position');
        if (!is_null($move->lat) && !is_null($move->lon)) {
            $xml->writeAttribute('latitude', $move->lat);
            $xml->writeAttribute('longitude', $move->lon);
        }
        $xml->endElement(); // position

        if (!is_null($move->waypoint) && !empty($move->waypoint)) {
            $xml->startElement('waypoints');
            $xml->startElement('waypoint');
            $xml->writeCdata($move->waypoint);
            $xml->endElement(); // waypoint
            $xml->endElement(); // waypoints
        }

        $xml->startElement('date');
        $xml->writeAttribute('moved', $move->moved_on_datetime->format('Y-m-d H:i:s'));
        $xml->writeAttribute('logged', $move->created_on_datetime->format('Y-m-d H:i:s'));
        $xml->writeAttribute('edited', $move->updated_on_datetime->format('Y-m-d H:i:s'));
        $xml->endElement(); // date

        $xml->startElement('date_Iso8601');
        $xml->writeAttribute('moved', $move->moved_on_datetime->format(\DateTime::ATOM));
        $xml->writeAttribute('logged', $move->created_on_datetime->format(\DateTime::ATOM));
        $xml->writeAttribute('edited', $move->updated_on_datetime->format(\DateTime::ATOM));
        $xml->endElement(); // date

        $this->addUser($move);

        $xml->startElement('comment');
        $xml->writeCdata(Markdown::toText($move->comment));
        $xml->endElement(); // comment

        $xml->startElement('comment_html');
        $xml->writeCdata(Markdown::toHtml($move->comment));
        $xml->endElement(); // comment

        $xml->startElement('comment_markdown');
        $xml->writeCdata($move->comment);
        $xml->endElement(); // comment

        $xml->startElement('logtype');
        $xml->writeAttribute('id', $move->move_type->getLogTypeId());
        $xml->text($move->move_type->getLogTypeString());
        $xml->endElement(); // logtype

        $xml->endElement(); // moves
    }
}
