<?php

namespace GeoKrety\Service\Dialog;

use GeoKrety\Service\Smarty;

class Info extends Base {
    public static function message(string $message, ?string $title = null) {
        Smarty::assign('title', $title ?? _('Information'));
        Smarty::assign('message', $message);
        self::render('dialog/info.tpl');
    }
}
