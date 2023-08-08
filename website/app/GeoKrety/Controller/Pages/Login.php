<?php

namespace GeoKrety\Controller;

use GeoKrety\Auth;
use GeoKrety\AuthGroup;
use GeoKrety\Model\SocialAuthProvider;
use GeoKrety\Model\User;
use GeoKrety\Model\UsersAuthenticationHistory;
use GeoKrety\Model\UserSocialAuth;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\RateLimit;
use GeoKrety\Service\Smarty;
use GeoKrety\Service\Url;
use GeoKrety\Service\Xml\Error;
use GeoKrety\Session;
use Sugar\Event;

class Login extends Base {
    private const LEGACY_API2SECID_ERROR_STRING = 'error %d ';
    private const PASSWORD_CREDENTIALS_FAILS_ERROR = 1;
    private const PASSWORD_INVALID_ACCOUNT_ERROR = 2;
    private const API2SECID_CREDENTIALS_FAILS_ERROR = 1;
    private const API2SECID_INVALID_ACCOUNT_ERROR = 2;
    private const API2SECID_EMPTY_CREDENTIALS_ERROR = 3;
    private const SECID_TOKEN_INVALID_LENGTH_ERROR = 1;
    private const SECID_TOKEN_NOT_EXISTING_ERROR = 2;
    public const NO_GRAPHIC_LOGIN = [
        'secid',
        ];

    public function loginFormFragment() {
        Smarty::render('extends:base_modal.tpl|dialog/login.tpl');
    }

