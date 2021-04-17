<?php

namespace GeoKrety\Controller;

use GeoKrety\Email\AccountActivation;
use GeoKrety\Model\AccountActivationToken as AccountActivationModel;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class RegistrationActivate extends Base {
    private AccountActivationModel $token;

    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        // Check database for provided token
        $token = new AccountActivationModel();
        $token->load(['token = ? AND used = ? AND created_on_datetime + cast(? as interval) >= NOW() ', $f3->get('PARAMS.token'), AccountActivationModel::TOKEN_UNUSED, GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY.' DAY']);
        if ($token->dry()) {
            \Flash::instance()->addMessage(_('Sorry this token is not valid, already used or expired.'), 'danger');
            $f3->reroute('@home');
        }

        $this->token = $token;
        Smarty::assign('token', $token);
    }

    public function get(\Base $f3) {
        $f3->get('DB')->begin();

        $this->token->used = AccountActivationModel::TOKEN_VALIDATED;
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

        Smarty::render('pages/registration_validated.tpl');
        // Let unit test run smoothly
        if (!GK_DEVEL) {
            $f3->abort();
        }
        \Sugar\Event::instance()->emit('user.activated', $this->token->user);
        \Sugar\Event::instance()->emit('activation.token.used', $this->token);
        $smtp = new AccountActivation();
        $smtp->sendActivationConfirm($this->token);
    }
}
