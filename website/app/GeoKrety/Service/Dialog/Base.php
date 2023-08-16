<?php

namespace GeoKrety\Service\Dialog;

use GeoKrety\Service\Smarty;

abstract class Base {
    public const DISPLAY_FULL_SCREEN = 'extends:full_screen_modal.tpl|';

    abstract public static function message(string $template, ?string $title = null, string $display = '');

    protected static function render($template) {
        Smarty::render($template);
    }
}
