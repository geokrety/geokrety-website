<?php

namespace GeoKrety\Email;

use GeoKrety\Model\PasswordToken;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class PasswordChange extends BasePHPMailer {
    protected function setFromDefault() {
        $this->setFromSupport();
    }

    public function sendPasswordChangeToken(PasswordToken $token) {
        $this->setTo($token->user);
        Smarty::assign('token', $token);
        $this->setSubject(_('Password reset request'), '🔑');
        if ($this->sendEmail('emails/password-recovery.tpl')) {
            \Flash::instance()->addMessage(_('An email containing a validation link has been sent to the provided email address.'), 'success');
        }
    }

    public function sendPasswordChangedNotification(User $user) {
        $this->setTo($user);
        $this->setSubject(_('Your password has been changed'), '🔑');
        $this->sendEmail('emails/password-changed.tpl');
    }
}
