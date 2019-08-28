<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\Geokret;

class BaseGeokret extends Base {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);
        $gk = $f3->get('PARAMS.gkid');
        if (strtoupper(substr($gk, 0, 2)) !== 'GK') {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }

        $geokret = new Geokret();
        $geokret->filter('owner_codes', array('user = ?', null));
        $geokret->load(array('gkid = ?', hexdec(substr($gk, 2))));
        if ($geokret->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        $this->geokret = $geokret;
        Smarty::assign('geokret', $geokret);
    }
}
