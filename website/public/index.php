<?php

use GeoKrety\Email\CronError;
use GeoKrety\Model\User;

require '../init-f3.php';
$f3->config('../app/assets.ini');

include __DIR__.'/../app/middleware.php';

// Start session on all route except those below
ini_set('session.gc_maxlifetime', GK_SITE_SESSION_REMEMBER);
new \GeoKrety\Session($f3->get('DB'));
//// Split DB connexion for session to prevent session lose on rollback
//$dbSession = new \DB\SQL(GK_DB_DSN, GK_DB_USER, GK_DB_PASSWORD, [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;']);
//new \GeoKrety\Session($dbSession);

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

// Authorizations
$access = \Access::instance();
$access->authorize($f3->get('SESSION.user.group'));

\Assets::instance();
\Assets\Sass::instance()->init();

$f3->run();
