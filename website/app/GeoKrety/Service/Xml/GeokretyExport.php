<?php

namespace GeoKrety\Service\Xml;

use GeoKrety\Service\Markdown;

class GeokretyExport extends GeokretyBase {
    public function addGeokret(\GeoKrety\Model\Geokret &$geokret) {
        $gk = $this->xml->addChild('geokret');
        $gk->addAttribute('id', $geokret->id);
        $gk->addChildWithCDATA('name', $geokret->name);
        $gk->addChildWithCDATA('description', Markdown::toText($geokret->mission));
        $gk->addChildWithCDATA('description_html', Markdown::toHtml($geokret->mission));
        $gk->addChildWithCDATA('description_markdown', $geokret->mission);
        $owner = $gk->addChildWithCDATA('owner', $geokret->owner->username);
        $owner->addAttribute('id', $geokret->owner->id);
        $gk->addChild('datecreated', $geokret->created_on_datetime->format('Y-m-d H:i:s'));
        $gk->addChild('distancetravelled', $geokret->distance);
        $gk->addChild('state', $geokret->last_position->logtype->getLogTypeId());
        $gk->addChild('missing', $geokret->missing);
        $position = $gk->addChild('position');
        if (!is_null($geokret->last_position->lat) && !is_null($geokret->last_position->lon)) {
            $position->addAttribute('latitude', $geokret->last_position->lat);
            $position->addAttribute('longitude', $geokret->last_position->lon);
        }
        if (!is_null($geokret->last_position->waypoint) && !empty($geokret->last_position->waypoint)) {
            $wpts = $gk->addChild('waypoints');
            $wpts->addChildWithCDATA('waypoint', $geokret->last_position->waypoint);
        }
        $type = $gk->addChildWithCDATA('type', $geokret->type->getTypeString());
        $type->addAttribute('id', $geokret->type->getTypeId());
    }

    public function addMove(\GeoKrety\Model\Move &$move) {
        $log = $this->xml->addChild('moves');
        $log->addAttribute('id', $move->id);
        $gk = $log->addChildWithCDATA('geokret', $move->geokret->name);
        $gk->addAttribute('id', $move->geokret->id);
        $position = $log->addChild('position');
        if (!is_null($move->lat) && !is_null($move->lon)) {
            $position->addAttribute('latitude', $move->lat);
            $position->addAttribute('longitude', $move->lon);
        }
        if (!is_null($move->waypoint) && !empty($move->waypoint)) {
            $wpts = $log->addChild('waypoints');
            $wpts->addChildWithCDATA('waypoint', $move->waypoint);
        }
        $dates = $log->addChild('date');
        $dates->addAttribute('moved', $move->moved_on_datetime->format('Y-m-d H:i:s'));
        $dates->addAttribute('logged', $move->created_on_datetime->format('Y-m-d H:i:s'));
        $dates->addAttribute('edited', $move->updated_on_datetime->format('Y-m-d H:i:s'));
        $user = $log->addChildWithCDATA('user', $move->author->username);
        $user->addAttribute('id', $move->author->id);
        $log->addChildWithCDATA('comment', Markdown::toText($move->comment));
        $log->addChildWithCDATA('comment_html', Markdown::toHtml($move->comment));
        $log->addChildWithCDATA('comment_markdown', $move->comment);
        $logtype = $log->addChildWithCDATA('logtype', $move->logtype->getLogTypeString());
        $logtype->addAttribute('id', $move->logtype->getLogTypeId());
    }
}
