<?php

namespace GeoKrety\Controller;

use GeoKrety\Email\AccountActivation;
use GeoKrety\Model\AccountActivationToken as AccountActivationModel;
use GeoKrety\Model\User;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Smarty;
use GeoKrety\Service\UserSettings;

class RegistrationEmail extends BaseRegistration {
    public function post(\Base $f3) {
        $f3->get('DB')->begin();
        $user = $this->user;

        // Get form values
        $user->username = $f3->get('POST.username');
        $user->_email = $f3->get('POST.email');
        $user->preferred_language = $f3->get('POST.preferred_language');
        $user->password = $f3->get('POST.password');
        $user->email_invalid = User::USER_EMAIL_UNCONFIRMED;
        $user->account_valid = User::USER_ACCOUNT_NON_ACTIVATED;

        LanguageService::changeLanguageTo($user->preferred_language);

        if (filter_var($f3->get('POST.terms_of_use'), FILTER_VALIDATE_BOOLEAN)) {
            $user->touch('terms_of_use_datetime');
        }

        // reCaptcha
        $this->checkCaptcha();

        // Check CSRF
        $this->checkCsrf();

        // Check Js Content
        if (empty($f3->get('POST.username2')) || !empty($f3->get('POST.username3'))) {
            $f3->get('DB')->rollback();
            $f3->get('DB')->begin();
            \Sugar\Event::instance()->emit('user.create-spam.js', $f3->get('POST'));
            \Flash::instance()->addMessage('Account successfully created', 'success');
            $f3->get('DB')->commit();
            $f3->reroute('@home', die: true);
        }

        // Resend validation
        $token = new AccountActivationModel();
        $token->has('user', ['lower(username) = lower(?) AND _email_hash = public.digest(lower(?), \'sha256\')', $f3->get('POST.username'), $f3->get('POST.email')]);
        $token->load(['used = ? AND created_on_datetime + cast(? as interval) >= NOW()', AccountActivationModel::TOKEN_UNUSED, GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY.' DAY']);
        if ($token->valid()) {
            $f3->get('DB')->rollback();
            $smtp = new AccountActivation();
            $smtp->sendActivationOnCreateAgain($token);
            $f3->reroute(sprintf('@user_details(@userid=%d)', $token->user->id));
            // die
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

        // Save notification preferences using UserSettings service
        $daily_digest = filter_var($f3->get('POST.daily_digest'), FILTER_VALIDATE_BOOLEAN);
        $instant_notifications = filter_var($f3->get('POST.instant_notifications'), FILTER_VALIDATE_BOOLEAN);

        $userSettings = UserSettings::instance();
        if ($daily_digest) {
            $userSettings->put($user, 'DAILY_DIGEST', 'true');
        }
        if ($instant_notifications) {
            $userSettings->put($user, 'INSTANT_NOTIFICATIONS', 'true');
        }

        $f3->get('DB')->commit();
        $f3->reroute(\Multilang::instance()->alias('user_details', ['userid' => $user->id], $user->preferred_language));
    }

    public function get(\Base $f3) {
        Smarty::render('pages/registration.tpl');
    }
}
