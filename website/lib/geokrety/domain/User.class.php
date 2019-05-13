<?php

namespace Geokrety\Domain;

class User extends AbstractObject {
    public $id;
    public $username;
    public $email;
    public $isEmailActive;
    public $joinDate;
    public $acceptEmail;
    public $language;
    public $latitude;
    public $longitude;
    public $observationRadius;
    public $country;
    public $emailHour; // hour a which user receive his daily mail
    public $statpic;
    public $lastMail;
    public $lastlogin;
    public $secid;

    public function getUrl() {
        return '/mypage.php?userid='.$this->id;
    }

    public function isCurrentUser() {
        return $_SESSION['isLoggedIn'] && $_SESSION['currentUser'] === $this->id;
    }

    public function hasCoordinates() {
        return $this->latitude && $this->longitude;
    }

    public function avatar() {
        $pictureR = new \Geokrety\Repository\PictureRepository(\GKDB::getLink());

        return $pictureR->getAvatarByUserId($this->id);
    }

    public function save() {
        $userR = new \Geokrety\Repository\UserRepository(\GKDB::getLink());

        return $userR->updateUser($this);
    }

    public function getStatsGeokretyCreated() {
        $gkR = new \Geokrety\Repository\KonkretRepository(\GKDB::getLink());

        return $gkR->getStatsByUserId($this->id);
    }

    public function getStatsGeokretyMoved() {
        $tripeR = new \Geokrety\Repository\TripRepository(\GKDB::getLink());

        return $tripeR->getStatsByOwnerId($this->id);
    }

    public function getBadges() {
        $badgeR = new \Geokrety\Repository\BadgeRepository(\GKDB::getLink());

        return $badgeR->getByUserId($this->id);
    }
}
