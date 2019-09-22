<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Controller\Base;
use GeoKrety\Model\User;

class Migrations extends Base {
    /**
     * Decode username htmlentities.
     */
    public function htmlentitesDecodeUsername() {
        $userModel = new User();
        $users = $userModel->find(array('username like ?', '%&%;%'), null, 0);

        if (!$users) {
            echo "\e[0;32mNo username to convert\e[0m".PHP_EOL;

            return;
        }

        foreach ($users as $user) {
            $user->username = html_entity_decode($user->username);
            echo sprintf(' * userid: %5s ; %s', $user->id, $user->username).PHP_EOL;
            $user->save();
        }
        echo sprintf("\e[0;32mConverted %d users\e[0m", count($users)).PHP_EOL;
    }
}
