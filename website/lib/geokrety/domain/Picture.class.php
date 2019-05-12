<?php

namespace Geokrety\Domain;

define(__NAMESPACE__.'\AVATAR', 0);
define(__NAMESPACE__.'\TRIP', 1);
define(__NAMESPACE__.'\USER', 2);

class Picture extends AbstractObject {
    public $id;
    public $type;
    public $geokretId;
    public $userId;
    public $filename;
    public $legend;
}

class PictureUser extends Picture {
    public $username;

    public function author() {
        $user = new \Geokrety\Domain\User();
        $user->id = $this->userId;
        $user->username = $this->username;

        return $user;
    }
}

class PictureGeoKret extends Picture {
    public $name;

    public function geokret() {
        $geokret = new \Geokrety\Domain\User();
        $geokret->id = $this->geokretId;
        $geokret->name = $this->name;

        return $geokret;
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
}
