<?php

namespace GeoKrety\Controller\Devel;

use GeoKrety\Controller\Login;
use GeoKrety\Model\User;

/**
 * Class Authentication.
 */
class Authentication extends Base {
    public function login(\Base $f3) {
        header('Content-Type: text');

        $username = $f3->get('PARAMS.username');
        $user = $this->loadUser($f3, $username);
        if ($user === false) {
            echo sprintf("Error signing in user: %s\n", $username);
        } else {
            Login::connectUser($f3, $user);
            echo sprintf("User signed in: %s\n", $username);
        }

        echo "==========\n";
        echo 'done!';
    }

    private function loadUser(\Base $f3, $username) {
        $username = $f3->get('PARAMS.username');
        $user = new User();
        $user->load(['lower(username) = lower(?)', $username]);
        if (!$user->valid()) {
            echo sprintf("Error finding user: %s\n", $username);

            return false;
        }

        return $user;
    }

    public function logout(\Base $f3) {
        header('Content-Type: text');
        Login::disconnectUser($f3);
        echo sprintf("User signed out\n");

        echo "==========\n";
        echo 'done!';
    }
}
