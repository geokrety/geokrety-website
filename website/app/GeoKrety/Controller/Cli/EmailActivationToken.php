<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\EmailActivationToken as EmailActivationTokenModel;

class EmailActivationToken {
    public function prune(\Base $f3) {
        if ($f3->exists('LOCK.EmailActivationToken.prune')) {
            echo "\e[0;31mAnother task is already running\e[0m".PHP_EOL;
            exit(1);
        }

        echo "\e[0;32mLaunch task\e[0m".PHP_EOL;
        $f3->set('LOCK.EmailActivationToken.prune', 'true', 60);
        EmailActivationTokenModel::expireOldTokens();
        $f3->clear('LOCK.EmailActivationToken.prune');
    }
}
