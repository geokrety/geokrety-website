<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Email\PasswordChange as PasswordChangeEmail;
use GeoKrety\Model\PasswordToken;
use GeoKrety\Service\Smarty;
use Sugar\Event;

class PasswordRecoveryChange extends Base {
    /**
     * @var PasswordToken
     */
    private $token;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);

        $token = new PasswordToken();
        if (!$f3->exists('PARAMS.token') && $f3->exists('POST.token')) {
            $f3->copy('POST.token', 'PARAMS.token');
        }

        // Check database for provided token
        if ($f3->exists('PARAMS.token')) {
            $token->load(['token = ? AND used = ? AND created_on_datetime > NOW() - cast(? as interval)', $f3->get('PARAMS.token'), PasswordToken::TOKEN_UNUSED, GK_SITE_PASSWORD_RECOVERY_CODE_DAYS_VALIDITY.' DAY']);
            if ($token->dry()) {
                Flash::instance()->addMessage(_('Sorry this token is not valid, already used or expired.'), 'danger');
            }
            $token->token = $f3->get('PARAMS.token');
        }

        $this->token = $token;
        Smarty::assign('token', $token);
    }

    public function get(\Base $f3) {
        // Reset eventual transaction
        if ($f3->get('DB')->trans()) {
            $f3->get('DB')->rollback();
        }
        Smarty::render('pages/password_change.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf();
        // Don't execute post if token is not valid
        $token = $this->token;
        if ($token->dry()) {
            $this->get($f3);
            exit();
        }

        $user = $token->user;
        $password_new = $f3->get('POST.password_new');
        $password_new_confirm = $f3->get('POST.password_new_confirm');

        // Check passwords are equals
        if ($password_new !== $password_new_confirm) {
            Flash::instance()->addMessage(_('New passwords doesn\'t match.'), 'danger');
            $this->get($f3);
            exit();
        }

        $f3->get('DB')->begin();

        // Save new password
        $user->password = $password_new;
        if (!$user->validate()) {
            $this->get($f3);
            exit();
        }
        $user->save();

        // Mark token as used
        $token->used = PasswordToken::TOKEN_VALIDATED;
        $token->touch('used_on_datetime');
        if (!$token->validate()) {
            $this->get($f3);
            exit();
        }
        $token->save();
        Event::instance()->emit('password.token.used', $token);

        // Check for eventual error
        if ($f3->get('ERROR')) {
            Flash::instance()->addMessage(_('Unexpected error occurred.'), 'danger');
            $this->get($f3);
            exit();
        }

        Flash::instance()->addMessage(_('Your password has been changed.'), 'success');
        $f3->get('DB')->commit();

        Event::instance()->emit('user.password.changed', $user);

        // Send email
        $smtp = new PasswordChangeEmail();
        $smtp->sendPasswordChangedNotification($user);

        $f3->reroute('@login');
    }
}
