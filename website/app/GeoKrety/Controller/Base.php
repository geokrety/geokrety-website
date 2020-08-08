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
        'registration_activate',
        'terms_of_use',
    ];

    /**
     * @var User|null Currently logged in user
     */
    protected $current_user;

    public function beforeRoute(\Base $f3) {
        // Load supported languages
        Smarty::assign('languages', LanguageService::getSupportedLanguages(true));

        // Load current user
        if ($f3->exists('SESSION.CURRENT_USER')) {
            $user = new User();
            $user->filter('email_activation', ['used = ?', EmailActivationToken::TOKEN_UNUSED]);
            $user->load(['id = ?', $f3->get('SESSION.CURRENT_USER')]);
            if ($user->valid()) {
                Smarty::assign('current_user', $user);
                $this->current_user = $user;
            }

            // Check term of use acceptation
            if (!in_array($f3->get('ALIAS'), self::NO_TERMS_OF_USE_REDIRECT_URLS) && !$user->hasAcceptedThetermsOfUse()) {
                $f3->reroute('@terms_of_use');
            }
        }
    }

    public function isLoggedIn(): bool {
        return !is_null($this->current_user);
    }

//    public function afterRoute($f3) {
//        \Flash::instance()->addMessage('<pre>'.$f3->get('DB')->log().'</pre>', 'warning');
//    }
}
