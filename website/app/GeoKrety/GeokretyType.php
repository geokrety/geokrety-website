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

    public function __toString() {
        $types = self::getTypes();

        return $types[$this->type];
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

    public static function isValid($type) {
        return in_array((int)$type, self::GEOKRETY_TYPES, true);
    }

    public function getTypeString() {
        $types = self::getTypes();

        return $types[$this->type];
    }

    public static function getTypes() {
        return array(
            self::GEOKRETY_TYPE_TRADITIONAL => _('Traditional'),
            self::GEOKRETY_TYPE_BOOK_CD_DVD => _('A book/CD/DVD...'),
            self::GEOKRETY_TYPE_HUMAN => _('A human'),
            self::GEOKRETY_TYPE_COIN => _('A coin'),
            self::GEOKRETY_TYPE_KRETYPOST => _('KretyPost'),
        );
    }
}
