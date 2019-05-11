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

    protected $dblink = null;

    public function hasCoordinates() {
        return $this->latitude && $this->longitude;
    }

    protected function getLink() {
        if (is_null($this->dblink)) {
            $this->dblink = \GKDB::getLink();
        }

        return $this->dblink;
    }

    public function getStatsGeokretyCreated() {
        $gkR = new \Geokrety\Repository\KonkretRepository($this->getLink());

        return $gkR->getStatsByUserId($this->id);
    }

    public function getStatsGeokretyMoved() {
        $tripeR = new \Geokrety\Repository\TripRepository($this->getLink());

        return $tripeR->getStatsByOwnerId($this->id);
    }

    public function getBadges() {
        $badgeR = new \Geokrety\Repository\BadgeRepository($this->getLink());

        return $badgeR->getByUserId($this->id);
    }
}
