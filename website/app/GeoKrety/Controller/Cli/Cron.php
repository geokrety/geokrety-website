<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Email\CronError;
use GeoKrety\Model\AuditLog;
use GeoKrety\Model\AuditPost;
use GeoKrety\Model\Scripts;
use GeoKrety\Model\User;
use GeoKrety\Model\UsersAuthenticationHistory;
use GeoKrety\Service\RateLimit;
use GeoKrety\Session;

class Cron {
    use Script;

    public function cleanExpiredSessions() {
        $this->script_start(__METHOD__);
        Session::cleanExpired();
        $this->script_end();
    }

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

    public function refreshMaterializedView(\Base $f3) {
        $this->script_start(__METHOD__);
        $sql = 'REFRESH MATERIALIZED VIEW CONCURRENTLY gk_geokrety_in_caches;';
        $f3->get('DB')->exec($sql);
        $this->script_end();
    }

    public function refreshSiteStats(\Base $f3) {
        $this->script_start(__METHOD__);
        $sql = 'SELECT moves_stats_updater();';
        $f3->get('DB')->exec($sql);
        $this->script_end();
    }

    public function expungeAuditLogs() {
        $this->script_start(__METHOD__);
        $audit = new AuditLog();
        $audit->expungeOld();
        $this->script_end();
    }

    public function expungeAuditPosts() {
        $this->script_start(__METHOD__);
        $audit = new AuditPost();
        $audit->expungeOld();
        $this->script_end();
    }

    public function expungeUserAuthenticationHistory() {
        $this->script_start(__METHOD__);
        $audit = new UsersAuthenticationHistory();
        $audit->expungeOld();
        $this->script_end();
    }

    public function purgeRateLimitFull() {
        $this->script_start(__METHOD__);
        RateLimit::purge();
        $this->script_end();
    }
}
