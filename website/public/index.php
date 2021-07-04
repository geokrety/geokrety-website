<?php

use GeoKrety\Email\CronError;
use GeoKrety\Service\Metrics;
use IPTools\IP;
use IPTools\Range;

require '../init-f3.php';
$f3->config('../app/assets.ini');

include __DIR__.'/../app/middleware.php';

if (preg_match('/^\/cron/', $f3->PATH)) {
    $f3->DEBUG = 2;
    $f3->ONERROR = function ($f3) {
        $mail = new CronError();
        $mail->sendException("$f3->PATH", $f3->get('ERROR'));
        exit(1);
    };
    $f3->config('../app/cron.ini');
    Cron::instance();
}

// Not counting pages in metrics
// No ACLs
// No session
foreach (GK_METRICS_EXCLUDE_PATH as $path) {
    if (strpos($f3->PATH, $path) !== false) {
        foreach (GK_SYSTEM_PATH_ALLOWED_IPS as $range) {
            if (empty($f3->get('IP')) or Range::parse($range)->contains(new IP($f3->get('IP')))) {
                $f3->run();
                exit();
            }
        }
        http_response_code(403);
        exit();
    }
}

// Start session on all route except those below
ini_set('session.gc_maxlifetime', GK_SITE_SESSION_REMEMBER);
new \GeoKrety\Session($f3->get('DB'));

// Authorizations
$access = \Access::instance();
$access->authorize($f3->get('SESSION.user.group'));

// Assets building
\Assets::instance();
\Assets\Sass::instance()->init();

Metrics::getOrRegisterCounter('total_requests', 'Total number of served requests', ['verb'])
    ->inc([$f3->get('VERB')]);
$f3->run();
