<?php

namespace GeoKrety\Controller;

use Event;
use Flash;
use GeoKrety\Auth;
use GeoKrety\AuthGroup;
use GeoKrety\Email\AccountActivation;
use GeoKrety\Model\SocialAuthProvider;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;
use GeoKrety\Session;
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
            $user->load(['lower(username) = lower(?)', $f3->get('POST.login')]);
            if ($user->valid()) {
                if (!$user->isAccountValid() && $user->activation) {
                    $smtp = new AccountActivation();
                    $smtp->sendActivationAgainOnLogin($user->activation);
                    $f3->reroute('@login');
                }

                if ($f3->get('POST.remember')) {
                    $f3->set('COOKIE.PHPSESSID', $f3->get('COOKIE.PHPSESSID'), GK_SITE_SESSION_LIFETIME_REMEMBER);
                    Session::setPersistent($f3->get('COOKIE.PHPSESSID'));
                }

                $this::connectUser($f3, $user);
            } else {
                Flash::instance()->addMessage(_('Something went wrong during the login procedure.'), 'danger');
            }
        } else {
            Flash::instance()->addMessage(_('Username and password doesn\'t match.'), 'danger');
        }
        $this->loginForm();
    }

    public function loginForm() {
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

    public static function connectUser(\Base $f3, User $user) {
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
    }

    public function socialAuthSuccess(array $data) {
        $f3 = \Base::instance();
        // Call before route manually as we come from an handler and not a route
        $this->beforeRoute($f3);

        // Do we have an already present association?
        $user = new User();
        $user->has('social_auth', ['uid = ?', $data['uid']]);
        $user->has('social_auth.provider', ['name = ?', $data['provider']]);
        $user->load();

        // User already authenticated here?
        if ($this->isLoggedIn()) {
            $f3->get('DB')->begin();
            // Connect user/provider
            if ($user->dry()) {
                $user_social_auth = new \GeoKrety\Model\UserSocialAuth();
                $user_social_auth->provider = SocialAuthProvider::getProvider($data['provider']);
                $user_social_auth->user = $this->current_user;
                $user_social_auth->uid = $data['uid'];
                if (!$user_social_auth->validate()) {
                    $f3->get('DB')->rollback();
                    $f3->reroute('@home');
                    die();
                }
                $user_social_auth->save();

                // Set account as verified
                $this->current_user->account_valid = User::USER_ACCOUNT_VALID;

                // TODO a less intrusive way would be to offer user possibility to manually do this, instead of being automatic
                if (is_null($this->current_user->email) && !empty($data['info']['email'])) {
                    // Update account with received email, and no validation
                    $this->current_user->email = $data['info']['email'];
                    $this->current_user->email_invalid = User::USER_EMAIL_NO_ERROR;
                }
                $this->current_user->save();
                $f3->get('DB')->commit();

                Flash::instance()->addMessage(sprintf(_('Your account is now fully connected with %s'), $data['provider']), 'success');
                $f3->reroute(sprintf('@user_details(@userid=%d)', $this->current_user->id));
            }

            // This user is already connected to this social auth provider
            if ($user->isCurrentUser()) {
//                Flash::instance()->addMessage(sprintf(_('Your account is already connected with %s'), $data['provider']), 'info');
                $f3->reroute('@home');
            }

            // This social account is already registered with *another* account
            Flash::instance()->addMessage(sprintf(_('Your %s account is already connected with another GeoKrety account.'), $data['provider']), 'danger');
            $f3->reroute('@home');
        }

        // Create a new user
        if ($user->dry()) {
            $f3->set('SESSION.social_auth_data', json_encode($data));
            $f3->reroute('@registration_social');
        }

        // Log user in
        $this::connectUser($f3, $user);
    }

    public function socialAuthAbort(array $data) {
        Flash::instance()->addMessage(_('Auth request was canceled.'), 'danger');
        \Base::instance()->reroute('@home');
    }
}
