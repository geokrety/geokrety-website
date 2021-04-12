<?php

namespace GeoKrety\Controller\Cli;

use Base;
use Exception;
use GeoKrety\Email\CronError;
use GeoKrety\Model\Scripts;
use GeoKrety\Model\User;

class Cron {
    const SCRIPT_NAME_REFRESH_MATERIALIZED_VIEW = 'refresh_materialized_view';

    public function checkLockedScripts() {
        $scripts = new Scripts();
        $sql = <<<'SQL'
       (EXTRACT(EPOCH FROM (DATE_TRUNC('MINUTE', NOW()) - DATE_TRUNC('MINUTE', locked_datetime)))::integer/60) >= %d
AND MOD(EXTRACT(EPOCH FROM (DATE_TRUNC('MINUTE', NOW()) - DATE_TRUNC('MINUTE', locked_datetime)))::integer/60, %d) = 0
SQL;
        $locked_scripts = $scripts->find([sprintf($sql, GK_SITE_CRON_LOCKED_MINUTES, GK_SITE_CRON_LOCKED_MINUTES)]);
        $user = new User();
        foreach ($locked_scripts ?: [] as $script) {
            $mail = new CronError();
            foreach (GK_SITE_ADMINISTRATORS as $admin_id) {
                $user->load(['id = ?', $admin_id]);
                $mail->sendLockedScript($script);
            }
        }
    }

    public function refreshMaterializedView(Base $f3) {
        $sql = 'REFRESH MATERIALIZED VIEW CONCURRENTLY gk_geokrety_in_caches;';

        $script_lock = new Scripts();
        $script_lock->load(['name = ?', static::SCRIPT_NAME_REFRESH_MATERIALIZED_VIEW]);
        try {
            $script_lock->lock(static::SCRIPT_NAME_REFRESH_MATERIALIZED_VIEW);
        } catch (Exception $exception) {
            echo sprintf("\e[0;31mE: %s\e[0m", $exception->getMessage()).PHP_EOL;
            exit();
        }

        $f3->get('DB')->exec($sql);
        $script_lock->unlock(static::SCRIPT_NAME_REFRESH_MATERIALIZED_VIEW);
    }
}
