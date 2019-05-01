<?php

namespace Geokrety\Domain;

const LOG_TYPE_DROPPED = 0;
const LOG_TYPE_GRABBED = 1;
const LOG_TYPE_COMMENT = 2;
const LOG_TYPE_SEEN = 3;
const LOG_TYPE_ARCHIVED = 4;
const LOG_TYPE_DIPPED = 5;

const LOG_TYPES = array(
    LOG_TYPE_DROPPED,
    LOG_TYPE_GRABBED,
    LOG_TYPE_COMMENT,
    LOG_TYPE_SEEN,
    LOG_TYPE_ARCHIVED,
    LOG_TYPE_DIPPED,
);

// It would have been nice if it  could be a constant
$LOG_TYPES_TEXT = array(
    LOG_TYPE_DROPPED => _('Dropped to'),
    LOG_TYPE_GRABBED => _('Grabbed from'),
    LOG_TYPE_COMMENT => _('A comment'),
    LOG_TYPE_SEEN => _('Seen in'),
    LOG_TYPE_ARCHIVED => _('Archived'),
    LOG_TYPE_DIPPED => _('Dipped in'),
);

const LOG_TYPES_REQUIRING_COORDINATES = array(
    LOG_TYPE_DROPPED,
    LOG_TYPE_SEEN,
    LOG_TYPE_DIPPED,
);

const LOG_TYPES_THEORICALLY_IN_CACHE = array(
    LOG_TYPE_DROPPED,
    LOG_TYPE_SEEN,
);

class LogType extends AbstractObject {
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
        return in_array($this->logtype, LOG_TYPES);
    }

    public function isCoordinatesRequired() {
        return in_array($this->logtype, LOG_TYPES_REQUIRING_COORDINATES);
    }

    public function isTheoricallyInCache() {
        return in_array($this->logtype, LOG_TYPES_THEORICALLY_IN_CACHE);
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
