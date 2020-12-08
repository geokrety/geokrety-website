<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\EmailActivationToken;
use GeoKrety\Model\User;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Smarty;

abstract class Base {
    const NO_TERMS_OF_USE_REDIRECT_URLS = [
        'login',
        'logout',
        'registration',
        'registration_social',
        'registration_activate',
        'terms_of_use',
    ];

    /**
     * @var User|null Currently logged in user
     */
    protected $current_user;

    /**
     * @var \Base|null The f3 instance
     */
    protected $f3;

    public function beforeRoute(\Base $f3) {
        $this->f3 = $f3;

        // Load supported languages
        Smarty::assign('languages', LanguageService::getSupportedLanguages(true));

        // Load current user
        $this->loadCurrentUser();

        // Check term of use acceptation
        if (!in_array($f3->get('ALIAS'), self::NO_TERMS_OF_USE_REDIRECT_URLS) && $this->isLoggedIn() && !$this->current_user->hasAcceptedTheTermsOfUse()) {
            $f3->reroute('@terms_of_use');
        }
    }

    public function loadCurrentUser() {
        if ($this->f3->exists('SESSION.CURRENT_USER')) {
            $user = new User();
            // TODO What the purpose of this filter?
            // It should have something related with class RegistrationActivate
            $user->filter('email_activation', ['used = ?', EmailActivationToken::TOKEN_UNUSED]);
            $user->load(['id = ?', $this->f3->get('SESSION.CURRENT_USER')]);
            if ($user->valid()) {
                Smarty::assign('current_user', $user);
                $this->current_user = $user;
            }
        }
    }

    public function isLoggedIn(): bool {
        return !is_null($this->current_user);
    }

//    public function afterRoute($f3) {
//        \Flash::instance()->addMessage('<pre>'.$f3->get('DB')->log().'</pre>', 'warning');
//    }

    public function checkCaptcha(string $func = 'get') {
        if (GK_GOOGLE_RECAPTCHA_SECRET_KEY) {
            $recaptcha = new \ReCaptcha\ReCaptcha(GK_GOOGLE_RECAPTCHA_SECRET_KEY);
            $resp = $recaptcha->verify($this->f3->get('POST.g-recaptcha-response'), $this->f3->get('IP'));
            if (!$resp->isSuccess()) {
                \Flash::instance()->addMessage(_('reCaptcha failed!'), 'danger');
                $this->$func($this->f3);
                exit();
            }
        }
    }
}
