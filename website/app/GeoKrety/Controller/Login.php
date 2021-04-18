<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Auth;
use GeoKrety\AuthGroup;
use GeoKrety\Model\AccountActivationToken;
use GeoKrety\Model\SocialAuthProvider;
use GeoKrety\Model\User;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Smarty;
use GeoKrety\Service\Url;
use GeoKrety\Service\Xml\Error;
use GeoKrety\Session;
use Sugar\Event;

class Login extends Base {
    const NO_GRAPHIC_LOGIN = [
        'secid',
    ];

    public function loginFormFragment() {
        Smarty::render('extends:base_modal.tpl|dialog/login.tpl');
    }

    public function login(\Base $f3) {
        $auth = new Auth('password', ['id' => 'username', 'pw' => 'password']);
        $user = $auth->login($f3->get('POST.login'), $f3->get('POST.password'));
        if ($user !== false) {
            if ($f3->get('POST.remember')) {
                Session::setPersistent();
                Session::setGKTCookie();
            }
            if (!$user->isAccountValid()) {
                $f3->reroute('@home');
            }
            $this::connectUser($f3, $user, 'password');
        } else {
            Flash::instance()->addMessage(_('Username and password doesn\'t match.'), 'danger');
        }
        $this->loginForm($f3);
    }

    /**
     * @param \Base       $f3       F3 Base instance
     * @param User        $user     The user to connect to
     * @param string|null $method   The method used to connect
     * @param bool        $redirect Redirect to GOTO url
     */
    public static function connectUser(\Base $f3, User $user, ?string $method = null, bool $redirect = true) {
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

        Event::instance()->emit("user.login.$method", $user);
        if (in_array($method, self::NO_GRAPHIC_LOGIN)) {
            return;
        }
        Session::setGKTCookie();
        LanguageService::changeLanguageTo($user->preferred_language);
        Flash::instance()->addMessage(_('Welcome on board!'), 'success');
        if ($redirect) {
            $url = Url::unserializeGoto($user->preferred_language);
            if (is_null($url)) {
                $ml = \Multilang::instance();
                $url = $ml->alias('user_details', ['userid' => $user->id], $user->preferred_language);
            }
            $f3->reroute($url);
        }
    }

    public function loginForm(\Base $f3) {
        if ($this->current_user) {
            $f3->reroute(['user_details', ['userid' => $this->current_user->id]]);
        }
        Smarty::render('extends:full_screen_modal.tpl|dialog/login.tpl');
    }

    public function logout(\Base $f3) {
        $user = new User();
        $user->load(['id = ?', $f3->get('SESSION.CURRENT_USER')]);
        self::disconnectUser($f3);
        Event::instance()->emit('user.logout', $user);
        $f3->reroute('@home');
    }

    public static function disconnectUser(\Base $f3) {
        $f3->clear('SESSION');
        $f3->clear('COOKIE.PHPSESSID');
        $f3->clear('COOKIE.gkt_on_behalf');
    }

    public function login2Secid() {
        Smarty::render('extends:base_minimal.tpl|dialog/login_simple.tpl');
    }

    /**
     * @return void True if authentication succeed
     */
    public function login2Secid_post(\Base $f3) {
        if (is_null($f3->get('POST.login')) or is_null($f3->get('POST.password'))) {
            http_response_code(400);
            echo _('Please provide \'login\' and \'password\' parameters.');
            exit();
        }
        $auth = new Auth('password', ['id' => 'username', 'pw' => 'password']);
        $user = $auth->login($f3->get('POST.login'), $f3->get('POST.password'));
        if ($user !== false) {
            echo $user->secid;
            Event::instance()->emit('user.login.api2secid', $user);
            exit();
        }
        echo _('Username and password doesn\'t match.');
        //$this->loginForm();
    }

    /**
     * @param string|null $secid     The secid token
     * @param bool        $streamXML Prepare to render as xml output
     *
     * @return void True if authentication succeed
     */
    public function secidAuth(\Base $f3, ?string $secid, bool $streamXML = true) {
        if (strlen($secid) !== 128) {
            Error::buildError($streamXML, _('Invalid "secid"'));
            exit();
        }
        $auth = new Auth('secid');
        $user = $auth->login($secid, null);
        if ($user === false) {
            Error::buildError($streamXML, _('Invalid "secid"'));
            exit();
        }
        Login::connectUser($f3, $user, 'secid');
    }

    public function socialAuthSuccess(array $data) {
        $f3 = \Base::instance();
        // Call beforeRoute() manually as we come from an handler and not a route
        $this->beforeRoute($f3);

        // Do we have an already present association?
        $auth = new Auth('oauth');
        $user = $auth->login($data['uid'], $data['provider']);

        // User already authenticated here?
        if ($this->isLoggedIn()) {
            // Connect user/provider
            if ($user === false) {
                $f3->get('DB')->begin();
                $user_social_auth = new \GeoKrety\Model\UserSocialAuth();
                $user_social_auth->provider = SocialAuthProvider::getProvider($data['provider']);
                $user_social_auth->user = $this->current_user;
                $user_social_auth->uid = $data['uid'];
                if (!$user_social_auth->validate()) {
                    $f3->get('DB')->rollback();
                    $f3->reroute('@home', $die = true);
                }
                $user_social_auth->save();

                // Set account as verified
                $this->current_user->account_valid = User::USER_ACCOUNT_VALID;

                // TODO a less intrusive way would be to offer user possibility to manually do this, instead of being automatic
                if (!$this->current_user->hasEmail() && !empty($data['info']['email'])) {
                    // Update account with received email, and mark it as valid
                    $this->current_user->email = $data['info']['email'];
                    $this->current_user->email_invalid = User::USER_EMAIL_NO_ERROR;
                    AccountActivationToken::invalidateOtherUserTokens($this->current_user);
                }
                $this->current_user->save();
                $f3->get('DB')->commit();

                $f3->reroute(sprintf('@user_details(@userid=%d)', $this->current_user->id), $die = true);
            }

            // This user is already connected to this social auth provider
            if ($user->isCurrentUser()) {
                Flash::instance()->addMessage(sprintf(_('Your account is already linked with %s'), $data['provider']), 'info');
                $f3->reroute('@home', $die = true);
            }

            // This social account is already registered with *another* account
            Flash::instance()->addMessage(sprintf(_('This %s account is already linked with another GeoKrety account.'), $data['provider']), 'danger');
            $f3->reroute(sprintf('@user_details(@userid=%d)', $this->current_user->id), $die = true);
        }

        // Create a new user
        if ($user === false) {
            $f3->set('SESSION.social_auth_data', json_encode($data));
            $f3->reroute('@registration_social', $die = true);
        }

        // Log user in
        $this::connectUser($f3, $user, 'oauth');
    }

    public function socialAuthAbort(array $data) {
        Flash::instance()->addMessage(_('Auth request was canceled.'), 'danger');
        \Base::instance()->reroute('@home');
    }
}
