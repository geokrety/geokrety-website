<?php

namespace GeoKrety\Controller;

use GeoKrety\Traits\GeokretLoader;

class UserContactByGeokret extends UserContact {
    use GeokretLoader;

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
        $this->mail->to_user = $this->geokret->owner;
    }
}
