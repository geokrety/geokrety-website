<?php

namespace Geokrety\Domain;

class LogType extends AbstractObject {
    const LOG_TYPE_DROPPED = 0;
    const LOG_TYPE_GRABBED = 1;
    const LOG_TYPE_COMMENT = 2;
    const LOG_TYPE_SEEN = 3;
    const LOG_TYPE_ARCHIVED = 4;
    const LOG_TYPE_DIPPED = 5;

    const LOG_TYPES = array(
        self::LOG_TYPE_DROPPED,
        self::LOG_TYPE_GRABBED,
        self::LOG_TYPE_COMMENT,
        self::LOG_TYPE_SEEN,
        self::LOG_TYPE_ARCHIVED,
        self::LOG_TYPE_DIPPED,
    );

    const LOG_TYPES_REQUIRING_COORDINATES = array(
        self::LOG_TYPE_DROPPED,
        self::LOG_TYPE_SEEN,
        self::LOG_TYPE_DIPPED,
    );

    const LOG_TYPES_THEORICALLY_IN_CACHE = array(
        self::LOG_TYPE_DROPPED,
        self::LOG_TYPE_SEEN,
    );

    private $logtype;

    public function __construct($logtype = null) {
        $this->logtype = $logtype;
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

    public function isValid() {
        return in_array($this->logtype, self::LOG_TYPES);
    }

    public function isCoordinatesRequired() {
        return in_array($this->logtype, self::LOG_TYPES_REQUIRING_COORDINATES);
    }

    public function isTheoricallyInCache() {
        return !is_null($this->logtype) && in_array($this->logtype, self::LOG_TYPES_THEORICALLY_IN_CACHE);
    }

    public function getLogTypeString() {
        switch ($this->logtype) {
            case 0: return _('drop');
            case 1: return _('grab');
            case 2: return _('comment');
            case 3: return _('met');
            case 4: return _('archive');
            case 5: return _('dip');
        }

        return null;
    }
}
