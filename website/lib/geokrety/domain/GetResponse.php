<?php

namespace Geokrety\Domain;

class GetResponse extends AbstractResponse {
    /*
     * mixed value
     */
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }
}
