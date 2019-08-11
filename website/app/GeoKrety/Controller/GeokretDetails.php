<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\Geokret;

class GeokretDetails extends Base {
    public function get($f3) {
        $geokret = new Geokret();
        $geokret->load(array('id = ?', $f3->get('PARAMS.gkid')));
        Smarty::assign('geokret', $geokret);
        Smarty::render('pages/geokret_details.tpl');
    }
}
