<?php

namespace Geokrety\Domain;

class TripComment {
    public $id;
    public $tripId;
    public $geokretId;
    public $userId;
    public $date;
    public $comment;
    public $type;

    public function isAvatar() {
        // TODO
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
}
