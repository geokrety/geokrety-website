<?php

function audit(string $event, $newObjectModel) {
    $log = new \GeoKrety\Model\AuditLog();
    $log->event = $event;
    $log->context = json_encode($newObjectModel);
    $log->save();
}

// Listen Events
$events = \Sugar\Event::instance();
$events->on('user.created', function (GeoKrety\Model\User $user) {
    \GeoKrety\Service\UserBanner::generate($user);
    audit('user.created', $user);
});
$events->on('user.activated', function (GeoKrety\Model\User $user) { audit('user.activated', $user); });
$events->on('user.deleted', function (GeoKrety\Model\User $user) { audit('user.deleted', $user->id); });
$events->on('activation.token.created', function (GeoKrety\Model\AccountActivationToken $token) { audit('activation.token.created', $token->user); });
$events->on('activation.token.used', function (GeoKrety\Model\AccountActivationToken $token) { audit('activation.token.used', $token); });
$events->on('user.login.password', function (GeoKrety\Model\User $user) { audit('user.login.password', $user); });
$events->on('user.login.secid', function (GeoKrety\Model\User $user) { audit('user.login.secid', $user); });
$events->on('user.login.oauth', function (GeoKrety\Model\User $user) { audit('user.login.oauth', $user); });
$events->on('user.login.devel', function (GeoKrety\Model\User $user) { audit('user.login.devel', $user); });
$events->on('user.login.registration.oauth', function (GeoKrety\Model\User $user) { audit('user.login.registration.oauth', $user); });
$events->on('user.login.registration.email', function (GeoKrety\Model\User $user) { audit('user.login.registration.email', $user); });
$events->on('user.logout', function (GeoKrety\Model\User $user) { audit('user.logout', $user); });
$events->on('user.language.changed', function (GeoKrety\Model\User $user, $context) { audit('user.language.changed', ['language' => $user->language, 'old_language' => $context]); });  // context => $oldLanguage
$events->on('user.home_location.changed', function (GeoKrety\Model\User $user) { audit('user.created', $user); });
$events->on('user.oauth.attach', function (GeoKrety\Model\UserSocialAuth $userSocialAuth) { audit('user.oauth.attach', $userSocialAuth); });
$events->on('user.oauth.detach', function (GeoKrety\Model\UserSocialAuth $userSocialAuth) { audit('user.oauth.detach', $userSocialAuth); });

$events->on('user.email.change', function (GeoKrety\Model\User $user) { audit('user.email.change', $user); });
$events->on('user.email.changed', function (GeoKrety\Model\User $user) { audit('user.email.changed', $user); });
$events->on('email.token.generated', function (GeoKrety\Model\EmailActivationToken $token) { audit('email.token.generated', $token); });
$events->on('email.token.used', function (GeoKrety\Model\EmailActivationToken $token) { audit('email.token.used', $token); });
$events->on('user.secid.changed', function (GeoKrety\Model\User $user) { audit('user.secid.changed', $user); });
$events->on('user.password.changed', function (GeoKrety\Model\User $user) { audit('user.password.changed', $user); });
$events->on('password.token.generated', function (GeoKrety\Model\PasswordToken $token) { audit('password.token.generated', $token); });
$events->on('password.token.used', function (GeoKrety\Model\PasswordToken $token) { audit('password.token.used', $token); });
$events->on('news.subscribed', function (GeoKrety\Model\News $news) { audit('news.subscribed', $news); });
$events->on('news.unsubscribed', function (GeoKrety\Model\News $news) { audit('news.unsubscribed', $news); });
$events->on('news-comment.created', function (GeoKrety\Model\NewsComment $comment) { audit('news-comment.created', $comment); });
$events->on('news-comment.deleted', function (GeoKrety\Model\NewsComment $comment) { audit('news-comment.deleted', $comment); });
$events->on('move.created', function (GeoKrety\Model\Move $move) { audit('move.created', $move); });
$events->on('move.updated', function (GeoKrety\Model\Move $move) { audit('move.updated', $move); });
$events->on('move.deleted', function (GeoKrety\Model\Move $move) { audit('move.deleted', $move); });
$events->on('move-comment.created', function (GeoKrety\Model\MoveComment $comment) { audit('move-comment.created', $comment); });
$events->on('move-comment.deleted', function (GeoKrety\Model\MoveComment $comment) { audit('move-comment.deleted', $comment); });
$events->on('geokret.avatar.presigned_request', function (GeoKrety\Model\Picture $picture, $context) { audit('geokret.avatar.presigned_request', $picture); });
$events->on('picture.uploaded', function (GeoKrety\Model\Picture $picture) { audit('picture.uploaded', $picture); });
$events->on('picture.caption.saved', function (GeoKrety\Model\Picture $picture) { audit('picture.caption.saved', $picture); });
$events->on('picture.deleted', function (GeoKrety\Model\Picture $picture) { audit('picture.deleted', $picture); });
$events->on('picture.avatar.defined', function (GeoKrety\Model\Picture $picture) { audit('picture.avatar.defined', $picture); });
$events->on('contact.new', function (GeoKrety\Model\Mail $mail) { audit('contact.new', $mail); });
$events->on('geokret.created', function (GeoKrety\Model\Geokret $geokret) {
    if (!is_null($geokret->owner)) {
        \GeoKrety\Service\UserBanner::generate($geokret->owner);
    }
    audit('geokret.created', $geokret);
});
$events->on('geokret.updated', function (GeoKrety\Model\Geokret $geokret) {
    if (!is_null($geokret->owner) && $geokret->changed('owner')) {
        \GeoKrety\Service\UserBanner::generate($geokret->owner);
    }
    audit('geokret.updated', $geokret);
});
$events->on('geokret.deleted', function (GeoKrety\Model\Geokret $geokret) {
    if (!is_null($geokret->owner)) {
        \GeoKrety\Service\UserBanner::generate($geokret->owner);
    }
    audit('geokret.deleted', $geokret);
});
$events->on('geokret.owner_code.created', function (GeoKrety\Model\OwnerCode $ownerCode) {
    audit('geokret.owner_code.created', $ownerCode);
});
$events->on('geokret.claimed', function (GeoKrety\Model\Geokret $geokret, $context) {  // context => $oldUser, $newUser
    \GeoKrety\Service\UserBanner::generate($context['newUser']);
    if (!is_null($context['oldUser'])) {
        \GeoKrety\Service\UserBanner::generate($context['oldUser']);
    }
    audit('geokret.claimed', $geokret); // TODO: context
});
$events->on('user.statpic.template.changed', function (GeoKrety\Model\User $user) {
    \GeoKrety\Service\UserBanner::generate($user);
    audit('user.statpic.template.changed', $user);
});
