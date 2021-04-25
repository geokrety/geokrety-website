<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Smarty;

class UserContactByGeokret extends UserContact {
    /**
     * @var Geokret
     */
    private $geokret;

    public function getPostUrl(\Base $f3) {
        return $f3->alias('mail_by_geokret');
    }

    public function getPostRedirectUrl() {
        return sprintf('@geokret_details(@gkid=%s)', $this->geokret->gkid);
    }

    protected function _get(\Base $f3) {
        parent::_get($f3);
        $this->mail->subject = sprintf(_('GeoKret: %s (%s)'), $this->geokret->name, $this->geokret->gkid);
    }

    public function loadToUser(\Base $f3) {
        $geokret = new Geokret();
        $geokret->load(['gkid = ?', hexdec(substr($f3->get('PARAMS.gkid'), 2))]);
        if ($geokret->dry()) {
            http_response_code(404);
            Smarty::render('dialog/alert_404.tpl');
            exit();
        }
        $this->geokret = $geokret;
        $this->mail->to_user = $geokret->owner;
    }
}
