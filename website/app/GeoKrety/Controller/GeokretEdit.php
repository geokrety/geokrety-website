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
            \Flash::instance()->addMessage("name:$geokret->name <br />tracking_code:$geokret->tracking_code <br />type:$geokret->type", 'danger');

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to create the GeoKret.'), 'danger');
            } else {
                \Flash::instance()->addMessage(sprintf(_('Your GeoKret has been created. You may now wish to <a href="%s">print</a> it a great label…'), $f3->alias('geokret_label_generator', '@gkid='.$geokret->id)), 'success');
                $f3->reroute('@geokret_details(@gkid='.$geokret->id.')');
            }
        }

        $this->get($f3);
    }
}
