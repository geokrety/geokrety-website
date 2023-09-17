<?php

namespace GeoKrety\Email;

use Carbon\Carbon;
use GeoKrety\Model\EmailRevalidateToken;
use GeoKrety\Service\Smarty;

class EmailRevalidate extends BasePHPMailer {
    private array $message = [];

    public function sendRevalidation(EmailRevalidateToken $token, bool $notif_only = false) {
        $this->prepareMessageSendRevalidation();
        if ($notif_only) {
            $this->flashMessage($token);

            return;
        }
        $this->_sendRevalidation($token);
    }

    public function prepareMessageSendRevalidation() {
        $this->message['status'] = 'warning';
        $this->message['msg'][] = _('A confirmation email has been sent to your address.');
        $this->message['msg'][] = _('Please click the included link to confirm it\'s validity.');
        $this->message['msg'][] = _('The confirmation link expires in %s.');
    }

    private function _sendRevalidation(EmailRevalidateToken $token) {
        Smarty::assign('token', $token);
        $this->setSubject(_('Account revalidation'), '👌');
        $this->setTo($token->user, true);
        if ($this->sendEmail('emails/email-revalidate-address.tpl')) {
            $this->flashMessage($token);
        }
    }

    public function flashMessage($token) {
        \Flash::instance()->addMessage(sprintf(
            join(' ', $this->message['msg']),
            Carbon::instance($token->validate_expire_on_datetime)->longAbsoluteDiffForHumans(['parts' => 3, 'join' => true])
        ), $this->message['status']);
    }

    protected function setFromDefault() {
        $this->setFrom(GK_SITE_EMAIL_REGISTRATION, 'GeoKrety');
    }
}
