<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\User;
use GeoKrety\Model\Mail;

class UserContact extends BaseUser {
    public function loadToUser($f3) {
        $user = new User();
        $user->load(array('id = ?', $f3->get('PARAMS.userid')));
        if ($user->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        $this->userTo = $user;
        Smarty::assign('userTo', $this->userTo);
    }

    public function get(\Base $f3) {
        $this->loadToUser($f3);
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_contact.tpl');
    }

    public function get_ajax(\Base $f3) {
        $this->loadToUser($f3);
        Smarty::render('extends:base_modal.tpl|dialog/user_contact.tpl');
    }

    public function post(\Base $f3) {
        // reCaptcha
        if (GK_GOOGLE_RECAPTCHA_SECRET_KEY) {
            $recaptcha = new \ReCaptcha\ReCaptcha(GK_GOOGLE_RECAPTCHA_SECRET_KEY);
            $resp = $recaptcha->verify($f3->get('POST.g-recaptcha-response'), $f3->get('IP'));
            if (!$resp->isSuccess()) {
                \Flash::instance()->addMessage(_('reCaptcha failed!'), 'danger');
                \Flash::instance()->addMessage(print_r($resp->getErrorCodes(), true), 'danger');
                $this->get($f3);
                die();
            }
        }

        $this->loadToUser($f3);
        $mail = new Mail();
        $mail->from = $this->user;
        $mail->to = $this->userTo;
        $mail->subject = $f3->get('POST.subject');
        $mail->content = $f3->get('POST.message');

        if ($mail->validate()) {
            $mail->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to save the mail.'), 'danger');
                $this->get($f3);
                die();
            } else {
                Smarty::assign('mail', $mail);
                $this->sendEmail($mail);
                \Flash::instance()->addMessage(sprintf(_('Your message to %s has been sent.'), $mail->to->username), 'success');
            }
        }

        $f3->reroute(sprintf('@user_details(@userid=%d)', $mail->to->id));
    }

    protected function sendEmail($mail) {
        $smtp = new \SMTP(GK_SMTP_HOST, GK_SMTP_PORT, GK_SMTP_SCHEME, GK_SMTP_USER, GK_SMTP_PASSWORD);
        $smtp->set('From', GK_SITE_EMAIL);
        $smtp->set('Errors-To', GK_SITE_EMAIL);
        $smtp->set('Content-Type', 'text/html; charset=UTF-8');
        $smtp->set('Subject', sprintf(_('GeoKrety: contact from user %s'), $mail->from->username));

        $smtp->set('To', $mail->to->email);
        if (!$smtp->send(Smarty::fetch('mails/user_contact.tpl'))) {
            \Flash::instance()->addMessage(_('An error occured while sending mail.'), 'danger');
            $this->get($f3);
            die();
        }
        $smtp->set('To', $mail->from->email);
        if (!$smtp->send(Smarty::fetch('mails/user_contact.tpl'))) {
            \Flash::instance()->addMessage(_('An error occured while sending mail copy.'), 'danger');
        }
    }
}
