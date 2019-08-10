<?php

require 'vendor/autoload.php';
$f3 = \Base::instance();
$f3->set('AUTOLOAD', 'app/');

// Create GK_* consts from environments
new \GeoKrety\Service\Config();

$f3->set('TMP', GK_F3_TMP);
$f3->set('CACHE', GK_F3_CACHE);
new Session();

$f3->route('HEAD /', function () {});
$f3->route('GET @debug_env: /env', '\GeoKrety\Service\Config::printEnvironements');

$f3->route('GET @home: /', '\GeoKrety\Controller\Home->get');

$f3->route('GET @news_list: /news', '\GeoKrety\Controller\NewsList->get');
$f3->map('@news_details: /news/@newsid', '\GeoKrety\Controller\NewsDetails');

$f3->route('GET @news_subscription: /news/@newsid/subscribe [sync]', '\GeoKrety\Controller\NewsSubscription->subscription');
$f3->route('GET @news_subscription: /news/@newsid/subscribe [ajax]', '\GeoKrety\Controller\NewsSubscription->subscriptionFragment');
$f3->route('POST @news_subscription: /news/@newsid/subscribe', '\GeoKrety\Controller\NewsSubscription->subscriptionToggle');

$f3->route('GET @user: /users/@userid', '\GeoKrety\Controller\Home->get');

$f3->route('GET @move: /move-geokrety/',
    function () {
        \GeoKrety\Service\Smarty::render('pages/move.tpl');
    }
);

$f3->run();