    public function login(\Base $f3) {
        $this->checkCsrf('loginForm');
        $auth = new Auth('password', ['id' => 'username', 'pw' => 'password']);
        $user = $auth->login($f3->get('POST.login'), $f3->get('POST.password'));
        if ($user !== false) {
            Event::instance()->emit('user.login.password', $user);
            LanguageService::changeLanguageTo($user->preferred_language);
            if ($user->isAccountInvalid() && !$user->isAccountImported()) {
                Event::instance()->emit('user.login.password-failure', [
                    'username' => $f3->get('POST.login'),
                    'error' => self::PASSWORD_INVALID_ACCOUNT_ERROR,
                    'error_message' => 'Your account is not valid.',
                ]);
                if (GK_DEVEL) {
                    $user->resendAccountActivationEmail();
                    \Base::instance()->reroute('@home');
                }
                $user->resendAccountActivationEmail(true);
                \Base::instance()->reroute('@home', false, false);
                $f3->abort();
                $user->resendAccountActivationEmail();
                exit;
            }
            if ($f3->get('POST.remember')) {
                Session::setPersistent();
            }
            $this::connectUser($f3, $user, 'password');
        } else {
            Event::instance()->emit('user.login.password-failure', [
                'username' => $f3->get('POST.login'),
                'error' => self::PASSWORD_CREDENTIALS_FAILS_ERROR,
                'error_message' => 'Username and password doesn\'t match.',
            ]);
            \Flash::instance()->addMessage(_('Username and password doesn\'t match.'), 'danger');
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
        $f3->set('CURRENT_USER', $user->id);
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
        Smarty::assign('current_user', $user);
        Event::instance()->emit("user.login.$method-effective", $user);
        if (in_array($method, self::NO_GRAPHIC_LOGIN)) {
            return;
        }
        Session::setGKTCookie();
        LanguageService::changeLanguageTo($user->preferred_language);
        \Flash::instance()->addMessage(_('Welcome on board!'), 'success');
        $failed_count = UsersAuthenticationHistory::has_failed_attempts($user->username);
        if ($failed_count) {
            \Flash::instance()->addMessage(
                sprintf(
                    _('There was %d failed login attempts on your account, please review <a href="%s">login activity</a>'),
                    $failed_count,
                    $f3->alias('user_authentication_history'),
                ),
                'warning'
            );
        }
        if ($redirect) {
            $url = Url::unserializeGoto($user->preferred_language);
            if (is_null($url)) {
                $ml = \Multilang::instance();
                $url = $ml->alias('user_details', ['userid' => $user->id], $user->preferred_language);
            }
            if (GK_DEVEL) {
                $user->resendAccountActivationEmail();
                $f3->reroute($url);
            }
            $user->resendAccountActivationEmail(true);
            $f3->reroute($url, false, false);
            $f3->abort();
            $user->resendAccountActivationEmail();
            exit;
        }
        $user->resendAccountActivationEmail();
    }

    public function loginForm(\Base $f3) {
        if ($this->current_user) {
            $f3->reroute(['user_details', ['userid' => $this->current_user->id]]);
        }
        Smarty::render('extends:full_screen_modal.tpl|dialog/login.tpl');
    }

    public function logout(\Base $f3) {
        self::disconnectUser($f3);
        $f3->reroute('@home', false);
    }

    public static function disconnectUser(\Base $f3) {
        if (GK_DEVEL || is_null(GK_SMTP_HOST)) {
            $local_mail = $f3->get('SESSION.LOCAL_MAIL');
        }
        $user = new User();
        $user->load(['id = ?', $f3->get('SESSION.CURRENT_USER')]);
        if (!$user->dry()) {
            Event::instance()->emit('user.logout', $user);
        }
        $f3->set('CURRENT_USER', $user->id);
        $f3->clear('SESSION');
        $f3->clear('COOKIE.PHPSESSID');
        $f3->clear('COOKIE.gkt_on_behalf');
        if (GK_DEVEL || is_null(GK_SMTP_HOST)) {
            $f3->set('SESSION.LOCAL_MAIL', $local_mail);
        }
    }

    public function login2Secid() {
        Smarty::render('extends:base_minimal.tpl|dialog/login_simple.tpl');
    }

    /**
     * @return void True if authentication succeed
     */
    public function login2Secid_post(\Base $f3) {
        RateLimit::check_rate_limit_raw('API_V1_LOGIN_2_SECID');

        // No Check Csrf here else it will break legacy clients
        // $this->checkCsrf(function ($error) {
        //    echo $error;
        // });
        if (is_null($f3->get('POST.login')) or is_null($f3->get('POST.password'))) {
            http_response_code(400);
            echo $this->getApi2SecidLegacyError(self::API2SECID_EMPTY_CREDENTIALS_ERROR);
            echo _('Please provide \'login\' and \'password\' parameters.');
            Event::instance()->emit('user.login.api2secid-failure', [
                    'username' => $f3->get('POST.login'),
                    'error' => self::API2SECID_EMPTY_CREDENTIALS_ERROR,
                    'error_message' => 'Please provide \'login\' and \'password\' parameters.',
                    ]);
            exit;
        }
        $auth = new Auth('password', ['id' => 'username', 'pw' => 'password']);
        $user = $auth->login($f3->get('POST.login'), $f3->get('POST.password'));
        if ($user !== false) {
            Event::instance()->emit('user.login.api2secid', $user);
            if ($user->isAccountInvalid() && !$user->isAccountImported()) {
                http_response_code(400); // TODO what is the most logical code ? probably not 400 neither 500
                echo $this->getApi2SecidLegacyError(self::API2SECID_INVALID_ACCOUNT_ERROR);
                echo _('Your account is not valid.');
                Event::instance()->emit('user.login.api2secid-failure', [
                    'username' => $f3->get('POST.login'),
                    'error' => self::API2SECID_INVALID_ACCOUNT_ERROR,
                    'error_message' => 'Your account is not valid.',
                    ]);
                $user->resendAccountActivationEmail();
                Login::disconnectUser($f3);
                exit;
            }
            echo $user->secid;
            Event::instance()->emit('user.login.api2secid-effective', $user);
            Login::disconnectUser($f3);
            exit;
        }
        Login::disconnectUser($f3);
        Event::instance()->emit('user.login.api2secid-failure', [
            'username' => $f3->get('POST.login'),
            'error' => self::API2SECID_CREDENTIALS_FAILS_ERROR,
            'error_message' => 'Username and password doesn\'t match.',
            ]);
        echo $this->getApi2SecidLegacyError(self::API2SECID_CREDENTIALS_FAILS_ERROR);
        echo _('Username and password doesn\'t match.');
    }

    /**
     * @param string|null $secid     The secid token
     * @param bool        $streamXML Prepare to render as xml output
     *
     * @return User Connected user authentication succeed
     */
    public function secidAuth(\Base $f3, ?string $secid, bool $streamXML = true): User {
        if (strlen($secid) !== GK_SITE_SECID_CODE_LENGTH) {
            Error::buildError($streamXML, _('Invalid "secid" length'));
            Event::instance()->emit('user.login.secid-failure', [
                'secid' => $secid,
                'error' => self::SECID_TOKEN_INVALID_LENGTH_ERROR,
                'error_message' => 'Invalid "secid" length',
                ]);
            exit;
        }
        $auth = new Auth('secid');
        $user = $auth->login($secid, null);
        if ($user === false) {
            Error::buildError($streamXML, _('This "secid" does not exist'));
            Event::instance()->emit('user.login.secid-failure', [
                'secid' => $secid,
                'error' => self::SECID_TOKEN_NOT_EXISTING_ERROR,
                'error_message' => 'This "secid" does not exist',
                ]);
            exit;
        }
        Event::instance()->emit('user.login.secid', $user);
        Login::connectUser($f3, $user, 'secid');

        return $user;
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
                $user_social_auth = new UserSocialAuth();
                $user_social_auth->provider = SocialAuthProvider::getProvider($data['provider']);
                $user_social_auth->user = $this->current_user;
                $user_social_auth->uid = $data['uid'];
                if (!$user_social_auth->validate()) {
                    $f3->get('DB')->rollback();
                    $f3->reroute('@home');
                }
                $user_social_auth->save();

                // Set account as verified
                $this->current_user->account_valid = User::USER_ACCOUNT_VALID;

                // TODO a less intrusive way would be to offer user possibility to manually do this, instead of being automatic
                if (!$this->current_user->hasEmail() && !empty($data['info']['email'])) {
                    // Update account with received email, and mark it as valid
                    $this->current_user->email = $data['info']['email'];
                    $this->current_user->email_invalid = User::USER_EMAIL_NO_ERROR;
                }
                $this->current_user->save();
                $f3->get('DB')->commit();

                $this::connectUser($f3, $this->current_user, 'oauth');
                exit;
            }

            // This user is already connected to this social auth provider
            if ($user->isCurrentUser()) {
                \Flash::instance()->addMessage(sprintf(_('Your account is already linked with %s'), $data['provider']), 'info');
                $f3->reroute('@home');
            }

            // This social account is already registered with *another* account
            \Flash::instance()->addMessage(sprintf(_('This %s account is already linked with another GeoKrety account.'), $data['provider']), 'danger');
            $f3->reroute(sprintf('@user_details(@userid=%d)', $this->current_user->id));
        }

        // Create a new user
        if ($user === false) {
            $f3->set('SESSION.social_auth_data', json_encode($data));
            $f3->reroute('@registration_social');
        }

        // Log user in
        $this::connectUser($f3, $user, 'oauth');
    }

    public function socialAuthAbort(array $data) {
        \Flash::instance()->addMessage(_('Auth request was canceled.'), 'danger');
        \Base::instance()->reroute('@home');
    }

    private function getApi2SecidLegacyError(int $error): string {
        return sprintf(self::LEGACY_API2SECID_ERROR_STRING, $error);
    }
}
