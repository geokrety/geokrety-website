<?php

use GeoKrety\Email\CronError;
use GeoKrety\Service\Metrics;
use IPTools\IP;
use IPTools\Range;

require '../init-f3.php';
$f3->config('../app/assets.ini');

include __DIR__.'/../app/middleware.php';
header('X-GK-Version: '.GK_SITE_USER_AGENT);

if (!$f3->get('CLI') and !$f3->get('AJAX')) {
    $f3->ONERROR = function (Base $f3) {
        $eol = "\n";
        $error = array_diff_key(
            $f3->get('ERROR'),
            $f3->get('DEBUG') ?
                [] :
                ['trace' => 1]
        );

        if ($error['code'] === 400) {
            session_status() == PHP_SESSION_ACTIVE && Flash::instance()->addMessage($error['text'] ?: _('Your request seems invalid.'), 'danger');
            $f3->set('SESSION.HTTP_RETURN_CODE', 400);
            $f3->reroute($f3->get('ERROR_REDIRECT') ?: '@home');
        }
        if ($error['code'] === 401) {
            session_status() == PHP_SESSION_ACTIVE && Flash::instance()->addMessage(_('Please login first.'), 'danger');
            $f3->reroute('@login');
        }
        if ($error['code'] === 403) {
            session_status() == PHP_SESSION_ACTIVE && Flash::instance()->addMessage($error['text'] ?: _('You are not allowed to access this page.'), 'danger');
            $f3->reroute($f3->get('ERROR_REDIRECT') ?: '@home');
        }
        if ($error['code'] === 404) {
            session_status() == PHP_SESSION_ACTIVE && Flash::instance()->addMessage($error['text'] ?: _('This page doesn\'t exist.'), 'danger');
            $f3->reroute($f3->get('ERROR_REDIRECT') ?: '@home');
        }
        if ($error['code'] === 405) {
            session_status() == PHP_SESSION_ACTIVE && Flash::instance()->addMessage($error['text'] ?: _('Method not allowed.'), 'danger');
            $f3->reroute($f3->get('ERROR_REDIRECT') ?: '@home');
        }
        if ($error['code'] === 500 && !GK_DEBUG) {
            session_status() == PHP_SESSION_ACTIVE && Flash::instance()->addMessage(_('We are sorry, something unexpected happened.'), 'danger');
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
    if (strpos($f3->PATH, $path) === 0) {
        foreach (GK_SYSTEM_PATH_ALLOWED_IPS as $range) {
            if (empty($f3->get('IP')) or Range::parse($range)->contains(new IP($f3->get('IP')))) {
                // We have to enable session else, sessions files ends up in /tmp folder
                ini_set('session.gc_maxlifetime', 5);
                new Session(); // Use a different backend: cache
                $f3->run();
                exit;
            }
        }
        http_response_code(403);
        exit;
    }
}

// Register shutdown functions
include __DIR__.'/../app/shutdown.php';

ini_set('session.gc_probability', 0);
$session = new GeoKrety\Session($f3->get('DB_SESSION'));
// Create a per session based CSRF token
if (!$f3->exists('SESSION.csrf') or empty($f3->get('SESSION.csrf'))) {
    $f3->CSRF = $session->csrf();
    $f3->copy('CSRF', 'SESSION.csrf');
}
$f3->set('CURRENT_USER', $f3->get('SESSION.CURRENT_USER'));

// Assets building
Assets::instance();
Assets\Sass::instance()->init();

// Force HTTP_RETURN_CODE
if ($f3->exists('SESSION.HTTP_RETURN_CODE')) {
    http_response_code($f3->get('SESSION.HTTP_RETURN_CODE'));
    $f3->clear('SESSION.HTTP_RETURN_CODE');
}

$f3->run();
