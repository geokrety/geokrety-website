<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\OwnerCode;

class GeokretClaim extends Base {
    public function get(\Base $f3) {
        Smarty::render('pages/geokret_claim.tpl');
    }

    public function post(\Base $f3) {

        $ownerCode = new OwnerCode();
        $ownerCode->has('geokret', array('tracking_code = ?', $f3->get('POST.tc')));
        $ownerCode->load(array('token = ?', $f3->get('POST.oc')));

        if ($ownerCode->dry()) {
            \Flash::instance()->addMessage(_('Sorry, the provided owner code and tracking code doesn\'t match.'), 'danger');
            $this->get($f3);
            die();
        }

        if ($ownerCode->user) {
            \Flash::instance()->addMessage(_('Sorry, this owner code has already been used.'), 'danger');
            $this->get($f3);
            die();
        }

        $f3->get('DB')->begin();
        $ownerCode->user = $f3->get('SESSION.CURRENT_USER');
        $ownerCode->touch('claimed_on_datetime');
        $ownerCode->geokret->owner = $f3->get('SESSION.CURRENT_USER');

        if ($ownerCode->validate() && $ownerCode->geokret->validate()) {
            $ownerCode->save();
            $ownerCode->geokret->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Something went wrong while registering the adoption.'), 'danger');
            } else {
                \Flash::instance()->addMessage(sprintf(_('🎉 Congratulation! You are now the owner of %s.'), $ownerCode->geokret->name), 'success');
                $f3->get('DB')->commit();
                $f3->reroute('@geokret_details(@gkid='.$ownerCode->geokret->id.')');
            }
        }

        $this->get($f3);
    }
}
