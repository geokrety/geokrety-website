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

    public function sendActivationConfirm(AccountActivationModel $token) {
        $this->setSubject('🎉 '._('Account activated'));
        $this->set('To', $token->user->email);
        Smarty::assign('token', $token);
        $emailContent = Smarty::fetch('email-account-activated.html');
        if (GK_DEV_LOCAL) {
            $this->localMail($emailContent, '___activated.html');
        } else if (!$this->send($emailContent)) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');
        }
    }

    protected function sendEmail(AccountActivationModel $token) {
        $this->setSubject('🎉 '._('Welcome on GeoKrety.org'));
        $this->set('To', $token->user->email);
        Smarty::assign('token', $token);
        $emailContent = Smarty::fetch('email-account-activation.html');
        if (GK_DEV_LOCAL) {
            $this->localMail($emailContent, '___activate.html');
        } else if (!$this->send($emailContent)) {
           //  \Flash::instance()->addMessage(_('An error occured while sending the activation mail.'), 'danger');
        } else {
            if (!is_null($this->message)) {
                \Flash::instance()->addMessage(sprintf($this->message, Carbon::instance($token->expire_on_datetime)->longAbsoluteDiffForHumans(['parts' => 3, 'join' => true])), 'success');
            }
        }
    }

    /**
      * Local DEV only
      * Instead of using smtp server to send an email, provide a local file with email content.
      */
    protected function localMail($emailContent, $localFilename) {
        $targetFile = GK_DEV_CACHE_DIR.$localFilename;
        file_put_contents($targetFile, $emailContent);
        \Flash::instance()->addMessage(sprintf("local mail <a href='/%s' target='_new'>file</a>", $targetFile), 'success');
    }
}
