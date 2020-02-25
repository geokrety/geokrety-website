<?php

namespace GeoKrety\Traits;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

trait GeokretLoader {
    /**
     * @var Geokret
     */
    protected $geokret;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        $gkid = $f3->get('PARAMS.gkid');
        $gkid = Geokret::gkid2id($gkid);

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
