<?php

namespace GeoKrety\Controller;

use GeoKrety\Email\AccountActivation;
use GeoKrety\Model\AccountActivationToken as AccountActivationModel;
use GeoKrety\Model\User;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Smarty;

class RegistrationEmail extends BaseRegistration {
    public function post(\Base $f3) {
        $f3->get('DB')->begin();
        $user = $this->user;

        // Get form values
        $user->username = $f3->get('POST.username');
        $user->_email = $f3->get('POST.email');
        $user->preferred_language = $f3->get('POST.preferred_language');
        $user->daily_mails = filter_var($f3->get('POST.daily_mails'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $user->password = $f3->get('POST.password');
        $user->email_invalid = User::USER_EMAIL_UNCONFIRMED;
        $user->account_valid = User::USER_ACCOUNT_INVALID;

        LanguageService::changeLanguageTo($user->preferred_language);

        if (filter_var($f3->get('POST.terms_of_use'), FILTER_VALIDATE_BOOLEAN)) {
            $user->touch('terms_of_use_datetime');
        }

        // reCaptcha
        $this->checkCaptcha();

        // Check CSRF
        $this->checkCsrf();

        // Check Js Content
        if (empty($f3->get('POST.username2'))) {
            $f3->get('DB')->rollback();
            \Sugar\Event::instance()->emit('user.create-spam.js', $f3->get('POST'));
            \Flash::instance()->addMessage('Account successfully created', 'success');
            $f3->reroute('@home', die: true);
        }

        // Resend validation
        $token = new AccountActivationModel();
        $token->has('user', ['lower(username) = lower(?) AND _email_hash = public.digest(lower(?), \'sha256\')', $f3->get('POST.username'), $f3->get('POST.email')]);
        $token->load(['used = ? AND created_on_datetime + cast(? as interval) >= NOW()', AccountActivationModel::TOKEN_UNUSED, GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY.' DAY']);
        if ($token->valid()) {
            $f3->reroute(sprintf('@user_details(@userid=%d)', $token->user->id), false, false);
            $smtp = new AccountActivation();
            $smtp->sendActivationAgain($token);
            if (!GK_DEVEL) {
                // Let unit test run smoothly
                $f3->abort();
            }
            $f3->get('DB')->rollback();
            exit;
        }

        // Check email unicity over users table
        $this->checkUniqueEmail();

        // Save
        if (!$user->validate()) {
            $f3->get('DB')->rollback();
            $this->get($f3);
            exit;
        }
        $user->save();
        $this->saveTrackingSettings();

        $f3->get('DB')->commit();
        $f3->reroute(\Multilang::instance()->alias('user_details', ['userid' => $user->id], $user->preferred_language));
    }

    public function get(\Base $f3) {
        Smarty::render('pages/registration.tpl');
    }
}
