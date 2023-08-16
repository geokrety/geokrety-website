<?php

namespace GeoKrety\Service\Dialog;

use GeoKrety\Service\Smarty;

class Warning extends Base {
    public static function message_full_screen(string $message, ?string $title = null) {
        self::message($message, $title, self::DISPLAY_FULL_SCREEN);
    }

    public static function message(string $message, ?string $title = null, string $display = '') {
        Smarty::assign('title', $title ?? _('Warning'));
        Smarty::assign('message', $message);
        Smarty::assign('variant', 'warning');
        self::render($display.'dialog/info.tpl');
    }
}
