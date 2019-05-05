<?php

namespace Geokrety\Domain;

class Konkret {
    public $id;
    public $trackingCode;
    public $name;
    public $description;
    // public $url; // TODO update ldjson
    public $ownerId; // TODO update ldjson
    // public $ownerName; // TODO update ldjson
    // public $authorUrl; // TODO update ldjson
    public $datePublished;
    public $type;
    public $distance;
    public $cachesCount;
    public $picturesCount;
    public $avatarId; // TODO update ldjson
    public $lastPositionId;
    public $lastLogId;
    public $missing;
    public $keywords;
    public $ratingCount;
    public $ratingAvg;
    public $konkretLogs;

    public function getUrl() {
      // TODO
    }

    public function getAuthorUrl() {
      // TODO
    }

    public function getAvatarUrl() {
      // TODO
    }
}
