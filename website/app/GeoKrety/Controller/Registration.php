<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Email\AccountActivation;
use GeoKrety\Model\AccountActivationToken as AccountActivationModel;
use GeoKrety\Model\SocialAuthProvider;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class Registration extends Base {
    /**
     * @var User
     */
    private $user;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);

        // TODO: use Traits here?
        $user = new User();
        $this->user = $user;
        Smarty::assign('user', $this->user);
    }

    public function get(\Base $f3) {
        Smarty::render('pages/registration.tpl');
    }

    public function get_social_auth(\Base $f3) {
        if ($f3->exists('SESSION.social_auth_data')) {
            $data = json_decode($f3->get('SESSION.social_auth_data'));
            Smarty::assign('social_auth_data', $data);
            Smarty::assign('social_auth', true);
            $this->user->username = $data->info->name;
            if (isset($data->info->email)) {
                $this->user->email = $data->info->email;
            }
        }
        $this->get($f3);
    }

    public function post_social_auth(\Base $f3) {
        if (!$f3->exists('SESSION.social_auth_data')) {
            $f3->reroute('registration');
        }

        $f3->get('DB')->begin();
        $user = $this->user;
        $user->username = $f3->get('POST.username');
        $user->daily_mails = false;

        if (filter_var($f3->get('POST.terms_of_use'), FILTER_VALIDATE_BOOLEAN)) {
            $user->touch('terms_of_use_datetime');
        }

        // reCaptcha
        if (GK_GOOGLE_RECAPTCHA_SECRET_KEY) {
            $recaptcha = new \ReCaptcha\ReCaptcha(GK_GOOGLE_RECAPTCHA_SECRET_KEY);
            $resp = $recaptcha->verify($f3->get('POST.g-recaptcha-response'), $f3->get('IP'));
            if (!$resp->isSuccess()) {
                \Flash::instance()->addMessage(_('reCaptcha failed!'), 'danger');
                $f3->get('DB')->rollback();
                $this->get_social_auth($f3);
                die();
            }
        }

        // Save
        if (!$user->validate()) {
            $f3->get('DB')->rollback();
            $this->get_social_auth($f3);
            die();
        }
        $user->save();

        // Store social auth token
        $social_data = json_decode($f3->get('SESSION.social_auth_data'));
        $social_auth = new \GeoKrety\Model\UserSocialAuth();
        $social_auth->user = $user;
        $social_auth->uid = $social_data->uid;
        $social_auth->provider = SocialAuthProvider::getProvider($social_data->provider);
        if (!$social_auth->validate()) {
            $f3->get('DB')->rollback();
            $this->get_social_auth($f3);
            die();
        }
        $social_auth->save();

        $f3->get('DB')->commit();
        Login::connectUser($f3, $user);
    }

    public function post(\Base $f3) {
        $f3->get('DB')->begin();
        $user = $this->user;
        $user->username = $f3->get('POST.username');
        $user->_email = $f3->get('POST.email');
        $user->preferred_language = $f3->get('POST.preferred_language');
        $user->daily_mails = filter_var($f3->get('POST.daily_mails'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $user->password = $f3->get('POST.password');

        if (filter_var($f3->get('POST.terms_of_use'), FILTER_VALIDATE_BOOLEAN)) {
            $user->touch('terms_of_use_datetime');
        }

        // reCaptcha
        if (GK_GOOGLE_RECAPTCHA_SECRET_KEY) {
            $recaptcha = new \ReCaptcha\ReCaptcha(GK_GOOGLE_RECAPTCHA_SECRET_KEY);
            $resp = $recaptcha->verify($f3->get('POST.g-recaptcha-response'), $f3->get('IP'));
            if (!$resp->isSuccess()) {
                \Flash::instance()->addMessage(_('reCaptcha failed!'), 'danger');
                $f3->get('DB')->rollback();
                $this->get($f3);
                die();
            }
        }

        // Resend validation
        $token = new AccountActivationModel();
        $token->has('user', ['lower(username) = lower(?) AND _email_hash = public.digest(lower(?), \'sha256\')', $f3->get('POST.username'), $f3->get('POST.email')]);
        $token->load(['used = ? AND created_on_datetime + cast(? as interval) >= NOW()', AccountActivationModel::TOKEN_UNUSED, GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY.' DAY']);
        if ($token->valid()) {
            $f3->reroute(sprintf('@user_details(@userid=%d)', $token->user->id), false, false);
            $smtp = new AccountActivation();
            if (!GK_DEVEL) {
                $f3->abort(); // Send response to client now
            }
            $smtp->sendActivationAgain($token);
            die();
        }

        // Check email unicity over users table
        if ($user->count(['_email_hash = public.digest(lower(?), \'sha256\')', $f3->get('POST.email')], null, 0)) { // no cache
            Flash::instance()->addMessage(_('Sorry but this mail address is already in use.'), 'danger');
            $f3->get('DB')->rollback();
            $this->get($f3);
            die();
        }

        // Save
        if (!$user->validate()) {
            $f3->get('DB')->rollback();
            $this->get($f3);
            die();
        }
        $user->save();

        $f3->get('DB')->commit();
        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }
}
