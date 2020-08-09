<?php

require '../init-f3.php';
$f3->config('../app/cron.ini');
$f3->config('../app/assets.ini');

ini_set('session.gc_maxlifetime', GK_SITE_SESSION_REMEMBER);
new \GeoKrety\Session($f3->get('DB'));
//// Split DB connexion for session to prevent session rollback
//$dbSession = new \DB\SQL(GK_DB_DSN, GK_DB_USER, GK_DB_PASSWORD, [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;']);
//new \GeoKrety\Session($dbSession);

// Authorizations
$access = \Access::instance();
$access->authorize($f3->get('SESSION.user.group'));

Cron::instance();
\Assets::instance();
\Assets\Sass::instance()->init();

$f3->run();
