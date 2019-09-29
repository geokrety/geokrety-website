<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\User;

class UserBanner {
    public function generateAll() {
        $userModel = new User();
        $users = $userModel->find();

        if (!$users) {
            echo "\e[0;32mNo user found\e[0m".PHP_EOL;

            return;
        }

        foreach ($users as $user) {
            \GeoKrety\Service\UserBanner::generate($user);
            echo sprintf(' * generated banner for  %5s ; %s', $user->id, $user->username).PHP_EOL;
        }
        echo sprintf("\e[0;32mConverted %d users\e[0m", count($users)).PHP_EOL;
    }

    public function generateByUserId($f3) {
        $user = new User();
        $user->load(array('id = ?', $f3->get('PARAMS.userid')));
        if ($user->dry()) {
            echo "\e[0;32mNo user found\e[0m".PHP_EOL;

            return;
        }
        \GeoKrety\Service\UserBanner::generate($user);
        echo sprintf(' * generated banner for  %5s ; %s', $user->id, $user->username).PHP_EOL;
    }
}
