<?php

namespace GeoKrety\Email;

use GeoKrety\Model\Mail;
use GeoKrety\Service\Smarty;

class UserContact extends Base {
    protected function setFromDefault() {
        $this->setFromSupport();
    }

    public function sendUserMessage(Mail $mail) {
        $this->setTo($mail->to_user);
        $this->setSubject('💌 '.sprintf(_('Contact from user %s'), $mail->from_user->username));

        if (!$this->send(Smarty::fetch('email-user-contact.html'))) {
            \Flash::instance()->addMessage(_('An error occurred while sending mail.'), 'danger');
        }
    }
}
