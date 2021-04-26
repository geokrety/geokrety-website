<?php

namespace GeoKrety;

use Flash;
use GeoKrety\Model\User;
use Hautelook\Phpass\PasswordHash;

class Auth extends \Auth {
    /**
     * @param string $id The username or email address used for validation
     * @param string $pw The password used for validation
     *
     * @return false|User The logged in user object or false
     */
    protected function _password(string $id, string $pw) {
        $user = new User();
        if ($user->count(['lower(username) = lower(?) OR _email_hash = public.digest(lower(?), \'sha256\')', $id, $id]) > 1) {
            $f3 = \Base::instance();
            Flash::instance()->addMessage(sprintf(_('Multiple accounts share the same email address. Please <a href="%s">contact us</a>.'), $f3->alias('contact_us')), 'danger');
            $f3->reroute('@home');
        }
        $user->load(['lower(username) = lower(?) OR _email_hash = public.digest(lower(?), \'sha256\')', $id, $id]);

        if ($user->valid()) {
            $hasher = new PasswordHash(GK_PASSWORD_HASH_ROTATION, false);
            if ($hasher->CheckPassword($pw.GK_PASSWORD_HASH.GK_PASSWORD_SEED, (string) $user->password)) {
                return $user;
            }
        }

        return false;
    }

    /**
     * @param string $secid The secid to validate
     *
     * @return false|User The logged in user object or false
     */
    protected function _secid(string $secid) {
        $user = new User();
        $user->load(['_secid_hash = public.digest(?, \'sha256\')', $secid]);
        if ($user->valid()) {
            return $user;
        }

        return false;
    }

    /**
     * @param string $id
     * @param string $pw
     * @param null   $realm
     *
     * @return bool|User
     */
    public function login($id, $pw, $realm = null) {
        return parent::login($id, $pw, $realm);
    }

    /**
     * @param string $uid      The uid returned by OAuth provider
     * @param string $provider The used provider name
     *
     * @return false|User The logged in user object or false
     */
    protected function _oauth(string $uid, string $provider) {
        $user = new User();
        $user->has('social_auth', ['uid = ?', $uid]);
        $user->has('social_auth.provider', ['name = ?', $provider]);
        $user->load();
        if ($user->valid()) {
            return $user;
        }

        return false;
    }

    public static function hash_password($value) {
        $hasher = new PasswordHash(GK_PASSWORD_HASH_ROTATION, false);

        return $hasher->HashPassword($value.GK_PASSWORD_HASH.GK_PASSWORD_SEED);
    }
}
