<?php

namespace GeoKrety\Email;

use GeoKrety\Model\EmailActivationToken;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class EmailChange extends BasePHPMailer {
    protected function setFromDefault() {
        $this->setFromSupport();
    }

    public function sendEmailChangeNotification(EmailActivationToken $token) {
        Smarty::assign('token', $token);

        $this->sendEmailChangeNotificationToOldEmail($token);
        $this->sendEmailChangeNotificationToNewEmail($token);
    }

    protected function sendEmailChangeNotificationToOldEmail(EmailActivationToken $token) {
        if (is_null($token->user->email)) {
            return;
        }
        $this->setTo($token->user);
        $this->setSubject(_('Changing your email address'), '📯');
        $this->sendEmail('emails/email-change-to-old-address.tpl');
    }

    protected function sendEmailChangeNotificationToNewEmail(EmailActivationToken $token) {
        $user = clone $token->user;
        $user->_email = $token->email;
        $this->setTo($user, true);
        $this->setSubject(_('Changing your email address'), '✉️');
        $this->sendEmail('emails/email-change-to-new-address.tpl');
    }

    public function sendEmailChangedNotification(EmailActivationToken $token) {
        Smarty::assign('token', $token);

        $this->sendEmailChangedNotificationToOldEmail($token);
        $this->sendEmailChangedNotificationToNewEmail($token);
    }

    protected function sendEmailChangedNotificationToOldEmail(EmailActivationToken $token) {
        if (is_null($token->previous_email)) {
            return;
        }
        $user = clone $token->user;
        $user->_email = $token->_previous_email;
        $this->setTo($user);
        $this->setSubject(_('Email address changed'), '📯');
        $this->sendEmail('emails/email-address-changed-to-old-address.tpl');
    }

    protected function sendEmailChangedNotificationToNewEmail(EmailActivationToken $token) {
        $user = clone $token->user;
        $user->_email = $token->email;
        $this->setTo($user);
        $this->setSubject(_('Email address changed'), '✉️');
        $this->sendEmail('emails/email-address-changed-to-new-address.tpl');
    }

    public function sendEmailRevertedNotification(User $user) {
        $this->setTo($user);
        $this->setSubject(_('Email address reverted'), '📯');
        $this->sendEmail('emails/email-address-reverted.tpl');
    }
}
