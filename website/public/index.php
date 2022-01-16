<?php

use GeoKrety\Email\CronError;
use GeoKrety\Service\Metrics;
use IPTools\IP;
use IPTools\Range;

require '../init-f3.php';
$f3->config('../app/assets.ini');

include __DIR__.'/../app/middleware.php';

if (!$f3->get('CLI') and !$f3->get('AJAX')) {
    $f3->ONERROR = function (Base $f3) {
        $eol = "\n";
        $error = array_diff_key(
            $f3->get('ERROR'),
            $f3->get('DEBUG') ?
                [] :
                ['trace' => 1]
        );

        if ($error['code'] === 403) {
            if ($f3->get('SESSION.user.group') === AuthGroup::AUTH_LEVEL_ANONYMOUS) {
                $error['code'] = 401;
            } else {
                Flash::instance()->addMessage(_('You are not allowed to access this page.'), 'danger');
                $f3->reroute('@home');
            }
        }
        if ($error['code'] === 401) {
            Flash::instance()->addMessage(_('Please login first.'), 'danger');
            $f3->reroute('@login');
        }
        if ($error['code'] === 404) {
            Flash::instance()->addMessage($error['text'] ?: _('This page doesn\'t exists.'), 'danger');
            $f3->reroute('@home');
        }
        $header = $f3->status($error['code']);
        $req = $f3->get('VERB').' '.$f3->get('PATH');
        echo '<!DOCTYPE html>'.$eol.
                    '<html>'.$eol.
                    '<head>'.
                        '<title>'.$error['code'].' '.$header.'</title>'.
                    '</head>'.$eol.
                    '<body>'.$eol.
                        '<h1>'.$header.'</h1>'.$eol.
                        '<p>'.$f3->encode($error['text'] ?: $req).'</p>'.$eol.
                        ($f3->get('DEBUG') ? ('<pre>'.$error['trace'].'</pre>'.$eol) : '').
                    '</body>'.$eol.
                    '</html>';
        exit(1);
    };
}

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
foreach (GK_METRICS_EXCLUDE_PATH as $path) {
    if (strpos($f3->PATH, $path) !== false) {
        foreach (GK_SYSTEM_PATH_ALLOWED_IPS as $range) {
            if (empty($f3->get('IP')) or Range::parse($range)->contains(new IP($f3->get('IP')))) {
                // We have to enable session else, sessions files ends up in /tmp folder
                ini_set('session.gc_maxlifetime', 5);
                new Session(); // Use a different backend: cache
                $f3->run();
                exit();
            }
        }
        http_response_code(403);
        exit();
    }
}

ini_set('session.gc_maxlifetime', GK_SITE_SESSION_REMEMBER);
$session = new \GeoKrety\Session($f3->get('DB'));
// Create a per session based CSRF token
if (!$f3->exists('SESSION.csrf') or empty($f3->get('SESSION.csrf'))) {
    $f3->CSRF = $session->csrf();
    $f3->copy('CSRF', 'SESSION.csrf');
}

// Authorizations
$access = \Access::instance();
$access->authorize($f3->get('SESSION.user.group'));

// Assets building
\Assets::instance();
\Assets\Sass::instance()->init();

Metrics::getOrRegisterCounter('total_requests', 'Total number of served requests', ['verb'])
    ->inc([$f3->get('VERB')]);
$f3->run();
