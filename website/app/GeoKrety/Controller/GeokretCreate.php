<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\Geokret;

class GeokretCreate extends Base {
    public function get($f3) {
        Smarty::assign('isCreate', true);
        Smarty::render('pages/geokret_create.tpl');
    }
    public function post($f3) {
        $geokret = new Geokret();
        $geokret->copyFrom('POST');

        if ($geokret->validate()) {
            $geokret->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to create the GeoKret.'), 'danger');
            } else {
                \Flash::instance()->addMessage(sprintf(_('Your GeoKret has been created. You may now wish to <a href="%s">print</a> it a great label…'), $f3->alias('geokret_label_generator', '@gkid='.$geokret->id)), 'success');
                \Event::instance()->emit('geokret.new', $geokret);
                $f3->reroute('@geokret_details(@gkid='.$geokret->id.')');
            }
        }

        Smarty::assign('geokret', $geokret);
        $this->get($f3);
    }
}
