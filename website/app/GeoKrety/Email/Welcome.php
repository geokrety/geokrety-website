<?php

namespace GeoKrety\Email;

use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class Welcome extends Base {
    protected function setFromDefault() {
        $this->set('From', GK_SITE_EMAIL_REGISTRATION);
    }

    public function sendWelcome(User $user) {
        $this->sendEmail($user);
    }

    protected function sendEmail(User $user) {
        $this->setTo($user);
        $this->setSubject('🎉 '._('Welcome on GeoKrety.org'));
        Smarty::assign('user', $user);
        if (!$this->send(Smarty::fetch('email-welcome.html'))) {
            \Flash::instance()->addMessage(_('An error occurred while sending the welcome mail.'), 'danger');
        }
    }
}
