<?php

namespace GeoKrety;

use Flash;
use GeoKrety\Model\User;
use Hautelook\Phpass\PasswordHash;

class Auth extends \Auth {
    protected function _geokrety($id, $pw, $realm) {
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
                return true;
            }
        }

        return false;
    }

    public static function hash_password($value) {
        $hasher = new PasswordHash(GK_PASSWORD_HASH_ROTATION, false);

        return $hasher->HashPassword($value.GK_PASSWORD_HASH.GK_PASSWORD_SEED);
    }
}
