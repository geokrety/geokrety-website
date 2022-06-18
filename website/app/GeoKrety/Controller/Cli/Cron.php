<?php

namespace GeoKrety\Controller\Cli;

use Base;
use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Email\CronError;
use GeoKrety\Model\Scripts;
use GeoKrety\Model\User;

class Cron {
    use Script;

    public function checkLockedScripts() {
        $this->script_start(__METHOD__);
        $scripts = new Scripts();
        $sql = <<<'SQL'
       (EXTRACT(EPOCH FROM (DATE_TRUNC('MINUTE', NOW()) - DATE_TRUNC('MINUTE', locked_on_datetime)))::integer/60) >= %d
AND MOD(EXTRACT(EPOCH FROM (DATE_TRUNC('MINUTE', NOW()) - DATE_TRUNC('MINUTE', locked_on_datetime)))::integer/60, %d) = 0
AND acked_on_datetime = ?
SQL;
        $locked_scripts = $scripts->find([sprintf(
            $sql,
            GK_SITE_CRON_LOCKED_MINUTES,
            GK_SITE_CRON_LOCKED_MINUTES,
            null,
        )]);
        $locked_scripts = $locked_scripts ?: [];
        $user = new User();
        foreach ($locked_scripts as $script) {
            $mail = new CronError();
            foreach (GK_SITE_ADMINISTRATORS as $admin_id) {
                $user->load(['id = ?', $admin_id]);
                $mail->sendLockedScript($script);
            }
        }
        $this->script_end();
    }

    public function refreshMaterializedView(Base $f3) {
        $this->script_start(__METHOD__);
        $sql = 'REFRESH MATERIALIZED VIEW CONCURRENTLY gk_geokrety_in_caches;';
        $f3->get('DB')->exec($sql);
        $this->script_end();
    }

    public function refreshSiteStats(Base $f3) {
        $this->script_start(__METHOD__);
        $sql = 'SELECT moves_stats_updater();';
        $f3->get('DB')->exec($sql);
        $this->script_end();
    }

    public function expungeAudiLogs(Base $f3) {
        $this->script_start(__METHOD__);
        $sql = 'DELETE FROM gk_audit_logs where log_datetime < NOW() - cast(? as interval)';
        $f3->get('DB')->exec($sql, [GK_AUDIT_LOGS_EXCLUDE_RETENTION_DAYS.' DAY']);
        $this->script_end();
    }

    public function expungeAudiPosts(Base $f3) {
        $this->script_start(__METHOD__);
        $sql = 'DELETE FROM gk_audit_posts where created_on_datetime < NOW() - cast(? as interval)';
        $f3->get('DB')->exec($sql, [GK_AUDIT_POST_EXCLUDE_RETENTION_DAYS.' DAY']);
        $this->script_end();
    }
}
