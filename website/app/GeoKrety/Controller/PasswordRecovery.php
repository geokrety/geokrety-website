<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\User;
use GeoKrety\Model\PasswordToken;

class PasswordRecovery extends Base {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $user = new User();
        $this->user = $user;
        Smarty::assign('user', $user);
    }

    public function get($f3) {
        Smarty::render('pages/password_recovery.tpl');
    }

    public function post($f3) {
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

        // Check database for provided email
        $user = $this->user;
        $user->email = $f3->get('POST.email');
        $user->load(array('email = ?', $user->email));
        if ($user->dry()) {
            \Flash::instance()->addMessage(_('Sorry no account using that email address.'), 'danger');
            $this->get($f3);
            die();
        }

        // Generate a new token
        $token = new PasswordToken();
        $token->user = $user;
        if (!$token->validate()) {
            $this->get($f3);
            die();
        }

        $token->save();
        \Event::instance()->emit('password.token.generated', $token);
        \Flash::instance()->addMessage(_('An email containing a validation link has been sent to the provided email address.'), 'success');

        $this->sendEmail($token);

        $f3->reroute('home');
    }

    protected function sendEmail($token) {
        $subject = GK_EMAIL_SUBJECT_PREFIX.'🔑 '._('Password reset request');
        $smtp = new \SMTP(GK_SMTP_HOST, GK_SMTP_PORT, GK_SMTP_SCHEME, GK_SMTP_USER, GK_SMTP_PASSWORD);
        $smtp->set('From', GK_SITE_EMAIL_SUPPORT);
        $smtp->set('To', $token->user->email);
        $smtp->set('Errors-To', GK_SITE_EMAIL);
        $smtp->set('Content-Type', 'text/html; charset=UTF-8');
        $smtp->set('Subject', '=?utf-8?B?'.base64_encode($subject).'?=');
        Smarty::assign('subject', $subject);
        Smarty::assign('token', $token);
        if (!$smtp->send(Smarty::fetch('password-recovery.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the mail.'), 'danger');
        }
    }
}
