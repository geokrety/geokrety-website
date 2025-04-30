<?php

namespace GeoKrety\Email;

use GeoKrety\Model\Mail;

class UserContact extends BasePHPMailer {
    /**
     * Override mail From.
     *
     * @return void
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function setFromDefault() {
        $this->setFrom(GK_SITE_EMAIL_NOREPLY, 'GeoKrety');
    }

    public function sendUserMessage(Mail $mail) {
        $this->setTo($mail->to_user);
        $this->setSubject(sprintf(_('Contact from user %s'), $mail->from_user->username), '💌');
        $this->sendEmail('emails/user-contact.tpl');
    }
}
