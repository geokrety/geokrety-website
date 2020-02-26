<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

class BaseGeokret extends Base {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);
        $gkid = $f3->get('PARAMS.gkid');
        if (strtoupper(substr($gkid, 0, 2)) === 'GK') {
            $gkid = hexdec(substr($gkid, 2));
        }

        $geokret = new Geokret();
        $geokret->filter('owner_codes', ['user = ?', null]);
        $geokret->load(['gkid = ?', $gkid]);
        if ($geokret->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        $this->geokret = $geokret;
        Smarty::assign('geokret', $geokret);
    }
}
