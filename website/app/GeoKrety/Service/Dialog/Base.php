<?php

namespace GeoKrety\Service\Dialog;

use GeoKrety\Service\Smarty;

abstract class Base {
    public static function render($template) {
        Smarty::render($template);
    }
}
