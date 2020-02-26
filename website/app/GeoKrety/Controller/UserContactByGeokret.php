<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Model\Mail;
use GeoKrety\Service\Smarty;

class UserContactByGeokret extends UserContact {
    public function getPostUrl(\Base $f3) {
        return $f3->alias('mail_by_geokret');
    }

    public function getPostRedirectUrl() {
        return sprintf('@geokret_details(@gkid=%s)', $this->geokret->gkid);
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
        $geokret->load(['gkid = ?', hexdec(substr($f3->get('PARAMS.gkid'), 2))]);
        if ($geokret->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        $this->geokret = $geokret;
        $this->userTo = $geokret->owner;
        Smarty::assign('userTo', $this->userTo);
    }
}
