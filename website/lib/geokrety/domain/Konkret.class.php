<?php

namespace Geokrety\Domain;

class Konkret extends AbstractObject {
    public $id;
    public $trackingCode;
    public $name;
    public $description;
    // public $url; // TODO update ldjson
    public $ownerId; // TODO update ldjson
    public $ownerName; // TODO update ldjson
    public $ownerEmail;
    // public $authorUrl; // TODO update ldjson
    public $holderId;
    public $holderName;
    public $datePublished;
    public $type;
    public $typeString;
    public $distance;
    public $cachesCount;
    public $picturesCount;
    public $avatarId; // TODO update ldjson
    public $avatarFilename; // TODO update ldjson
    public $avatarCaption;
    public $lastPositionId;
    public $lastLogId;
    public $missing;
    public $keywords;
    public $ratingCount;
    public $ratingAvg;
    public $konkretLogs;
    public $lastLog;
    public $lastPosition;

    public function getGKId() {
        return gkid($this->id);
    }

    public function getUrl($anchor = null) {
        $anchor_ = '';
        if (!is_null($anchor)) {
            $anchor_ = '#'.$anchor;
        }

        return '/konkret.php?id='.$this->id.$anchor_;
    }

    public function editUrl() {
        return '/edit.php?co=geokret&id='.$this->id;
    }

    public function getAuthorUrl() {
        // TODO
    }

    public function setDatePublished($date, $format = 'Y-m-d H:i:s') {
        if (is_a($date, '\Datetime')) {
            $this->datePublished = $date;
        }
        $this->datePublished = \DateTime::createFromFormat($format, $date, new \DateTimeZone('UTC'));
    }

    public function getDatePublished() {
        if (is_a($this->datePublished, '\Datetime')) {
            return $this->datePublished->format('Y-m-d H:i:s');
        }

        return $this->datePublished;
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

    public function owner() {
        $user = new User();
        $user->id = $this->ownerId;
        $user->username = $this->ownerName;
        $user->filename = $this->avatarFilename;
        $user->email = $this->ownerEmail;

        return $user;
    }

    public function holder() {
        $user = new User();
        $user->id = $this->holderId;
        $user->username = $this->holderName;

        return $user;
    }

    public function avatar() {
        $picture = new PictureGeoKret();
        $picture->id = $this->avatarId;
        $picture->tripId = $this->id; // Isn't there an error in the db schema?
        $picture->type = PICTURE_AVATAR;
        $picture->geokretId = $this->id;
        $picture->name = $this->name;
        $picture->userId = $this->ownerId;
        $picture->filename = $this->avatarFilename;
        $picture->caption = $this->avatarCaption;
        $picture->isGkAvatar = true;

        return $picture;
    }

    public function update() {
        $geokretR = new \Geokrety\Repository\KonkretRepository(\GKDB::getLink());

        return $geokretR->updateGeokret($this);
    }

    public function isOwner() {
        return $_SESSION['isLoggedIn'] && $_SESSION['currentUser'] === $this->ownerId || $_SESSION['isSuperUser'];
    }

    public function hasCurrentUserSeenGeokretId() {
        if ($this->isOwner()) {
            return true;
        }
        $tripR = new \Geokrety\Repository\TripRepository(\GKDB::getLink());

        return $tripR->hasCurrentUserSeenGeokretId($this->id);
    }
}
