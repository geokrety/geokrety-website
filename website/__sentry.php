<?php

require_once 'templates/konfig.php';

if (defined('SENTRY_DSN')) {
    require_once 'templates/sentry-php-master/lib/Raven/Autoloader.php';
    Raven_Autoloader::register();
    $client = new Raven_Client(SENTRY_DSN);
    $client->tags_context(array(
      'php_version' => phpversion(),
    ));
    $client->setEnvironment(SENTRY_ENV ?: 'unknown');
    $error_handler = new Raven_ErrorHandler($client);
    $error_handler->registerExceptionHandler();
    $error_handler->registerErrorHandler();
    $error_handler->registerShutdownFunction();
}

$vendorDir = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'vendor'));
include_once $vendorDir.DIRECTORY_SEPARATOR.'autoload.php';

// Start session
if (SESSION_IN_REDIS) {
    ini_set('session.save_handler', 'redis');
    ini_set('session.save_path', REDIS_DSN);
}
session_start();
