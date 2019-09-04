<?php

namespace GeoKrety\Email;

use Carbon\Carbon;
use GeoKrety\Service\Smarty;
use GeoKrety\Model\AccountActivation as AccountActivationModel;

class AccountActivation extends Base {
    private $message;

    protected function setFromDefault() {
        $this->setFromSupport();
    }

    public function sendActivation(AccountActivationModel $token) {
        $this->message = _('A confirmation email has been sent to your address. You must click on the link provided in the email to activate your account. The confirmation link expires in %s.');
        $this->sendEmail($token);
    }

    public function sendActivationAgain(AccountActivationModel $token) {
        $this->message = _('Your account seems to already exists. The confirmation email was sent again to your mail address. You must click on the link provided in the email to activate your account. The confirmation link expires in %s.');
        $this->sendEmail($token);
    }

    public function sendActivationAgainOnLogin(AccountActivationModel $token) {
        $this->message = _('<strong>Your account is not yet active.</strong> The confirmation email was sent again to your mail address. You must click on the link provided in the email to activate your account. The confirmation link expires in %s.');
        $this->sendEmail($token);
    }

    protected function sendEmail(AccountActivationModel $token) {
        $this->setSubject('🎉 '._('Welcome on GeoKrety.org'));
        $this->set('To', $token->user->email);
        Smarty::assign('token', $token);
        if (!$this->send(Smarty::fetch('email-account-activation.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the activation mail.'), 'danger');
        } else {
            if (!is_null($this->message)) {
                \Flash::instance()->addMessage(sprintf($this->message, Carbon::instance($token->expire_on_datetime)->longAbsoluteDiffForHumans(['parts' => 3, 'join' => true])), 'success');
            }
        }
    }
}
