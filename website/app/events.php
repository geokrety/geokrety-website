<?php

function audit(string $event, $newObjectModel, $oldObjectModel=null) {
    $log = new \GeoKrety\Model\AuditLog();
    $log->event = $event;
    $log->context = json_encode($newObjectModel);
    if (!is_null($oldObjectModel)) {
        $log->new_value = json_encode($oldObjectModel);
    }
    $log->save();
};

// Listen Events
$events = \Event::instance();
$events->on('user.created', function (\GeoKrety\Model\User $user) { audit('user.created', $user); });
$events->on('user.activated', function (\GeoKrety\Model\User $user) { audit('user.activated', $user); });
$events->on('user.destroyed', function (\GeoKrety\Model\User $user) { audit('user.destroyed', $user->id); });
$events->on('activation.token.created', function (\GeoKrety\Model\AccountActivationToken $token) { audit('activation.token.generated', $token->user); });
$events->on('activation.token.used', function (\GeoKrety\Model\AccountActivationToken $token) { audit('activation.token.used', $token); });
$events->on('user.login', function (\GeoKrety\Model\User $user) { audit('user.login', $user); });
$events->on('user.logout', function (\GeoKrety\Model\User $user) { audit('user.logout', $user); });
$events->on('user.language.changed', function (\GeoKrety\Model\User $user, $context) { audit('user.language.changed', ['language' => $user->language, 'old_language' => $context]); });  // context => $oldLanguage
$events->on('user.home_location.changed', function (\GeoKrety\Model\User $user) { audit('user.created', $user); });

$events->on('user.email.change', function (\GeoKrety\Model\EmailActivation $token) { audit('user.email.change', $token); });
$events->on('user.email.changed', function (\GeoKrety\Model\EmailActivation $token) { audit('user.email.changed', $user); });
$events->on('email.token.generated', function (\GeoKrety\Model\EmailActivation $token) { audit('email.token.generated', $token); });
$events->on('email.token.used', function (\GeoKrety\Model\EmailActivation $token) { audit('email.token.used', $token); });
$events->on('user.secid.changed', function (\GeoKrety\Model\User $user) { audit('user.secid.changed', $user); });
$events->on('user.statpic.template.changed', function (\GeoKrety\Model\User $user) { audit('user.statpic.template.changed', $user); });
$events->on('user.password.changed', function (\GeoKrety\Model\User $user) { audit('user.password.changed', $user); });
$events->on('password.token.generated', function (\GeoKrety\Model\PasswordToken $token) { audit('password.token.generated', $token); });
$events->on('password.token.used', function (\GeoKrety\Model\PasswordToken $token) { audit('password.token.used', $token); });
$events->on('news.subscribed', function (\GeoKrety\Model\News $news) { audit('news.subscribed', $news); });
$events->on('news.unsubscribed', function (\GeoKrety\Model\News $news) { audit('news.unsubscribed', $news); });
$events->on('news-comment.created', function (\GeoKrety\Model\NewsComment $comment) { audit('news-comment.created', $comment); });
$events->on('news-comment.deleted', function (\GeoKrety\Model\NewsComment $comment) { audit('news-comment.deleted', $comment); });
$events->on('move.created', function (\GeoKrety\Model\Move $move) { audit('move.created', $move); });
$events->on('move.updated', function (\GeoKrety\Model\Move $move) { audit('move.updated', $move); });
$events->on('move.deleted', function (\GeoKrety\Model\Move $move) { audit('move.deleted', $move); });
$events->on('move-comment.created', function (\GeoKrety\Model\MoveComment $comment) { audit('move-comment.created', $comment); });
$events->on('move-comment.deleted', function (\GeoKrety\Model\MoveComment $comment) { audit('move-comment.deleted', $comment); });
$events->on('geokret.avatar.presigned_request', function (\GeoKrety\Model\Picture $picture, $context) { audit('geokret.avatar.presigned_request', $picture); });
$events->on('picture.uploaded', function (\GeoKrety\Model\Picture $picture) { audit('picture.uploaded', $picture); });
$events->on('picture.caption.saved', function (\GeoKrety\Model\Picture $picture) { audit('picture.caption.saved', $picture); });
$events->on('picture.deleted', function (\GeoKrety\Model\Picture $picture) { audit('picture.deleted', $picture); });
$events->on('picture.avatar.defined', function (\GeoKrety\Model\Picture $picture) { audit('picture.avatar.defined', $picture); });
$events->on('contact.new', function (\GeoKrety\Model\Mail $mail) { audit('contact.new', $mail); });
$events->on('geokret.created', function (\GeoKrety\Model\Geokret $geokret) {
    \GeoKrety\Service\UserBanner::generate($geokret->owner);
    audit('geokret.created', $geokret);
});  // context => s3 response
$events->on('geokret.updated', function (\GeoKrety\Model\Geokret $geokret) {
    \GeoKrety\Service\UserBanner::generate($geokret->owner);
    audit('geokret.updated', $geokret);
});
$events->on('geokret.owner_code.created', function (\GeoKrety\Model\OwnerCode $ownerCode) {
    audit('geokret.owner_code.created', $ownerCode);
});
$events->on('geokret.claimed', function (\GeoKrety\Model\Geokret $geokret, $context) {  // context => $oldUser, $newUser
    \GeoKrety\Service\UserBanner::generate($context['newUser']);
    if (!is_null($context['oldUser'])) {
        \GeoKrety\Service\UserBanner::generate($context['oldUser']);
    }
    audit('geokret.claimed', $geokret); // TODO: context
});
$events->on('user.statpic.template.changed', function (\GeoKrety\Model\User $user) {
    \GeoKrety\Service\UserBanner::generate($user);
    audit('user.statpic.template.changed', $user);
});
$events->on('picture.uploaded', function (\GeoKrety\Model\Picture $picture) {
    if (!is_null($picture->move)) {
        ++$picture->move->pictures_count;
        $picture->move->save();
    }
    if (!is_null($picture->geokret)) {
        ++$picture->geokret->pictures_count;
        $picture->geokret->save();
    }
    if (!is_null($picture->user)) {
        ++$picture->user->pictures_count;
        $picture->user->save();
    }
    audit('picture.uploaded', $picture);
});
$events->on('picture.deleted', function (\GeoKrety\Model\Picture $picture) {
    if (!is_null($picture->move)) {
        --$picture->move->pictures_count;
        $picture->move->save();
    }
    if (!is_null($picture->geokret)) {
        --$picture->geokret->pictures_count;
        $picture->geokret->save();
    }
    if (!is_null($picture->user)) {
        --$picture->user->pictures_count;
        $picture->user->save();
    }
    audit('picture.deleted', $picture);
});
