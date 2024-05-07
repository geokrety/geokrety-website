<?php

namespace GeoKrety\Email;

use GeoKrety\Model\EmailRevalidateToken;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class EmailRevalidate extends BasePHPMailer {
    private array $message = [];
    public const SESSION_SEND_REVALIDATION_GKV1 = 'SESSION.sendRevalidationGKv1';

    public const EMAIL_REVALIDATE_ACCEPTED_STATUSES = [
        User::USER_EMAIL_NO_ERROR,
        User::USER_EMAIL_UNCONFIRMED,
    ];

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

    protected function allowSend(User $user): bool {
        return $user->isAccountImported() && in_array($user->email_invalid, self::EMAIL_REVALIDATE_ACCEPTED_STATUSES);
    }

    protected function allowNonProdEnvSend(): bool {
        return true;
    }

    public function sendRevalidation(EmailRevalidateToken $token) {
        Smarty::assign('token', $token);
        $this->setSubject(_('Account revalidation'), '👌');
        $this->setTo($token->user);
        if ($this->sendEmail('emails/email-revalidate-address.tpl')) {
            $token->touch('last_notification_datetime');
            $token->save();
            \Base::instance()->clear(AccountActivation::SESSION_SEND_ACTIVATION_AGAIN);
            \Flash::instance()->addMessage(_('Mail sent.'), 'success');
        }
    }
}
