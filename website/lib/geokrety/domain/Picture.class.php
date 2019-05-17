<?php

namespace Geokrety\Domain;

define(__NAMESPACE__.'\AVATAR', 0);
define(__NAMESPACE__.'\TRIP', 1);
define(__NAMESPACE__.'\USER', 2);

class Picture extends AbstractObject {
    public $id;
    public $tripId;
    public $type;
    public $geokretId;
    public $userId;
    public $filename;
    public $caption;
    public $isGkAvatar;

    public function isAvatar() {
        return $this->isGkAvatar;
    }

    public function isOwner() {
        return $_SESSION['isLoggedIn'] && $_SESSION['currentUser'] === $this->userId;
    }

    public function deleteUrl() {
        return '/edit.php?delete_obrazek='.$this->id.'&confirmed=1';
    }

    public function insert() {
        $pictureR = new \Geokrety\Repository\PictureRepository(\GKDB::getLink());

        return $pictureR->insertPicture($this);
    }

    public function update() {
        $pictureR = new \Geokrety\Repository\PictureRepository(\GKDB::getLink());

        return $pictureR->updatePicture($this);
    }
}

class PictureUser extends Picture {
    public $username;

    public function author() {
        $user = new \Geokrety\Domain\User();
        $user->id = $this->userId;
        $user->username = $this->username;

        return $user;
    }

    public function isAvatar() {
        return $this->type === USER;
    }

    public function editUrl() {
        return '/imgup.php?user&&typ='.$this->type.'&id='.$this->userId.'&rename='.$this->id;
    }
}

class PictureGeoKret extends Picture {
    public $name;

    public function geokret() {
        $geokret = new \Geokrety\Domain\Konkret();
        $geokret->id = $this->geokretId;
        $geokret->name = $this->name;

        return $geokret;
    }

    public function editUrl() {
        return '/imgup.php?geokret&typ='.$this->type.'&id='.$this->geokretId.'&rename='.$this->id;
    }
}

class PictureTrip extends PictureGeoKret {
    public $country;
    public $date;

    public function trip() {
        $trip = new \Geokrety\Domain\TripStep();
        $trip->id = $this->id;
        $trip->geokretId = $this->geokretId;
        $trip->userId = $this->userId;

        return $trip;
    }

    public function editUrl() {
        return '/imgup.php?trip&typ='.$this->type.'&id='.$this->tripId.'&rename='.$this->id;
    }
}
