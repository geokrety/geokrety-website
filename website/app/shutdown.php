<?php

register_shutdown_function('shutdown_force_send_response_to_client', $f3);
register_shutdown_function('shutdown_prometheus_metrics', $f3);
register_shutdown_function('shutdown_audit_post', $f3);

// Piwik
if (GK_PIWIK_ENABLED) {
    register_shutdown_function('shutdown_piwik', $f3);
}

function shutdown_force_send_response_to_client(Base $f3) {
    if (!headers_sent()) {
        $f3->abort();
    }
}

function shutdown_piwik(Base $f3) {
    if (!\GeoKrety\Service\UserSettings::getForCurrentUser('TRACKING_OPT_OUT')) {
        try {
            $matomoTracker = new \GeoKrety\Service\MatomoTracker(GK_PIWIK_SITE_ID, GK_PIWIK_URL);
            $matomoTracker->setTokenAuth(GK_PIWIK_TOKEN);
            $matomoTracker->setIp($f3->get('IP'));
            $matomoTracker->setVisitorId(substr(md5($f3->get('IP').session_id()), 0, 16));
            $matomoTracker->doTrackPageView(\Base::instance()->PATH);
            \Sugar\Event::instance()->emit('tracker.success');
        } catch (RuntimeException $e) {
            \Sugar\Event::instance()->emit('tracker.timeout');
        }
    } else {
        \Sugar\Event::instance()->emit('tracker.skipped');
    }
}

function shutdown_prometheus_metrics(Base $f3) {
    \GeoKrety\Service\Metrics::getOrRegisterCounter('total_requests', 'Total number of served requests', ['verb'])
        ->inc([$f3->get('VERB')]);
}

function shutdown_audit_post(Base $f3) {
    // Audit POST logs
    if (sizeof($f3->get('POST'))) {
        $has_route_match = 0;
        if (GK_AUDIT_LOGS_EXCLUDE_PATH_BYPASS !== true) {
            foreach (GK_AUDIT_LOGS_EXCLUDE_PATH as $path) { // use?: !in_array()
                if (strpos($f3->PATH, $path) !== false) {
                    ++$has_route_match;
                }
            }
        }
        if ($has_route_match === 0 || GK_AUDIT_LOGS_EXCLUDE_PATH_BYPASS) {
            $audit = new \GeoKrety\Model\AuditPost();
            $audit->route = $f3->PATH;
            $audit->payload = json_encode($f3->get('POST')); // As safety guard, replace any *password* but placeholder (what about other patterns?)
            try {
                $audit->save();
                $f3->set('AUDIT_POST_ID', $audit->id);
            } catch (Exception $e) {
                file_put_contents('/tmp/error.log', $e->getMessage(), FILE_APPEND | LOCK_EX);
            }
        }
    }
}
