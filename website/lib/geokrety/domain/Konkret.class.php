<?php

namespace Geokrety\Domain;

class Konkret {
    public $id;
    public $trackingCode;
    public $name;
    public $description;
    // public $url; // TODO update ldjson
    public $ownerId; // TODO update ldjson
    public $ownerName='xxx'; // TODO update ldjson
    // public $authorUrl; // TODO update ldjson
    public $datePublished;
    public $type;
    public $typeString;
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

    public function enrichFields() {
        $this->typeString = $this->getTypeString();
    }

    public function getTypeString() {
        switch ($this->type) {
            case 0: return _('traditional');
            case 1: return _('book/cd/dvd');
            case 2: return _('human');
            case 3: return _('coin');
            case 4: return _('kretypost');
            default: return null;
        }
    }

    public function isOwner($userId) {
      return !is_null($userId) && $userId === $self->ownerId;
    }
}
