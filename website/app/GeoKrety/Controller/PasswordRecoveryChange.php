<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\PasswordToken;
use Hautelook\Phpass\PasswordHash;
use GeoKrety\Email\PasswordChange as PasswordChangeEmail;

class PasswordRecoveryChange extends Base {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $token = new PasswordToken();
        if (!$f3->exists('PARAMS.token') && $f3->exists('POST.token')) {
            $f3->copy('POST.token', 'PARAMS.token');
        }

        // Check database for provided token
        if ($f3->exists('PARAMS.token')) {
            $token->load(array('token = ? AND used = ? AND DATE_ADD(created_on_datetime, INTERVAL ? DAY) >= NOW() ', $f3->get('PARAMS.token'), PasswordToken::TOKEN_UNUSED, GK_SITE_PASSWORD_RECOVERY_CODE_DAYS_VALIDITY));
            if ($token->dry()) {
                \Flash::instance()->addMessage(_('Sorry this token is not valid, already used or expired.'), 'danger');
            }
            $token->token = $f3->get('PARAMS.token');
        }

        $this->token = $token;
        Smarty::assign('token', $token);
    }

    public function get($f3) {
        // Reset eventual transaction
        if ($f3->get('DB')->trans()) {
            $f3->get('DB')->rollback();
        }
        Smarty::render('pages/password_change.tpl');
    }

    public function post($f3) {
        // Don't execute post if token is not valid
        $token = $this->token;
        if ($token->dry()) {
            $this->get($f3);
            die();
        }

        $user = $token->user;
        $password_new = $f3->get('POST.password_new');
        $password_new_confirm = $f3->get('POST.password_new_confirm');

        // Check passwords are equals
        if ($password_new !== $password_new_confirm) {
            \Flash::instance()->addMessage(_('New passwords doesn\'t match.'), 'danger');
            $this->get($f3);
            die();
        }

        $f3->get('DB')->begin();

        // Save new password
        $hasher = new PasswordHash(GK_PASSWORD_HASH_ROTATION, false);
        $user->password = $hasher->HashPassword($password_new.GK_PASSWORD_HASH.GK_PASSWORD_SEED);
        if (!$user->validate()) {
            $this->get($f3);
            die();
        }
        $user->save();

        // Mark token as used
        $token->used = 1;
        $token->touch('used_on_datetime');
        if (!$token->validate()) {
            $this->get($f3);
            die();
        }
        $token->save();
        \Event::instance()->emit('password.token.used', $token);

        // Check for eventual error
        if ($f3->get('ERROR')) {
            \Flash::instance()->addMessage(_('Unexpected error occured.'), 'danger');
            $this->get($f3);
            die();
        }

        \Flash::instance()->addMessage(_('Your password has been changed.'), 'success');
        $f3->get('DB')->commit();

        \Event::instance()->emit('user.password.changed', $user);

        // Send email
        $smtp = new PasswordChangeEmail();
        $smtp->sendPasswordChangedNotification($user);

        $f3->reroute('login');
    }
}
