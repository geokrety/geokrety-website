<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\SocialAuthProvider;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class RegistrationOAuth extends BaseRegistration {
    public function post(\Base $f3) {
        if (!$f3->exists('SESSION.social_auth_data')) {
            $f3->reroute('@registration');
        }
        $social_data = json_decode($f3->get('SESSION.social_auth_data'));

        $user = $this->user;
        $user->username = $f3->get('POST.username');
        $user->account_valid = User::USER_ACCOUNT_VALID;

        if (isset($social_data->info->email)) {
            $user->_email = $social_data->info->email;
            $user->email_invalid = User::USER_EMAIL_NO_ERROR;
        }

        if (isset($social_data->raw->locale)) {
            $this->user->preferred_language = $social_data->raw->locale;
        }

        $user->daily_mails = filter_var($f3->get('POST.daily_mails'), FILTER_VALIDATE_BOOLEAN);
        if (filter_var($f3->get('POST.terms_of_use'), FILTER_VALIDATE_BOOLEAN)) {
            $user->touch('terms_of_use_datetime');
        }

        $this->checkCaptcha();
        $this->checkUniqueEmail();

        // Save
        if (!$user->validate()) {
            $this->get($f3);
            exit();
        }

        $user->save();

        // Store social auth token
        $social_auth = new \GeoKrety\Model\UserSocialAuth();
        $social_auth->user = $user;
        $social_auth->uid = $social_data->uid;
        $social_auth->provider = SocialAuthProvider::getProvider($social_data->provider);
        if (!$social_auth->validate()) {
            $this->get($f3);
            exit();
        }
        $social_auth->save();

        Login::connectUser($f3, $user, 'registration.oauth');
    }

    public function get(\Base $f3) {
        if (!$f3->exists('SESSION.social_auth_data')) {
            $f3->reroute('@registration');
        }

        $data = json_decode($f3->get('SESSION.social_auth_data'));
        Smarty::assign('social_auth_data', $data);
        Smarty::assign('social_auth', true);
        $this->user->username = $data->info->name;
        if (isset($data->info->email)) {
            $this->user->email = $data->info->email;
        }
        Smarty::render('pages/registration.tpl');
    }
}
