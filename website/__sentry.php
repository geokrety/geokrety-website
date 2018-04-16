<?php

require 'templates/konfig.php';

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
