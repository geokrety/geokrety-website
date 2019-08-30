<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\User;
use GeoKrety\Model\Mail;

class UserContact extends BaseCurrentUser {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $mail = new Mail();
        $this->mail = $mail;
        Smarty::assign('mail', $this->mail);
    }

    public function getPostUrl(\Base $f3) {
        return $f3->alias('mail_to_user');
    }

    public function getPostRedirectUrl() {
        return sprintf('@user_details(@userid=%d)', $this->userTo->id);
    }

    public function loadToUser(\Base $f3) {
        $user = new User();
        $user->load(array('id = ?', $f3->get('PARAMS.userid')));
        if ($user->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        $this->userTo = $user;
        Smarty::assign('userTo', $this->userTo);
    }

    protected function _get(\Base $f3) {
        Smarty::assign('postUrl', $this->getPostUrl($f3));
        $this->loadToUser($f3);
    }

    public function get(\Base $f3) {
        $this->_get($f3);
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_contact.tpl');
    }

    public function get_ajax(\Base $f3) {
        $this->_get($f3);
        Smarty::render('extends:base_modal.tpl|dialog/user_contact.tpl');
    }

    public function post(\Base $f3) {
        $this->loadToUser($f3);
        $mail = $this->mail;
        $mail->from = $this->user;
        $mail->to = $this->userTo;
        $mail->subject = $f3->get('POST.subject');
        $mail->content = $f3->get('POST.message');
        Smarty::assign('mail', $mail);

        // reCaptcha
        if (GK_GOOGLE_RECAPTCHA_SECRET_KEY) {
            $recaptcha = new \ReCaptcha\ReCaptcha(GK_GOOGLE_RECAPTCHA_SECRET_KEY);
            $resp = $recaptcha->verify($f3->get('POST.g-recaptcha-response'), $f3->get('IP'));
            if (!$resp->isSuccess()) {
                \Flash::instance()->addMessage(_('reCaptcha failed!'), 'danger');
                $this->get($f3);
                die();
            }
        }

        if ($mail->validate()) {
            $mail->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to save the mail.'), 'danger');
                $this->get($f3);
                die();
            } else {
                $this->sendEmail($mail);
                \Event::instance()->emit('contact.created', $mail);
                \Flash::instance()->addMessage(sprintf(_('Your message to %s has been sent.'), $mail->to->username), 'success');
            }
        }

        $f3->reroute($this->getPostRedirectUrl());
    }

    protected function sendEmail($mail) {
        $smtp = new \SMTP(GK_SMTP_HOST, GK_SMTP_PORT, GK_SMTP_SCHEME, GK_SMTP_USER, GK_SMTP_PASSWORD);
        $smtp->set('From', GK_SITE_EMAIL);
        $smtp->set('Errors-To', GK_SITE_EMAIL);
        $smtp->set('Content-Type', 'text/html; charset=UTF-8');
        $smtp->set('Subject', GK_EMAIL_SUBJECT_PREFIX.sprintf(_('Contact from user %s'), $mail->from->username));

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
