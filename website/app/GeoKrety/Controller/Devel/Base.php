<?php

namespace GeoKrety\Controller\Devel;

abstract class Base extends \GeoKrety\Controller\Base {
    public function beforeRoute(\Base $f3) {
        if (!GK_DEVEL) {
            throw new \Exception('Go away!');
        }
        parent::beforeRoute($f3);
    }
}
