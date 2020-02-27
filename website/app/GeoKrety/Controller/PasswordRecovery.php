<?php

namespace GeoKrety\Controller;

use GeoKrety\Email\PasswordChange as PasswordChangeEmail;
use GeoKrety\Model\PasswordToken;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

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
        $user->load(['email = ?', $user->email]);
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

        // Send email
        $smtp = new PasswordChangeEmail();
        $smtp->sendPasswordChangeToken($token);

        $f3->reroute('@home');
    }
}
