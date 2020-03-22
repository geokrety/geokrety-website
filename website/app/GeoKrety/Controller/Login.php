<?php

namespace GeoKrety\Controller;

use Event;
use Flash;
use GeoKrety\Auth;
use GeoKrety\AuthGroup;
use GeoKrety\Email\AccountActivation;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;
use Multilang;

class Login extends Base {
    const NO_REDIRECT_URLS = [
        'login',
        'logout',
        'registration',
        'registration_activate',
    ];

    public function loginFormFragment() {
        Smarty::render('extends:base_modal.tpl|dialog/login.tpl');
    }

    public function login(\Base $f3) {
        $auth = new Auth('geokrety', ['id' => 'username', 'pw' => 'password']);
        $login_result = $auth->login($f3->get('POST.login'), $f3->get('POST.password'));
        if ($login_result) {
            $user = new User();
            $user->load(['username = ?', $f3->get('POST.login')]);
            if ($user->valid()) {
                if (!$user->isAccountValid() && $user->activation) {
                    $smtp = new AccountActivation();
                    $smtp->sendActivationAgainOnLogin($user->activation);
                    $f3->reroute('@login');
                }

                if ($f3->get('POST.remember')) {
                    $f3->set('COOKIE.PHPSESSID', $f3->get('COOKIE.PHPSESSID'), GK_SITE_SESSION_LIFETIME_REMEMBER);
                }
                $ml = Multilang::instance();
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
                Flash::instance()->addMessage(_('Welcome on board!'), 'success');
                Event::instance()->emit('user.login', $user);
                if ($f3->exists('GET.goto')) {
                    $goto = $f3->get('GET.goto');
                    if (!in_array($goto, self::NO_REDIRECT_URLS)) {
                        $query = http_build_query($f3->unserialize(base64_decode($f3->get('GET.query'))));
                        $query = (!empty($query) ? '?' : '').$query;
                        $f3->reroute($ml->alias($goto, $params, $user->preferred_language).$query);
                    }
                }
                $f3->reroute($ml->alias('home', $params, $user->preferred_language));
            } else {
                Flash::instance()->addMessage(_('Something went wrong during the login procedure.'), 'danger');
            }
        } else {
            Flash::instance()->addMessage(_('Username and password doesn\'t match.'), 'danger');
        }
        $this->loginForm($f3);
    }

    public function loginForm($f3) {
        Smarty::render('extends:full_screen_modal.tpl|dialog/login.tpl');
    }

    public function logout(\Base $f3) {
        $user = new User();
        $user->load(['id = ?', $f3->get('SESSION.CURRENT_USER')]);
        $f3->clear('SESSION');
        $f3->clear('COOKIE.PHPSESSID');
        Event::instance()->emit('user.logout', $user);
        $f3->reroute('@home');
    }
}
