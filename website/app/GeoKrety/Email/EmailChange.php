<?php

namespace GeoKrety\Email;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\EmailActivation;
use GeoKrety\Model\User;

class EmailChange extends Base {
    protected function setFromDefault() {
        $this->setFromSupport();
    }

    public function sendEmailChangeNotification(EmailActivation $token) {
        Smarty::assign('token', $token);

        $this->sendEmailChangeNotificationToOldEmail($token);
        $this->sendEmailChangeNotificationToNewEmail($token);
    }

    protected function sendEmailChangeNotificationToOldEmail(EmailActivation $token) {
        $this->setSubject('📯 '._('Changing your email address'));
        $this->setTo($token->user);

        if (!$this->send(Smarty::fetch('email-change-to-old-address.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');
        }
    }

    protected function sendEmailChangeNotificationToNewEmail(EmailActivation $token) {
        $this->setSubject('✉️ '._('Changing your email address'));
        $this->setTo($token->email);

        if (!$this->send(Smarty::fetch('email-change-to-new-address.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');
        }
    }

    public function sendEmailChangedNotification(EmailActivation $token) {
        Smarty::assign('token', $token);

        $this->sendEmailChangedNotificationToOldEmail($token);
        $this->sendEmailChangedNotificationToNewEmail($token);
    }

    protected function sendEmailChangedNotificationToOldEmail(EmailActivation $token) {
        $this->setSubject('📯 '._('Email address changed'));
        $this->setTo($token->previous_email);

        if (!$this->send(Smarty::fetch('email-address-changed-to-old-address.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');
        }
    }

    protected function sendEmailChangedNotificationToNewEmail(EmailActivation $token) {
        $this->setSubject('✉️ '._('Email address changed'));
        $this->setTo($token->email);

        if (!$this->send(Smarty::fetch('email-address-changed-to-new-address.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');
        }
    }

    public function sendEmailRevertedNotification(User $user) {
        $this->setSubject('📯 '._('Email address reverted'));
        $this->setTo($user);

        if (!$this->send(Smarty::fetch('email-address-reverted.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');
        }
    }
}
