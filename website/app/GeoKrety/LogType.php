<?php

namespace GeoKrety;

class LogType {
    public const LOG_TYPE_DROPPED = 0;
    public const LOG_TYPE_GRABBED = 1;
    public const LOG_TYPE_COMMENT = 2;
    public const LOG_TYPE_SEEN = 3;
    public const LOG_TYPE_ARCHIVED = 4;
    public const LOG_TYPE_DIPPED = 5;

    public const LOG_TYPES = [
        self::LOG_TYPE_DROPPED,
        self::LOG_TYPE_GRABBED,
        self::LOG_TYPE_COMMENT,
        self::LOG_TYPE_SEEN,
        self::LOG_TYPE_ARCHIVED,
        self::LOG_TYPE_DIPPED,
    ];

    public const LOG_TYPES_ALIVE = [
        self::LOG_TYPE_DROPPED,
        self::LOG_TYPE_GRABBED,
        self::LOG_TYPE_SEEN,
        self::LOG_TYPE_DIPPED,
    ];

    public const LOG_TYPES_REQUIRING_COORDINATES = [
        self::LOG_TYPE_DROPPED,
        self::LOG_TYPE_SEEN,
        self::LOG_TYPE_DIPPED,
    ];

    public const LOG_TYPES_COUNT_KILOMETERS = [
        self::LOG_TYPE_DROPPED,
        self::LOG_TYPE_SEEN,
        self::LOG_TYPE_DIPPED,
    ];

    public const LOG_TYPES_THEORETICALLY_IN_CACHE = [
        self::LOG_TYPE_DROPPED,
        self::LOG_TYPE_SEEN,
    ];

    public const LOG_TYPES_LAST_POSITION = [
        self::LOG_TYPE_DROPPED,
        self::LOG_TYPE_GRABBED,
        self::LOG_TYPE_SEEN,
        self::LOG_TYPE_ARCHIVED,
        self::LOG_TYPE_DIPPED,
    ];

    public const LOG_TYPES_USER_TOUCHED = [
        self::LOG_TYPE_DROPPED,
        self::LOG_TYPE_GRABBED,
        self::LOG_TYPE_SEEN,
        self::LOG_TYPE_DIPPED,
    ];

    public const LOG_TYPES_EDITABLE = [
        self::LOG_TYPE_DROPPED,
        self::LOG_TYPE_GRABBED,
        self::LOG_TYPE_COMMENT,
        self::LOG_TYPE_SEEN,
        self::LOG_TYPE_DIPPED,
    ];

    private $logtype;

    public function __construct($logtype = null) {
        if (!is_null($logtype)) {
            $this->logtype = (int) $logtype;
        }
    }

    public function getLogTypeId() {
        return $this->logtype;
    }

    public function isType($logtype) {
        if (is_null($this->logtype)) {
            return false;
        }

        return $logtype == $this->logtype;
    }

    public static function isValid($logtype) {
        return in_array($logtype, self::LOG_TYPES, true);
    }

    public function isAlive() {
        return in_array($this->logtype, self::LOG_TYPES_ALIVE, true);
    }

    public function isCoordinatesRequired() {
        return in_array($this->logtype, self::LOG_TYPES_REQUIRING_COORDINATES, true);
    }

    public function isCountingKilometers() {
        return in_array($this->logtype, self::LOG_TYPES_COUNT_KILOMETERS, true);
    }

    public function isTheoricallyInCache() {
        return !is_null($this->logtype) && in_array($this->logtype, self::LOG_TYPES_THEORETICALLY_IN_CACHE);
    }

    public function isEditable() {
        return !is_null($this->logtype) && in_array($this->logtype, self::LOG_TYPES_EDITABLE);
    }

    public function getLogTypeString() {
        switch ($this->logtype) {
            case 0: return _('drop');
            case 1: return _('grab');
            case 2: return _('comment');
            case 3: return _('met');
            case 4: return _('archive');
            case 5: return _('dip');
            case 9: return _('Born');
        }

        return null;
    }
}
