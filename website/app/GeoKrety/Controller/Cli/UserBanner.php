<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\User;

class UserBanner {
    public function generateAll() {
        $userModel = new User();

        $user_count = $userModel->count();
        if (!$user_count) {
            echo "\e[0;32mNo user found\e[0m".PHP_EOL;

            return;
        }
        echo sprintf('%d users to proceed', $user_count).PHP_EOL;

        // Paginate the table resultset as it may blow ram!
        define('PER_PAGE', 10);
        $total_pages = ceil($user_count / PER_PAGE);
        $counter = 0;
        for ($i = 0; $i < $total_pages; ++$i) {
            $subset = $userModel->paginate($i, PER_PAGE);
            foreach ($subset['subset'] as $user) {
                \GeoKrety\Service\UserBanner::generate($user);
                echo sprintf(' * generated banner for  %5s ; %s', $user->id, $user->username).PHP_EOL;
                ob_flush();
                ++$counter;
            }
        }

        echo sprintf("\e[0;32mGenerated %d user banners\e[0m", $counter).PHP_EOL;
    }

    public function generateByUserId($f3) {
        $user = new User();
        $user->load(['id = ?', $f3->get('PARAMS.userid')]);
        if ($user->dry()) {
            echo "\e[0;32mNo user found\e[0m".PHP_EOL;

            return;
        }
        \GeoKrety\Service\UserBanner::generate($user);
        echo sprintf(' * generated banner for  %5s ; %s', $user->id, $user->username).PHP_EOL;
    }
}
