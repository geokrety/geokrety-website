<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\AuthGroup;
use GeoKrety\Model\User;
use GeoKrety\Email\AccountActivation;

class Login extends Base {
    const NO_REDIRECT_URLS = array(
        'login',
        'logout',
        'registration_activate',
    );

    public function loginForm($f3) {
        Smarty::render('extends:base.tpl|forms/login.tpl');
    }

    // public function loginFormFragment($f3) {
    //     Smarty::render('forms/login.tpl');
    // }

    public function login($f3) {
        $auth = new \GeoKrety\Auth('geokrety', array('id' => 'username', 'pw' => 'password'));
        $login_result = $auth->login($f3->get('POST.login'), $f3->get('POST.password'));
        if ($login_result) {
            $user = new User();
            $user->load(array('username = ?', $f3->get('POST.login')));
            if ($user->valid()) {
                if (!$user->isAccountValid() && $user->activation) {
                    $smtp = new AccountActivation();
                    $smtp->sendActivationAgainOnLogin($user->activation);
                    $f3->reroute('login');
                }

                $ml = \Multilang::instance();
                $params = $f3->unserialize(base64_decode($f3->get('GET.params')));
                $f3->set('SESSION.CURRENT_USER', $user->id);
                $f3->set('SESSION.CURRENT_USERNAME', $user->username);
                $f3->set('SESSION.IS_LOGGED_IN', true);
                if (in_array($user->id, GK_SITE_ADMINISTRATORS)) {
                    $f3->set('SESSION.user.group', AuthGroup::AUTH_LEVEL_ADMINISTRATORS);
                    $f3->set('SESSION.IS_ADMIN', true);
                } else {
                    $f3->set('SESSION.user.group', AuthGroup::AUTH_LEVEL_AUTHENTICATED);
                    $f3->set('SESSION.IS_ADMIN', false);
                }
                \Flash::instance()->addMessage(_('Welcome on board!'), 'success');
                \Event::instance()->emit('user.login', $user);
                if ($f3->exists('GET.goto')) {
                    $goto = $f3->get('GET.goto');
                    if (!in_array($goto, self::NO_REDIRECT_URLS)) {
                        $f3->reroute($ml->alias($goto, $params, $user->preferred_language));
                    }
                }
                $ml->reroute('@home', $params, $user->preferred_language);
            } else {
                \Flash::instance()->addMessage(_('Something went wrong during the login procedure.'), 'danger');
            }
        } else {
            \Flash::instance()->addMessage(_('Username and password doesn\'t match.'), 'danger');
        }
        $this->loginForm($f3);
    }

    // public function loginFragment($f3) {
    //     $this->authenticate($f3);
    //     $this->loginFormFragment($f3);
    // }

    public function logout($f3) {
        $user = new \GeoKrety\Model\User();
        $user->load(array('id = ?', $f3->get('SESSION.CURRENT_USER')));
        $f3->set('SESSION.CURRENT_USER', null);
        $f3->set('SESSION.CURRENT_USERNAME', null);
        $f3->set('SESSION.IS_LOGGED_IN', null);
        $f3->set('SESSION.user.group', null);
        \Event::instance()->emit('user.logout', $user);
        $f3->reroute('@home');
    }
}
