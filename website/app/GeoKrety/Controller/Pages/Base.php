<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\EmailActivationToken;
use GeoKrety\Model\User;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Smarty;

abstract class Base {
    public const NO_TERMS_OF_USE_REDIRECT_URLS = [
        'login',
        'logout',
        'registration',
        'registration_social',
        'registration_activate',
        'terms_of_use',
        'user_update_email_revalidate_token',
        'devel_mail_list',
        'devel_mail',
        'devel_mail_delete',
        'devel_mail_delete_all',
    ];

    protected ?User $current_user = null;

    protected ?\Base $f3;

    public function afterRoute() {
    }

    public function beforeRoute(\Base $f3) {
        $this->f3 = $f3;

        // Authorizations
        $access = \Access::instance();
        $access->authorize($f3->get('SESSION.user.group'));

        // Load supported languages
        Smarty::assign('languages', LanguageService::getSupportedLanguages(true));
        Smarty::assign('datatable_language_url', LanguageService::getDatatableCurrentLanguageUrl());

        // Load current user
        $this->loadCurrentUser();

        // Check term of use acceptation
        if (is_a($this->current_user, '\GeoKrety\Model\User') && !in_array($f3->get('ALIAS'), self::NO_TERMS_OF_USE_REDIRECT_URLS) && $this->isLoggedIn() && !$this->current_user->hasAcceptedTheTermsOfUse()) {
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

    public function checkCaptcha(?string $func = 'get'): ?string {
        if (GK_GOOGLE_RECAPTCHA_SECRET_KEY) {
            $recaptcha = new \ReCaptcha\ReCaptcha(GK_GOOGLE_RECAPTCHA_SECRET_KEY);
            $resp = $recaptcha->verify($this->f3->get('POST.g-recaptcha-response'), $this->f3->get('IP'));
            if (!$resp->isSuccess()) {
                $error = _('reCaptcha failed!');
                if (is_null($func)) {
                    return $error;
                }
                \Flash::instance()->addMessage($error, 'danger');
                $this->$func($this->f3);
                exit;
            }
        }

        return null;
    }

    /**
     * @param string|\Closure|null $func
     */
    protected function checkCsrf($func = 'get', array $options = []): ?string {
        if (!GK_DEVEL or (
            !\Base::instance()->exists('GET.skip_csrf')
            or !filter_var(\Base::instance()->get('GET.skip_csrf'), FILTER_VALIDATE_BOOLEAN)
        )) { // Allow skip tests only on DEVEL
            $token = $this->f3->get('POST.csrf_token');
            $csrf = $this->f3->get('SESSION.csrf');
            if (empty($token) || empty($csrf) || $token !== $csrf) {
                $error = _('CSRF error, please try again.');
                if (is_null($func)) {
                    return $error;
                }
                if (is_string($func) && method_exists($this, $func)) {
                    \Flash::instance()->addMessage($error, 'danger');
                    $this->$func($this->f3);
                } else {
                    call_user_func($func, $error, $options);
                }
                exit;
            }
        }

        return null;
    }

    public function exit($status = '') {
        $this->afterRoute();
        exit($status);
    }
}
