<?php

namespace Geokrety\Domain;

abstract class AbstractObject {
    public function redirect() {
        header('Location: '.$this->getUrl());
        die();
    }
}
