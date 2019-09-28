<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Base;
use GeoKrety\Service\Smarty;

class Home extends Base {
    public function get($f3) {
        Smarty::render('admin/pages/home.tpl');
    }
}
