<?php

namespace GeoKrety;

use Hautelook\Phpass\PasswordHash;

class Auth extends \Auth {
    protected function _geokrety($id, $pw, $realm) {
        $user = new \GeoKrety\Model\User();
        $user->load(array('username = ?', $id));

        $hasher = new PasswordHash(GK_PASSWORD_HASH_ROTATION, false);
        if ($hasher->CheckPassword($pw.GK_PASSWORD_HASH.GK_PASSWORD_SEED, (string) $user->password)) {
            return true;
        }

        return false;
    }
}

// return $hasher->HashPassword($pw.GK_PASSWORD_HASH.GK_PASSWORD_SEED);
