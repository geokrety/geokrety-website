<?php

namespace GeoKrety;

class GeokretyType {
    const GEOKRETY_TYPE_TRADITIONAL = 0;
    const GEOKRETY_TYPE_BOOK_CD_DVD = 1;
    const GEOKRETY_TYPE_HUMAN = 2;
    const GEOKRETY_TYPE_COIN = 3;
    const GEOKRETY_TYPE_KRETYPOST = 4;

    const GEOKRETY_TYPES = array(
        self::GEOKRETY_TYPE_TRADITIONAL,
        self::GEOKRETY_TYPE_BOOK_CD_DVD,
        self::GEOKRETY_TYPE_HUMAN,
        self::GEOKRETY_TYPE_COIN,
        self::GEOKRETY_TYPE_KRETYPOST,
    );

    private $type;

    public function __construct($type = null) {
        $this->type = $type;
    }

    public function getTypeId() {
        return $this->type;
    }

    public function isType($type) {
        if (is_null($this->type)) {
            return false;
        }

        return $type == $this->type;
    }

    public function isValid() {
        return in_array($this->type, self::GEOKRETY_TYPES);
    }

    public function getTypeString() {
        switch ($this->type) {
            case 0: return _('Traditional');
            case 1: return _('A book/CD/DVD...');
            case 2: return _('A human');
            case 3: return _('A coin');
            case 4: return _('KretyPost');
        }

        return null;
    }
}
