<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\Geokret;

class GeokretEdit extends Base {
    protected $geokrety = null;

    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $this->geokret = new Geokret();
        $this->geokret->load(array('id = ?', $f3->get('PARAMS.gkid')));
        Smarty::assign('geokret', $this->geokret);
    }

    public function get($f3) {
        Smarty::assign('isEdit', true);
        Smarty::render('pages/geokret_create.tpl');
    }

    public function post($f3) {
        $geokret = $this->geokret;
        $geokret->copyFrom('POST');

        if ($geokret->validate()) {
            $geokret->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to create the GeoKret.'), 'danger');
            } else {
                \Flash::instance()->addMessage(_('Your GeoKret has been updated.'), 'success');
                \Event::instance()->emit('geokret.updated', $geokret);
                $f3->reroute('@geokret_details(@gkid='.$geokret->id.')');
            }
        }

        $this->get($f3);
    }
}
