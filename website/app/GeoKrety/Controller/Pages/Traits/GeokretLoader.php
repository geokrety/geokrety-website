<?php

namespace GeoKrety\Traits;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

trait GeokretLoader {
    protected Geokret $geokret;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        $gkid = $f3->get('PARAMS.gkid');
        $gkid = Geokret::gkid2id($gkid);

        $geokret = new Geokret();
        $this->geokret = $geokret;
        $this->geokret->filter('owner_codes', ['adopter = ?', null]);
        $this->geokret->filter('avatars', ['uploaded_on_datetime != ?', null]);
        $this->filterHook();
        $geokret->load(['gkid = ?', $gkid]);
        if ($geokret->dry()) {
            $f3->error(404, _('This GeoKret does not exist.'));
        }
        Smarty::assign('geokret', $geokret);
    }

    protected function filterHook() {
        // empty
    }
}
