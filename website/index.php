<?php

require 'vendor/autoload.php';
$f3 = \Base::instance();
$f3->set('AUTOLOAD', 'app/');

// Create GK_* consts from environments
new \GeoKrety\Service\Config();

$f3->set('TMP', GK_F3_TMP);
$f3->set('CACHE', GK_F3_CACHE);
new Session();
// $f3->get('SESSION.IS_LOGGED_IN');
// $f3->get('SESSION.CURRENT_USER');

$f3->route('HEAD /', function () {});

$f3->route('GET @home: /', '\GeoKrety\Controller\Home->get');

$f3->route('GET @login: /login [sync]', '\GeoKrety\Controller\Login->loginForm');
// $f3->route('GET @login: /login [ajax]', '\GeoKrety\Controller\Login->loginFormFragment');
$f3->route('POST @login: /login [sync]', '\GeoKrety\Controller\Login->login');
// $f3->route('POST @login: /login [ajax]', '\GeoKrety\Controller\Login->loginFragment');

$f3->route('GET @logout: /logout [sync]', '\GeoKrety\Controller\Login->logout');

$f3->map('@news_details: /news/@newsid', '\GeoKrety\Controller\NewsDetails');
$f3->route(array(
        'GET @news_list: /news',
        'GET @news_list_paginate: /news/page/@page',
    ), '\GeoKrety\Controller\NewsList->get');

$f3->map('@news_comment_delete: /news-comment/@newscommentid/delete', '\GeoKrety\Controller\NewsCommentDelete');

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
if (GK_DEBUG) {
    echo $f3->get('DB')->log();
}
