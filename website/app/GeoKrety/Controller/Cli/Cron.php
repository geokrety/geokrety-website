<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Email\CronError;
use GeoKrety\Model\Scripts;
use GeoKrety\Model\User;

class Cron {
    public function checkLockedScripts() {
        $scripts = new Scripts();
        $locked_scripts = $scripts->find([sprintf('(EXTRACT(EPOCH FROM (DATE_TRUNC(\'MINUTE\', NOW()) - DATE_TRUNC(\'MINUTE\', locked_datetime)))::integer/60) >= %d AND MOD(EXTRACT(EPOCH FROM (DATE_TRUNC(\'MINUTE\', NOW()) - DATE_TRUNC(\'MINUTE\', locked_datetime)))::integer/60, %d) = 0', GK_SITE_CRON_LOCKED_MINUTES, GK_SITE_CRON_LOCKED_MINUTES)]);
        $user = new User();
        foreach ($locked_scripts ?: [] as $script) {
            $mail = new CronError();
            foreach (GK_SITE_ADMINISTRATORS as $admin_id) {
                $user->load(['id = ?', $admin_id]);
                $mail->sendLockedScript($user, $script);
            }
        }
    }
}
