<?php

namespace GeoKrety\Controller\Devel;

use Exception;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Smarty;

abstract class Base extends \GeoKrety\Controller\Base {

    public function beforeRoute(\Base $f3) {
        if (!GK_DEVEL) {
            throw new Exception('Go away!');
        }
        parent::beforeRoute($f3);
    }
}
