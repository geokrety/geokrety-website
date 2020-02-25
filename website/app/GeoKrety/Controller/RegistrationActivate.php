<?php

namespace GeoKrety\Controller;

use GeoKrety\Email\AccountActivation;
use GeoKrety\Model\AccountActivationToken as AccountActivationModel;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class RegistrationActivate extends Base {
    /**
     * @var AccountActivationModel
     */
    private $token;

    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        // Check database for provided token
        $token = new AccountActivationModel();
        $token->load(['token = ? AND used = ? AND DATE_ADD(created_on_datetime, INTERVAL ? DAY) >= NOW() ', $f3->get('PARAMS.token'), AccountActivationModel::TOKEN_UNUSED, GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY]);
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
        if (!$this->token->validate() || !$this->token->user->validate()) {
            $f3->get('DB')->rollback();
            $f3->reroute('@home');
        }
        $this->token->user->save();
        $this->token->save();
        $f3->get('DB')->commit();
        \Event::instance()->emit('user.activated', $this->token->user);
        $smtp = new AccountActivation();
        $smtp->sendActivationConfirm($this->token);

        Smarty::render('pages/registration_validate.tpl');
    }
}
