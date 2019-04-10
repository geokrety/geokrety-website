<?php

namespace Geokrety\Exception;

class NoDataException extends \Exception {
    public function __construct($message = 'no data found') {
        parent::__construct($message);
    }
}
