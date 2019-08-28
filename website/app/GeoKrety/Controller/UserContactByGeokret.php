<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Mail;

class UserContactByGeokret extends UserContact {
    public function getPostUrl(\Base $f3) {
        return $f3->alias('mail_by_geokret');
    }

    public function getPostRedirectUrl() {
        return sprintf('@geokret_details(@gkid=%d)', $this->geokret->gkid);
    }

    protected function _get(\Base $f3) {
        parent::_get($f3);
        if (!$f3->exists('mail')) {
            $mail = new Mail();
            $mail->subject = sprintf(_('GeoKret: %s (%s)'), $this->geokret->name, $this->geokret->gkid);
            $f3->set('mail', $mail);
            Smarty::assign('mail', $mail);
        }
    }

    public function loadToUser(\Base $f3) {
        $geokret = new Geokret();
        $geokret->load(array('id = ?', $f3->get('PARAMS.gkid')));
        if ($geokret->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        $this->geokret = $geokret;
        $this->userTo = $geokret->owner;
        Smarty::assign('userTo', $this->userTo);
    }
}
