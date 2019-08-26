<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\OwnerCode;

class GeokretOfferForAdoption extends Base {
    public function loadGeokret(\Base $f3) {
        parent::beforeRoute($f3);

        $geokret = new Geokret();
        $geokret->load(array('id = ?', $f3->get('PARAMS.gkid')));
        if ($geokret->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        $this->geokret = $geokret;
        Smarty::assign('geokret', $this->geokret);
    }

    public function get(\Base $f3) {
        Smarty::render('extends:full_screen_modal.tpl|dialog/geokret_offer_for_adoption.tpl');
    }

    public function get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/geokret_offer_for_adoption.tpl');
    }

    public function post(\Base $f3) {
        $this->loadGeokret($f3);
        if (!$this->geokret->isOwner()) {
            \Flash::instance()->addMessage(_('You must be the GeoKret owner to generate new Owner Code.'), 'danger');
            $f3->reroute(sprintf('@geokret_details(@gkid=%d)', $this->geokret->id));
        }

        $ownerCode = new OwnerCode();
        if ($ownerCode->count(array('geokret = ? AND user = ?', $this->geokret->id, null), null, 0)) {
            \Flash::instance()->addMessage(_('An Owner Code is already available for this GeoKret.'), 'warning');
            $f3->reroute(sprintf('@geokret_details(@gkid=%d)', $this->geokret->id));
        }

        $ownerCode->geokret = $this->geokret;

        if ($ownerCode->validate()) {
            $ownerCode->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to create a new Owner Code.'), 'danger');
            } else {
                \Event::instance()->emit('geokret.owner_code.created', $ownerCode);
                \Flash::instance()->addMessage(sprintf(_('The Owner Code (%s) has been generated. Give it along with the Tracking Code (%s) to someone so he can adopt your GeoKret.'), $ownerCode->token, $ownerCode->geokret->tracking_code), 'success');
            }
        }

        $f3->reroute(sprintf('@geokret_details(@gkid=%d)', $ownerCode->geokret->id));
    }
}
