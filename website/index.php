<?php

require 'vendor/autoload.php';
$f3 = \Base::instance();
$f3->config('app/config.ini');
$f3->config('app/routes.ini');
$f3->config('app/authorizations.ini');

// Create GK_* consts from environments
new \GeoKrety\Service\Config();

$f3->set('UI', GK_F3_UI);
$f3->set('TMP', GK_F3_TMP);
$f3->set('CACHE', GK_F3_CACHE);
$f3->set('DEBUG', GK_F3_DEBUG);

// Start Session
new Session();

// // Falsum
// Falsum\Run::handler();

// Healthcheck route
$f3->route('HEAD /', function () {});

$f3->route('GET @move: /move-geokrety/',
    function () {
        \GeoKrety\Service\Smarty::render('pages/move.tpl');
    }
);

// Authorizations
$access = \Access::instance();
$access->authorize($f3->get('SESSION.user.group'));

// Initialize the validator with custom rules
$validator = \Validation::instance();
$validator->onError(function ($text, $key) {
    \Flash::instance()->addMessage($text, 'danger');
});
$validator->addValidator('not_empty', function ($field, $input, $param = null) { return \GeoKrety\Validation\Base::isNotEmpty($input[$field]); }, 'The {0} field cannot be empty');
$validator->addValidator('geokrety_type', function ($field, $input, $param = null) { return \GeoKrety\GeokretyType::isValid($input[$field]); }, 'The GeoKret type is invalid');
$validator->addValidator('log_type', function ($field, $input, $param = null) { return \GeoKrety\LogType::isValid($input[$field]); }, 'The move type is invalid');
$validator->addValidator('language_supported', function ($field, $input, $param = null) { return \GeoKrety\Service\LanguageService::isLanguageSupported($input[$field]); }, 'This language is not supported');
$validator->addValidator('ciphered_password', function ($field, $input, $param = null) { return substr($input[$field], 0, 7) === '$2a$11$'; }, 'The password must be ciphered');
$validator->addFilter('HTMLPurifier', function ($value, $params = null) { return \GeoKrety\Service\HTMLPurifier::getPurifier()->purify($value); });
$validator->loadLang();

// Listen Events
$events = \Event::instance();
// $events->on('user.login', function (\GeoKrety\Model\User $user) {});
// $events->on('user.logout', function (\GeoKrety\Model\User $user) {});
// $events->on('user.language.changed', function (\GeoKrety\Model\User $user, ?string $oldLanguage) {});
// $events->on('user.home_location.changed', function (\GeoKrety\Model\User $user) {});
// $events->on('user.email.change', function (\GeoKrety\Model\EmailActivation $activation) {});
// $events->on('user.email.changed', function (\GeoKrety\Model\EmailActivation $activation) {});
// $events->on('user.password.changed', function (\GeoKrety\Model\User $user) {});
// $events->on('user.secid.changed', function (\GeoKrety\Model\User $user) {});
// $events->on('user.statpic.template.changed', function (\GeoKrety\Model\User $user) {});
// $events->on('news.subscribed', function (\GeoKrety\Model\News $news, \GeoKrety\Model\User $user) {});
// $events->on('news.unsubscribed', function (\GeoKrety\Model\News $news, \GeoKrety\Model\User $user) {});
// $events->on('news-comment.created', function (\GeoKrety\Model\NewsComment $comment) {});
// $events->on('news-comment.deleted', function (\GeoKrety\Model\NewsComment $comment) {});
// $events->on('move-comment.created', function (\GeoKrety\Model\MoveComment $comment) {});
// $events->on('move-comment.deleted', function (\GeoKrety\Model\MoveComment $comment) {});
// $events->on('geokret.created', function (\GeoKrety\Model\Geokret $geokret) {});
// $events->on('geokret.updated', function (\GeoKrety\Model\Geokret $geokret) {});
// $events->on('geokret.claimed', function (\GeoKrety\Model\Geokret $geokret, ?\GeoKrety\Model\User $oldUser, \GeoKrety\Model\User $newUser) {});
// $events->on('contact.new', function (\GeoKrety\Model\Mail $mail) {});
$events->on('user.statpic.template.changed', function (\GeoKrety\Model\User $user) {
    \GeoKrety\Service\UserBannerGenerator::generate($user);
});

$f3->run();
