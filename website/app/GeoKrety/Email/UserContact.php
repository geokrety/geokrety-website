<?php

namespace GeoKrety\Email;

use GeoKrety\Model\Mail;

class UserContact extends BasePHPMailer {
    protected function setFromDefault() {
        $this->setFromNotif();
    }

    public function sendUserMessage(Mail $mail) {
        $this->setTo($mail->to_user);
        $this->setSubject(sprintf(_('Contact from user %s'), $mail->from_user->username), '💌');
        $this->sendEmail('emails/user-contact.tpl');
    }
}
