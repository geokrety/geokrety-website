<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\AccountActivation as AccountActivationTokenModel;

class AccountActivationToken {
    public function prune(\Base $f3) {
        if ($f3->exists('LOCK.AccountActivationToken.prune')) {
            echo "\e[0;31mAnother task is already running\e[0m".PHP_EOL;

            return 1;
        }

        echo "\e[0;32mLaunch task\e[0m".PHP_EOL;
        $f3->set('LOCK.AccountActivationToken.prune', 'true', 60);
        AccountActivationTokenModel::expireOldTokens();
        $f3->clear('LOCK.AccountActivationToken.prune');
    }
}
