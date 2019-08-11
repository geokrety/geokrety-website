<?php

require 'vendor/autoload.php';
$f3 = \Base::instance();
$f3->config('app/config.ini');

// Create GK_* consts from environments
new \GeoKrety\Service\Config();

$f3->set('TMP', GK_F3_TMP);
$f3->set('CACHE', GK_F3_CACHE);
$f3->set('DEBUG', GK_F3_DEBUG);

// Start Session
new Session();

// Falsum
Falsum\Run::handler();

// Routes
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

$f3->map('@geokret_details: /geokrety/@gkid', '\GeoKrety\Controller\GeokretDetails');
$f3->map('@geokret_create: /geokrety/create', '\GeoKrety\Controller\GeokretCreate');
$f3->map('@geokret_edit: /geokrety/@gkid/edit', '\GeoKrety\Controller\GeokretEdit');
$f3->route('GET @geokret_label_generator: /geokrety/@gkid/label', '\GeoKrety\Controller\GeokretDetails->get');

$f3->route('GET @move: /move-geokrety/',
    function () {
        \GeoKrety\Service\Smarty::render('pages/move.tpl');
    }
);

$f3->map('@help_api: /help/api', '\GeoKrety\Controller\HelpApi');

// Authorizations
$access = \Access::instance();
$access->policy('allow');
$access->deny('@login', \GeoKrety\AuthGroup::AUTH_GROUP_AUTHENTICATED);
$access->deny('@geokret_create');
$access->deny('@geokret_edit');
$access->allow('@geokret_create', \GeoKrety\AuthGroup::AUTH_GROUP_AUTHENTICATED);
$access->allow('@geokret_edit', \GeoKrety\AuthGroup::AUTH_GROUP_AUTHENTICATED);
$access->authorize($f3->get('SESSION.user.group'));

// Initialize the validator with custom
$validator = \Validation::instance();
$validator->onError(function ($text, $key) {
    \Flash::instance()->addMessage($text, 'danger');
});
$validator->addValidator('not_empty', function ($field, $input, $param = null) {return \GeoKrety\Validation\Base::isNotEmpty($input[$field]); }, 'The {0} field cannot be empty');
$validator->addValidator('geokrety_type', function ($field, $input, $param = null) {return \GeoKrety\GeokretyType::isValid($input[$field]); }, 'The GeoKret type is invalid');
$validator->addValidator('log_type', function($value,$params=NULL) {return \GeoKrety\LogType::isValid($input[$field]);}, 'The move type is invalid');
$validator->addFilter('HTMLPurifier', function ($value, $params = null) {return \GeoKrety\Service\HTMLPurifier::getPurifier()->purify($value); });
$validator->loadLang();

$f3->run();
