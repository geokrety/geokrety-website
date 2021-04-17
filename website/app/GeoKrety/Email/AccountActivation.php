<?php

namespace GeoKrety\Email;

use Carbon\Carbon;
use Flash;
use GeoKrety\Model\AccountActivationToken as AccountActivationModel;
use GeoKrety\Service\Smarty;

// TODO rework messages and alert level
class AccountActivation extends BasePHPMailer {
    private array $message = [];

    public function sendActivation(AccountActivationModel $token) {
        $this->message['status'] = 'warning';
        $this->message['msg'][] = _('A confirmation email has been sent to your address.');
        $this->message['msg'][] = _('<strong>You must click on the link provided in the email to activate your account before your can use it.</strong>');
        $this->message['msg'][] = _('The confirmation link expires in %s.');
        $this->_sendActivation($token);
    }

    private function _sendActivation(AccountActivationModel $token) {
        Smarty::assign('token', $token);
        $this->setSubject(_('Welcome to GeoKrety.org'), '🎉');
        $this->setTo($token->user, true);
        if ($this->sendEmail('emails/account-activation.tpl')) {
            Flash::instance()->addMessage(sprintf(
                join(' ', $this->message['msg']),
                Carbon::instance($token->expire_on_datetime)->longAbsoluteDiffForHumans(['parts' => 3, 'join' => true])
            ), $this->message['status']);
        }
    }

    public function sendActivationAgain(AccountActivationModel $token) {
        $this->message['status'] = 'danger';
        $this->message['msg'][] = _('Your account seems to already exists.');
        $this->message['msg'][] = _('The confirmation email was sent again to your mail address.');
        $this->message['msg'][] = _('<strong>You must click on the link provided in the email to activate your account before your can use it.</strong>');
        $this->message['msg'][] = _('The confirmation link expires in %s.');
        $this->_sendActivation($token);
    }

    public function sendActivationAgainOnLogin(AccountActivationModel $token) {
        $this->message['status'] = 'danger';
        $this->message['msg'][] = _('<strong>Your account is not yet active.</strong>');
        $this->message['msg'][] = _('The confirmation email was sent again to your mail address.');
        $this->message['msg'][] = _('<strong>You must click on the link provided in the email to activate your account before your can use it.</strong>');
        $this->message['msg'][] = _('The confirmation link expires in %s.');
        $this->_sendActivation($token);
    }

    public function sendActivationConfirm(AccountActivationModel $token) {
        Smarty::assign('token', $token);
        $this->setSubject(_('Account activated'), '🎉');
        $this->setTo($token->user);
        $this->sendEmail('emails/account-activated.tpl');
    }

    protected function setFromDefault() {
        $this->setFrom(GK_SITE_EMAIL_REGISTRATION, 'GeoKrety');
    }
}
