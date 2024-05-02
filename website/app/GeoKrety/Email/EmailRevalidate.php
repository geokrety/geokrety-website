<?php

namespace GeoKrety\Email;

use GeoKrety\Model\EmailRevalidateToken;
use GeoKrety\Service\Smarty;

class EmailRevalidate extends BasePHPMailer {
    private array $message = [];
    public const SESSION_SEND_REVALIDATION_GKV1 = 'SESSION.sendRevalidationGKv1';

    /**
     * Override mail From.
     *
     * @return void
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function setFromDefault() {
        $this->setFrom(GK_SITE_EMAIL_REGISTRATION, 'GeoKrety');
    }

    public function sendRevalidation(EmailRevalidateToken $token) {
        Smarty::assign('token', $token);
        $this->setSubject(_('Account revalidation'), '👌');
        $this->setTo($token->user, true);
        if ($this->sendEmail('emails/email-revalidate-address.tpl')) {
            $token->touch('last_notification_datetime');
            $token->save();
            \Base::instance()->clear(AccountActivation::SESSION_SEND_ACTIVATION_AGAIN);
            \Flash::instance()->addMessage(_('Mail sent.'), 'success');
        }
    }
}
