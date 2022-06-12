<?php

namespace GeoKrety\Service\Xml;

use DateTimeInterface;
use GeoKrety\Model\Geokret;
use GeoKrety\Service\Markdown;

class GeokretyExport2Details extends GeokretyExport2 {
    public function addGeokret(Geokret &$geokret) {
        $xml = $this->xml;

        $xml->startElement('geokret');
        $xml->writeAttribute('id', $geokret->gkid());

        $xml->startElement('name');
        $xml->writeCdata($geokret->name);
        $xml->endElement(); // name

        $xml->startElement('description');
        $xml->writeCData(Markdown::toText($geokret->mission));
        $xml->endElement(); // description

        if ($geokret->owner) {
            $xml->startElement('owner');
            $xml->writeAttribute('id', $geokret->owner);
            $xml->writeCData($geokret->owner_username);
            $xml->endElement(); // owner
        }
        if ($geokret->holder) {
            $xml->startElement('holder');
            $xml->writeAttribute('id', $geokret->holder);
            $xml->writeCData($geokret->holder_username);
            $xml->endElement(); // holder
        }

        $xml->startElement('datecreated');
        $xml->writeCData($geokret->created_on_datetime->format('Y-m-d H:i:s'));
        $xml->endElement(); // datecreated

        $xml->startElement('datecreated_Iso8601');
        $xml->writeCData($geokret->created_on_datetime->format(DateTimeInterface::ATOM));
        $xml->endElement(); // datecreated_Iso8601

        $xml->startElement('dateupdated');
        $xml->writeCData($geokret->updated_on_datetime->format('Y-m-d H:i:s'));
        $xml->endElement(); // dateupdated

        $xml->startElement('datecreated_Iso8601');
        $xml->writeCData($geokret->updated_on_datetime->format(DateTimeInterface::ATOM));
        $xml->endElement(); // datecreated_Iso8601

        $xml->startElement('distancetraveled');
        $xml->writeAttribute('unit', 'km');
        $xml->writeCData($geokret->distance);
        $xml->endElement(); // distancetraveled

        $xml->startElement('places');
        $xml->writeCData($geokret->caches_count);
        $xml->endElement(); // places

        $xml->startElement('state');
        $xml->writeAttribute('last_pos_id', $geokret->last_position);
        $xml->writeAttribute('last_log_id', $geokret->last_log);
        $xml->writeCData($geokret->move_type->getLogTypeId());
        $xml->endElement(); // state

        $xml->startElement('missing');
        $xml->writeCData((int) $geokret->missing);
        $xml->endElement(); // missing

        if (!is_null($geokret->waypoint)) {
            $xml->startElement('position');
            $xml->writeAttribute('latitude', $geokret->lat ?? '');
            $xml->writeAttribute('longitude', $geokret->lon ?? '');
            $xml->endElement(); // position

            $xml->startElement('waypoints');
            $xml->startElement('waypoint');
            $xml->writeCData($geokret->waypoint);
            $xml->endElement(); // waypoint
            $xml->endElement(); // waypoints
        }

        $xml->startElement('type');
        $xml->writeAttribute('id', $geokret->type->getTypeId());
        $xml->writeCdata($geokret->type->getTypeString());
        $xml->endElement(); // type

        if (!is_null($geokret->avatar_key)) {
            $xml->startElement('image');
            $xml->writeCdata($geokret->avatar_key);
            $xml->endElement(); // image
        }
        $this->addPictures($geokret);

        $this->addMoves($geokret);

        $xml->endElement(); // geokret
    }

    public function addMoves(Geokret &$geokret) {
        $xml = $this->xml;

        $xml->startElement('moves');
        $xml->writeAttribute('only_last', GK_API_EXPORT_GEOKRET_DETAILS_MOVES_LIMIT);
        foreach ($geokret->moves as $move) {
            $xml->startElement('move');

            $xml->startElement('id');
            $xml->writeCdata($move->id);
            $xml->endElement(); // id

            $xml->startElement('date');
            $xml->writeAttribute('moved', $move->moved_on_datetime->format('Y-m-d H:i:s'));
            $xml->writeAttribute('moved_Iso8601', $move->moved_on_datetime->format(DateTimeInterface::ATOM));
            $xml->endElement(); // date

            $xml->startElement('user');
            $xml->writeAttribute('id', $move->author->id);
            $xml->writeCdata($move->author->username);
            $xml->endElement(); // user

            $xml->startElement('application');
            $xml->writeAttribute('name', $move->app);
            $xml->writeAttribute('version', $move->app_ver);
            $xml->endElement(); // application

            $xml->startElement('comment');
            $xml->writeCdata($move->comment);
            $xml->endElement(); // comment

            $xml->startElement('logtype');
            $xml->writeAttribute('id', $move->move_type->getLogTypeId());
            $xml->writeCdata($move->move_type->getLogTypeString());
            $xml->endElement(); // logtype

            $xml->startElement('distancetraveled');
            $xml->writeAttribute('unit', 'km');
            $xml->writeCdata($move->distance);
            $xml->endElement(); // distancetraveled

            if ($move->comments_count) {
                $xml->startElement('comments');
                /** @var \GeoKrety\Model\MoveComment $comment */
                foreach ($move->comments as $comment) {
                    $xml->startElement('comment');
                    $xml->writeAttribute('type', $comment->type == 0 ? 'comment' : 'missing');

                    $xml->startElement('user');
                    $xml->writeAttribute('id', $comment->author->id);
                    $xml->writeCdata($comment->author->username);
                    $xml->endElement(); // user

                    $xml->startElement('message');
                    $xml->writeCdata($comment->content);
                    $xml->endElement(); // message

                    $xml->startElement('date');
                    $xml->writeAttribute('commented', $comment->created_on_datetime->format('Y-m-d H:i:s'));
                    $xml->writeAttribute('commented_Iso8601', $comment->created_on_datetime->format(DateTimeInterface::ATOM));
                    $xml->endElement(); // date

                    $xml->endElement(); // comment
                }
                $xml->endElement(); // comments
            }
            $xml->endElement(); // move
        }
        $xml->endElement(); // moves
    }
}
