<?php

// Listen Events
$events = \Event::instance();
// $events->on('user.login', function (\GeoKrety\Model\User $user) {});
// $events->on('user.logout', function (\GeoKrety\Model\User $user) {});
// $events->on('user.language.changed', function (\GeoKrety\Model\User $user, $context) {});  // context => $oldLanguage
// $events->on('user.home_location.changed', function (\GeoKrety\Model\User $user) {});
// $events->on('user.email.change', function (\GeoKrety\Model\EmailActivation $activation) {});
// $events->on('user.email.changed', function (\GeoKrety\Model\EmailActivation $activation) {});
// $events->on('email.token.generated', function (\GeoKrety\Model\EmailActivation $token) {});
// $events->on('email.token.used', function (\GeoKrety\Model\EmailActivation $token) {});
// $events->on('user.secid.changed', function (\GeoKrety\Model\User $user) {});
// $events->on('user.statpic.template.changed', function (\GeoKrety\Model\User $user) {});
// $events->on('user.password.changed', function (\GeoKrety\Model\User $user) {});
// $events->on('password.token.generated', function (\GeoKrety\Model\PasswordToken $token) {});
// $events->on('password.token.used', function (\GeoKrety\Model\PasswordToken $token) {});
// $events->on('news.subscribed', function (\GeoKrety\Model\News $news) {});
// $events->on('news.unsubscribed', function (\GeoKrety\Model\News $news) {});
// $events->on('news-comment.created', function (\GeoKrety\Model\NewsComment $comment) {});
// $events->on('news-comment.deleted', function (\GeoKrety\Model\NewsComment $comment) {});
// $events->on('move.created', function (\GeoKrety\Model\Move $move) {});
// $events->on('move.updated', function (\GeoKrety\Model\Move $move) {});
// $events->on('move.deleted', function (\GeoKrety\Model\Move $move) {});
// $events->on('move-comment.created', function (\GeoKrety\Model\MoveComment $comment) {});
// $events->on('move-comment.deleted', function (\GeoKrety\Model\MoveComment $comment) {});
// $events->on('contact.new', function (\GeoKrety\Model\Mail $mail) {});
// $events->on('geokret.created', function (\GeoKrety\Model\Geokret $geokret) {});
// $events->on('geokret.updated', function (\GeoKrety\Model\Geokret $geokret) {});
// $events->on('geokret.owner_code.created', function (\GeoKrety\Model\OwnerCode $ownerCode) {});
$events->on('geokret.claimed', function (\GeoKrety\Model\Geokret $geokret, $context) {  // context => $oldUser, $newUser
    \GeoKrety\Service\UserBannerGenerator::generate($context['newUser']);
    if (!is_null($context['oldUser'])) {
        \GeoKrety\Service\UserBannerGenerator::generate($context['oldUser']);
    }
});
$events->on('user.statpic.template.changed', function (\GeoKrety\Model\User $user) {
    \GeoKrety\Service\UserBannerGenerator::generate($user);
});
