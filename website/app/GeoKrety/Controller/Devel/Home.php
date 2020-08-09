<?php

namespace GeoKrety\Controller\Devel;

use GeoKrety\Service\Smarty;

class Home extends Base {
    public function get() {
        Smarty::render('devel/pages/home.tpl');
    }
}
