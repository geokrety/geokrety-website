<?php

namespace GeoKrety\Email;

use GeoKrety\Model\Geokret;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class GeokretClaim extends BasePHPMailer {
    /**
     * Override mail From.
     *
     * @return void
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function setFromDefault() {
        $this->setFrom(GK_SITE_EMAIL_NOTIF, 'GeoKrety');
    }

    public function sendClaimedNotification(Geokret $geokret, ?User $user) {
        $this->setTo($user);
        $this->setSubject(sprintf(_('Your GeoKret \'%s\' has been adopted'), $geokret->name), '🎉');
        $this->setFromNotif();
        Smarty::assign('geokret', $geokret);
        Smarty::assign('user', $user);
        $this->sendEmail('emails/geokret-adopted.tpl');
    }
}
