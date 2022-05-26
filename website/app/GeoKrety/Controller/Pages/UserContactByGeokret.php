<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;

class UserContactByGeokret extends UserContact {
    private Geokret $geokret;

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
            $f3->error(404, _('This user does not exist.'));
        }
        $this->geokret = $geokret;
        $this->mail->to_user = $geokret->owner;
    }
}
