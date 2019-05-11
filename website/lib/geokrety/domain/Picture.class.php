<?php

namespace Geokrety\Domain;

class Picture extends AbstractObject {
    public $id;
    public $type;
    public $geokretId;
    public $userId;
    public $filename;
    public $authorName; // TODO update ldjson
    public $legend;

    public function isAvatar() {
        // TODO
    }
}

class PictureUser extends Picture {
    public $username;

    public function author() {
        $user = new \Geokrety\Domain\User();
        $user->id = $this->userId;
        $user->username = $this->authorName;

        return $user;
    }
}

class PictureGeoKret extends Picture {
    public $name;
}

class PictureTrip extends PictureGeoKret {
    public $country;
    public $date;
}
