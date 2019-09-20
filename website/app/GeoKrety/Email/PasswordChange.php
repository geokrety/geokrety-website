<?php

namespace GeoKrety\Email;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\User;
use GeoKrety\Model\PasswordToken;

class PasswordChange extends Base {
    protected function setFromDefault() {
        $this->setFromSupport();
    }

    public function sendPasswordChangeToken(PasswordToken $token) {
        $this->setSubject('🔑 '._('Password reset request'));
        $this->setTo($token->user);
        Smarty::assign('token', $token);
        if (!$this->send(Smarty::fetch('email-password-recovery.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the password reset mail.'), 'danger');
        } else {
            \Flash::instance()->addMessage(_('An email containing a validation link has been sent to the provided email address.'), 'success');
        }
    }

    public function sendPasswordChangedNotification(User $user) {
        $this->setSubject('🔑 '._('Your password has been changed'));
        $this->setTo($user);
        Smarty::assign('user', $user);
        if (!$this->send(Smarty::fetch('email-password-changed.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the password changed mail notification.'), 'danger');
        }
    }
}
