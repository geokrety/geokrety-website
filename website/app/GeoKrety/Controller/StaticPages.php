<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class StaticPages extends Base {
    public function terms_of_use($f3) {
        Smarty::render('pages/terms_of_use.tpl');
    }

    public function press_corner($f3) {
        Smarty::render('pages/press_corner.tpl');
    }

    public function work_in_progress($f3) {
        Smarty::render('pages/work_in_progress.tpl');
    }
}
