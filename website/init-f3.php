<?php

require_once __DIR__.'/../vendor/autoload.php';
use GeoKrety\Email\CronError;
use IPTools\IP;
use IPTools\Range;

// Create GK_* consts from environments
GeoKrety\Service\Config::instance();
$f3 = Base::instance();

// Our dynamic routes will use it
$f3->route('POST @s3_file_uploaded: /s3/file-uploaded', '\GeoKrety\Controller\GeokretAvatarUploadWebhook->post');
$f3->route('HEAD @s3_file_uploaded: /s3/file-uploaded', function () {});
$f3->config(__DIR__.'/app/config.ini');
$f3->config(__DIR__.'/app/routes.ini');
$f3->config(__DIR__.'/app/routes-legacy.ini', true);
$f3->config(__DIR__.'/app/routes-api.ini');
$f3->config(__DIR__.'/app/cli.ini');
$f3->config(__DIR__.'/app/admin.ini');
$f3->config(__DIR__.'/app/authorizations.ini');

ini_set('user_agent', GK_SITE_USER_AGENT);

if (GK_DEVEL) {
    $f3->config('../app/devel.ini');
}

// OpAuth
if (GK_OPAUTH_ACTIVE) {
    $f3->config(__DIR__.'/app/opauth.ini', true);
    if (GK_OPAUTH_GOOGLE_CLIENT_ID) {
        $f3->config(__DIR__.'/app/opauth.google.ini', true);
    }
    if (GK_OPAUTH_FACEBOOK_CLIENT_ID) {
        $f3->config(__DIR__.'/app/opauth.facebook.ini', true);
    }
    if (GK_OPAUTH_GITHUB_CLIENT_ID) {
        $f3->config(__DIR__.'/app/opauth.github.ini', true);
    }

    $opAuth = OpauthBridge::instance($f3->opauth);
    $opAuth->onSuccess('\GeoKrety\Controller\Login->socialAuthSuccess');
    $opAuth->onAbort('\GeoKrety\Controller\Login->socialAuthAbort');
}

// // Falsum
// Falsum\Run::handler();

if (!is_null(GK_SENTRY_DSN)) {
    \Sentry\init(['dsn' => GK_SENTRY_DSN, 'environment' => GK_SENTRY_ENV, 'release' => GK_APP_VERSION]);
}

Prometheus\Storage\Redis::setDefaultOptions(
    [
        'host' => GK_REDIS_HOST,
        'port' => GK_REDIS_PORT,
        'password' => null,
        'timeout' => 0.1, // in seconds
        'read_timeout' => '10', // in seconds
        'persistent_connections' => false,
    ]
);

$f3->set('UI', GK_F3_UI);
$f3->set('TMP', GK_F3_TMP);
$f3->set('LOGS', GK_F3_LOGS);
$f3->set('CACHE', GK_F3_CACHE);
$f3->set('DEBUG', GK_F3_DEBUG);
if (GK_F3_DEBUG) {
    error_reporting(E_ALL);
}

// Language
$ml = Multilang::instance();
Carbon\Carbon::setLocale($ml->current);
Carbon\CarbonInterval::setLocale($ml->current);
setlocale(LC_NUMERIC, 'en_US.UTF-8');
bindtextdomain('messages', GK_GETTEXT_BINDTEXTDOMAIN_PATH);
bind_textdomain_codeset('messages', 'UTF-8');
textdomain('messages');

include __DIR__.'/app/validators.php';
include __DIR__.'/app/events.php';

if (!$f3->exists('DB')) {
    $f3->set('DB', new DB\SQL(GK_DB_DSN, GK_DB_USER, GK_DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;', PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]));
}
if (!$f3->exists('DB_SESSION')) {
    $f3->set('DB_SESSION', new DB\SQL(GK_DB_DSN, GK_DB_USER, GK_DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;', PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]));
}

$f3->config(__DIR__.'/app/assets.ini');

// Assets building
Assets::instance();
Assets\Sass::instance()->init();

include __DIR__.'/app/middleware.php';
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
include __DIR__.'/app/shutdown.php';

ini_set('session.gc_probability', 0);
$session = new GeoKrety\Session($f3->get('DB_SESSION'));
// Create a per session based CSRF token
if (!$f3->exists('SESSION.csrf') or empty($f3->get('SESSION.csrf'))) {
    $f3->CSRF = $session->csrf();
    $f3->copy('CSRF', 'SESSION.csrf');
}
$f3->set('CURRENT_USER', $f3->get('SESSION.CURRENT_USER'));

// Force HTTP_RETURN_CODE
if ($f3->exists('SESSION.HTTP_RETURN_CODE')) {
    http_response_code($f3->get('SESSION.HTTP_RETURN_CODE'));
    $f3->clear('SESSION.HTTP_RETURN_CODE');
}
