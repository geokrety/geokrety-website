<?php

namespace Geokrety\Domain;

class TripComment extends AbstractObject {
    public $id;
    public $tripId;
    public $geokretId;
    public $userId;
    public $username;
    public $date;
    public $comment;
    public $type;

    public function author() {
        $user = new \Geokrety\Domain\User();
        $user->id = $this->userId;
        // Workaround: Fix database encoding
        $user->username = html_entity_decode($this->username);

        return $user;
    }

    public function enrichFields() {
        $this->typeString = $this->getTypeString();
    }

    public function getTypeString() {
        switch ($this->type) {
            case 0: return _('comment');
            case 1: return _('missing');
            default: return null;
        }
    }

    public function getDate() {
        if (is_a($this->date, '\Datetime')) {
            return $this->date->format('Y-m-d H:i:s');
        }

        return $this->date;
    }

    public function setDate($date, $format = 'Y-m-d H:i:s') {
        if (is_a($date, '\Datetime')) {
            $this->date = $date;
        }
        $this->date = \DateTime::createFromFormat($format, $date, new \DateTimeZone('UTC'));
    }
}
