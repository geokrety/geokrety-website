<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class StaticPages extends Base {
    public function head($f3) {
    }

    public function press_corner($f3) {
        Smarty::render('pages/press_corner.tpl');
    }

    public function geokrety_toolbox($f3) {
        Smarty::render('pages/geokrety_toolbox.tpl');
    }

    public function downloads($f3) {
        Smarty::render('pages/downloads.tpl');
    }

    public function contact_us($f3) {
        Smarty::render('pages/contact_us.tpl');
    }

    public function work_in_progress($f3) {
        Smarty::render('pages/work_in_progress.tpl');
    }

    public function app_version($f3) {
        echo json_encode(['version' => GK_APP_VERSION], JSON_UNESCAPED_UNICODE);
    }

    public static function _404($f3) {
        Smarty::render('pages/404.tpl');
    }
}
