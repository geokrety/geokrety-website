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
        $this->geokret = $geokret;
        $geokret->load(['gkid = ?', $gkid]);
        $this->geokret->filter('owner_codes', ['user = ?', null]);
        $this->filterHook();
        if ($geokret->dry()) {
            http_response_code(404);
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        Smarty::assign('geokret', $geokret);
    }

    protected function filterHook() {
        // empty
    }
}
