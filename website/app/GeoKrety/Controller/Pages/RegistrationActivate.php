<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Email\AccountActivation;
use GeoKrety\Model\AccountActivationToken;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;
use Sugar\Event;

class RegistrationActivate extends Base {
    private AccountActivationToken $token;

    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        // Check database for provided token
        $token = new AccountActivationToken();
        $token->load(['token = ? AND used = ? AND created_on_datetime + cast(? as interval) >= NOW() ', $f3->get('PARAMS.token'), AccountActivationToken::TOKEN_UNUSED, GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY.' DAY']);
        if ($token->dry()) {
            Flash::instance()->addMessage(_('Sorry this token is not valid, already used or expired.'), 'danger');
            $f3->reroute('@home');
        }

        $this->token = $token;
        Smarty::assign('token', $token);
    }

    public function get(\Base $f3) {
        $f3->get('DB')->begin();
        $this->token->used = AccountActivationToken::TOKEN_VALIDATED;
        $this->token->touch('used_on_datetime');
        $this->token->validating_ip = \Base::instance()->get('IP');
        $this->token->user->account_valid = User::USER_ACCOUNT_VALID;
        $this->token->user->email_invalid = User::USER_EMAIL_NO_ERROR;
        if (!$this->token->validate() || !$this->token->user->validate()) {
            $f3->get('DB')->rollback();
            $f3->reroute('@home');
        }
        $this->token->user->save();
        Login::connectUser($f3, $this->token->user, 'registration.activate', false);
        $this->loadCurrentUser();
        $this->token->save();
        $f3->get('DB')->commit();

        // Let unit test run smoothly
        Smarty::render('pages/registration_validated.tpl');
        if (!GK_DEVEL) {
            $f3->abort();
        }
        Event::instance()->emit('user.activated', $this->token->user);
        Event::instance()->emit('activation.token.used', $this->token);
        $smtp = new AccountActivation();
        $smtp->sendActivationConfirm($this->token);
    }
}
