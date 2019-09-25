<?php

namespace GeoKrety;

use Hautelook\Phpass\PasswordHash;
use GeoKrety\Model\User;

class Auth extends \Auth {
    protected function _geokrety($id, $pw, $realm) {
        $user = new User();
        $user->load(array('username = ?', $id));

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
