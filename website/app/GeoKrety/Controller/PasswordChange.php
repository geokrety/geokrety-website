<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\PasswordToken;
use Hautelook\Phpass\PasswordHash;

class PasswordChange extends Base {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $token = new PasswordToken();
        if (!$f3->exists('PARAMS.token') && $f3->exists('POST.token')) {
            $f3->copy('POST.token', 'PARAMS.token');
        }

        // Check database for provided token
        if ($f3->exists('PARAMS.token')) {
            $token->load(array('token = ? AND used = 0 AND DATE_ADD(created_on_datetime, INTERVAL '.GK_SITE_PASSWORD_RECOVERY_CODE_DAYS_VALIDITY.' DAY) >= ? ', $f3->get('PARAMS.token'), (new \DateTime())->format('Y-m-d H:i:s')));
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
        // TODO check captcha

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
        \Event::instance()->emit('user.password.changed', $user);
        \Flash::instance()->addMessage(_('Your password has been changed.'), 'success');

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

        $f3->get('DB')->commit();

        $this->sendEmail($user);

        $f3->reroute('login');
    }

    protected function sendEmail($user) {
        $subject = GK_EMAIL_SUBJECT_PREFIX.'🔑 '._('Your password has been changed');
        $smtp = new \SMTP(GK_SMTP_HOST, GK_SMTP_PORT, GK_SMTP_SCHEME, GK_SMTP_USER, GK_SMTP_PASSWORD);
        $smtp->set('From', GK_SITE_EMAIL_SUPPORT);
        $smtp->set('To', $user->email);
        $smtp->set('Errors-To', GK_SITE_EMAIL);
        $smtp->set('Content-Type', 'text/html; charset=UTF-8');
        $smtp->set('Subject', '=?utf-8?B?'.base64_encode($subject).'?=');
        Smarty::assign('subject', $subject);
        Smarty::assign('user', $user);
        if (!$smtp->send(Smarty::fetch('password-changed.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the mail.'), 'danger');
        }
    }
}
