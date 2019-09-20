<?php

namespace GeoKrety\Email;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\User;

class GeokretClaim extends Base {
    public function sendClaimedNotification(Geokret $geokret, User $user) {
        $this->setSubject('🎉 '.sprintf(_('Your GeoKret \'%s\' has been adopted'), $geokret->name));
        $this->setTo($user);
        $this->setFromSupport();
        Smarty::assign('geokret', $geokret);
        Smarty::assign('user', $user);
        if (!$this->send(Smarty::fetch('email-geokret-adopted.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the adoption mail notification to old owner.'), 'danger');
        }
    }
}
