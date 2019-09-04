<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\User;
use GeoKrety\Model\AccountActivation as AccountActivationModel;
use GeoKrety\Email\AccountActivation;

class Registration extends Base {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $user = new User();
        $this->user = $user;
        Smarty::assign('user', $this->user);
    }

    public function get(\Base $f3) {
        // Reset eventual transaction
        if ($f3->get('DB')->trans()) {
            $f3->get('DB')->rollback();
        }
        Smarty::render('pages/registration.tpl');
    }

    public function post(\Base $f3) {
        $f3->get('DB')->begin();
        $user = $this->user;
        $user->username = $f3->get('POST.username');
        $user->email = $f3->get('POST.email');
        $user->preferred_language = $f3->get('POST.preferred_language');
        $user->daily_mails = filter_var($f3->get('POST.daily_mails'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $user->password = $f3->get('POST.password');

        if (filter_var($f3->get('POST.terms_of_use'), FILTER_VALIDATE_BOOLEAN)) {
            $user->touch('terms_of_use_datetime');
        }

        // Resend validation
        $token = new AccountActivationModel();
        $token->has('user', array('username = ? AND email = ?', $f3->get('POST.username'), $f3->get('POST.email')));
        $token->load(array('used = ? AND DATE_ADD(created_on_datetime, INTERVAL ? DAY) >= NOW()', AccountActivationModel::TOKEN_UNUSED, GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY));
        if ($token->valid()) {
            $smtp = new AccountActivation();
            $smtp->sendActivationAgain($token);
            $f3->reroute(sprintf('@user_details(@userid=%d)', $token->user->id));
        }

        // Save
        if (!$user->validate()) {
            $this->get($f3);
            die();
        }
        $user->save();

        $f3->get('DB')->commit();
        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }
}
