<?php

namespace GeoKrety;

class GeokretyType {
    public const GEOKRETY_TYPE_TRADITIONAL = 0;
    public const GEOKRETY_TYPE_BOOK_CD_DVD = 1;
    public const GEOKRETY_TYPE_HUMAN = 2;
    public const GEOKRETY_TYPE_COIN = 3;
    public const GEOKRETY_TYPE_KRETYPOST = 4;
    public const GEOKRETY_TYPE_PEBBLE = 5;
    public const GEOKRETY_TYPE_CAR = 6;
    public const GEOKRETY_TYPE_PLAYING_CARD = 7;
    public const GEOKRETY_TYPE_DOG_TAG = 8;
    public const GEOKRETY_TYPE_JIGSAW = 9;

    public const GEOKRETY_TYPES = [
        self::GEOKRETY_TYPE_TRADITIONAL,
        self::GEOKRETY_TYPE_BOOK_CD_DVD,
        self::GEOKRETY_TYPE_HUMAN,
        self::GEOKRETY_TYPE_COIN,
        self::GEOKRETY_TYPE_KRETYPOST,
        self::GEOKRETY_TYPE_PEBBLE,
        self::GEOKRETY_TYPE_CAR,
        self::GEOKRETY_TYPE_PLAYING_CARD,
        self::GEOKRETY_TYPE_DOG_TAG,
        self::GEOKRETY_TYPE_JIGSAW,
    ];

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
        return in_array((int) $type, self::GEOKRETY_TYPES, true);
    }

    public function getTypeString() {
        $types = self::getTypes();

        return $types[$this->type];
    }

    public static function getTypes() {
        return [
            self::GEOKRETY_TYPE_TRADITIONAL => _('Traditional'),
            self::GEOKRETY_TYPE_BOOK_CD_DVD => _('A book'),
            self::GEOKRETY_TYPE_HUMAN => _('A human'),
            self::GEOKRETY_TYPE_COIN => _('A coin'),
            self::GEOKRETY_TYPE_KRETYPOST => _('KretyPost'),
            self::GEOKRETY_TYPE_PEBBLE => _('A Pebble'),
            self::GEOKRETY_TYPE_CAR => _('A car'),
            self::GEOKRETY_TYPE_PLAYING_CARD => _('A Playing Card'),
            self::GEOKRETY_TYPE_DOG_TAG => _('A dog'),
            self::GEOKRETY_TYPE_JIGSAW => _('Jigsaw part'),
        ];
    }
}
