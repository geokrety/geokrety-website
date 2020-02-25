<?php

namespace GeoKrety;

class PictureType {
    const PICTURE_GEOKRET_AVATAR = 0;
    const PICTURE_GEOKRET_MOVE = 1;
    const PICTURE_USER_AVATAR = 2;

    const PICTURE_TYPES = [
        self::PICTURE_GEOKRET_AVATAR,
        self::PICTURE_GEOKRET_MOVE,
        self::PICTURE_USER_AVATAR,
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
        return in_array((int) $type, self::PICTURE_TYPES, true);
    }

    public function getTypeString() {
        $types = self::getTypes();

        return $types[$this->type];
    }

    public static function getTypes() {
        return [
            self::PICTURE_GEOKRET_AVATAR => _('GeoKret avatar'),
            self::PICTURE_GEOKRET_MOVE => _('GeoKret move'),
            self::PICTURE_USER_AVATAR => _('User avatar'),
        ];
    }
}
