<?php

use GeoKrety\Service\Metrics;

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
    Metrics::counter('users_total', 'Total number of accounts', ['verb'], ['created']);
});
$events->on('user.activated', function (GeoKrety\Model\User $user) {
    audit('user.activated', $user);
    Metrics::counter('users_total', 'Total number of accounts', ['verb'], ['activated']);
});
$events->on('user.deleted', function (GeoKrety\Model\User $user) {
    audit('user.deleted', $user->id);
    Metrics::counter('users_total', 'Total number of accounts', ['verb'], ['deleted']);
});
$events->on('activation.token.created', function (GeoKrety\Model\AccountActivationToken $token) {
    audit('activation.token.created', $token->user);
    Metrics::counter('activation_token_total', 'Total number of account activation token', ['verb'], ['created']);
});
$events->on('activation.token.used', function (GeoKrety\Model\AccountActivationToken $token) {
    audit('activation.token.used', $token);
    Metrics::counter('activation_token_total', 'Total number of account activation token', ['verb'], ['used']);
});
$events->on('user.login.password', function (GeoKrety\Model\User $user) {
    audit('user.login.password', $user);
    Metrics::counter('logged_in_users_total', 'Total number of connections', ['type'], ['password']);
});
$events->on('user.login.secid', function (GeoKrety\Model\User $user) {
    audit('user.login.secid', $user);
    Metrics::counter('logged_in_users_total', 'Total number of connections', ['type'], ['secid']);
});
$events->on('user.login.oauth', function (GeoKrety\Model\User $user) {
    audit('user.login.oauth', $user);
    Metrics::counter('logged_in_users_total', 'Total number of connections', ['type'], ['oauth']);
});
$events->on('user.login.devel', function (GeoKrety\Model\User $user) {
    audit('user.login.devel', $user);
    Metrics::counter('logged_in_users_total', 'Total number of connections', ['type'], ['devel']);
});
$events->on('user.login.registration.oauth', function (GeoKrety\Model\User $user) {
    audit('user.login.registration.oauth', $user);
    Metrics::counter('registration_type_total', 'Total number of registration types', ['type'], ['oauth']);
});
$events->on('user.login.registration.email', function (GeoKrety\Model\User $user) {
    audit('user.login.registration.email', $user);
    Metrics::counter('registration_type_total', 'Total number of registration types', ['type'], ['email']);
});
$events->on('user.oauth.attach', function (GeoKrety\Model\UserSocialAuth $userSocialAuth) {
    audit('user.oauth.attach', $userSocialAuth);
    Metrics::counter('oauth_association_total', 'Total number of oauth associations', ['type'], ['attach']);
});
$events->on('user.oauth.detach', function (GeoKrety\Model\UserSocialAuth $userSocialAuth) {
    audit('user.oauth.detach', $userSocialAuth);
    Metrics::counter('oauth_association_total', 'Total number of oauth associations', ['type'], ['detach']);
});
$events->on('user.logout', function (GeoKrety\Model\User $user) {
    audit('user.logout', $user);
    Metrics::counter('explicit_logout_total', 'Total number of explicit disconnections');
});
$events->on('user.language.changed', function (GeoKrety\Model\User $user, $context) {
    audit('user.language.changed', ['language' => $user->language, 'old_language' => $context]);
    Metrics::counter('preference_change_total', 'Total number of preference change', ['type'], ['language']);
});  // context => $oldLanguage
$events->on('user.home_location.changed', function (GeoKrety\Model\User $user) {
    audit('user.created', $user);
    Metrics::counter('preference_change_total', 'Total number of preference change', ['type'], ['home_location']);
});

$events->on('user.email.change', function (GeoKrety\Model\User $user) {
    audit('user.email.change', $user);
    Metrics::counter('user_email_change_requests_total', 'Total number of user email change requests');
});
$events->on('user.email.changed', function (GeoKrety\Model\User $user) {
    audit('user.email.changed', $user);
    Metrics::counter('user_email_changed_total', 'Total number of user email changed');
});
$events->on('email.token.generated', function (GeoKrety\Model\EmailActivationToken $token) {
    audit('email.token.generated', $token);
    Metrics::counter('email_validation_token_created_total', 'Total number of email validation token created');
});
$events->on('email.token.used', function (GeoKrety\Model\EmailActivationToken $token) {
    audit('email.token.used', $token);
    Metrics::counter('email_validation_token_used_total', 'Total number of email validation token used');
});
$events->on('user.secid.changed', function (GeoKrety\Model\User $user) {
    audit('user.secid.changed', $user);
    Metrics::counter('secid_generated_total', 'Total number of secid generated');
});
$events->on('user.password.changed', function (GeoKrety\Model\User $user) {
    audit('user.password.changed', $user);
    Metrics::counter('user_password_changed_total', 'Total number of password changed');
});
$events->on('password.token.generated', function (GeoKrety\Model\PasswordToken $token) {
    audit('password.token.generated', $token);
    Metrics::counter('password_token_created_total', 'Total number of password token generated');
});
$events->on('password.token.used', function (GeoKrety\Model\PasswordToken $token) {
    audit('password.token.used', $token);
    Metrics::counter('password_token_used_total', 'Total number of password token used');
});
$events->on('news.subscribed', function (GeoKrety\Model\News $news) {
    audit('news.subscribed', $news);
    Metrics::counter('news_subscribed_total', 'Total number of news subscription');
});
$events->on('news.unsubscribed', function (GeoKrety\Model\News $news) {
    audit('news.unsubscribed', $news);
    Metrics::counter('news_unsubscribed_total', 'Total number of news unsubscription');
});
$events->on('news-comment.created', function (GeoKrety\Model\NewsComment $comment) {
    audit('news-comment.created', $comment);
    Metrics::counter('news_comment_created_total', 'Total number of news comment created');
});
$events->on('news-comment.deleted', function (GeoKrety\Model\NewsComment $comment) {
    audit('news-comment.deleted', $comment);
    Metrics::counter('news_comment_deleted_total', 'Total number of news comment deleted');
});
$events->on('move.created', function (GeoKrety\Model\Move $move) {
    audit('move.created', $move);
    Metrics::counter('move_created_total', 'Total number of move created');
});
$events->on('move.updated', function (GeoKrety\Model\Move $move) {
    audit('move.updated', $move);
    Metrics::counter('move_updated_total', 'Total number of move updated');
});
$events->on('move.deleted', function (GeoKrety\Model\Move $move) {
    audit('move.deleted', $move);
    Metrics::counter('move_deleted_total', 'Total number of move deleted');
});
$events->on('move-comment.created', function (GeoKrety\Model\MoveComment $comment) {
    audit('move-comment.created', $comment);
    Metrics::counter('move_comment_created_total', 'Total number of move comment created');
});
$events->on('move-comment.deleted', function (GeoKrety\Model\MoveComment $comment) {
    audit('move-comment.deleted', $comment);
    Metrics::counter('move_comment_deleted_total', 'Total number of move comment deleted');
});
$events->on('geokret.avatar.presigned_request', function (GeoKrety\Model\Picture $picture, $context) {
    audit('geokret.avatar.presigned_request', $picture);
    Metrics::counter('geokret_avatar_presigned_request_total', 'Total number of geokret avatar upload presigned request');
});
$events->on('picture.uploaded', function (GeoKrety\Model\Picture $picture) {
    audit('picture.uploaded', $picture);
    Metrics::counter('picture_uploaded_total', 'Total number of pictures uploaded');
});
$events->on('picture.caption.saved', function (GeoKrety\Model\Picture $picture) {
    audit('picture.caption.saved', $picture);
    Metrics::counter('picture_caption_saved_total', 'Total number of pictures caption saved');
});
$events->on('picture.deleted', function (GeoKrety\Model\Picture $picture) {
    audit('picture.deleted', $picture);
    Metrics::counter('picture_deleted_total', 'Total number of pictures deleted');
});
$events->on('picture.avatar.defined', function (GeoKrety\Model\Picture $picture) {
    audit('picture.avatar.defined', $picture);
    Metrics::counter('picture_avatar_assign_total', 'Total number of pictures assigned as avatar');
});
$events->on('contact.new', function (GeoKrety\Model\Mail $mail) {
    audit('contact.new', $mail);
    Metrics::counter('private_message_sent_total', 'Total number of private message sent');
});
$events->on('geokret.created', function (GeoKrety\Model\Geokret $geokret) {
    if (!is_null($geokret->owner)) {
        \GeoKrety\Service\UserBanner::generate($geokret->owner);
    }
    audit('geokret.created', $geokret);
    Metrics::counter('geokrety_created_total', 'Total number of GeoKrety created');
});
$events->on('geokret.updated', function (GeoKrety\Model\Geokret $geokret) {
    if (!is_null($geokret->owner) && $geokret->changed('owner')) {
        \GeoKrety\Service\UserBanner::generate($geokret->owner);
    }
    audit('geokret.updated', $geokret);
    Metrics::counter('geokrety_updated_total', 'Total number of GeoKrety updated');
});
$events->on('geokret.deleted', function (GeoKrety\Model\Geokret $geokret) {
    if (!is_null($geokret->owner)) {
        \GeoKrety\Service\UserBanner::generate($geokret->owner);
    }
    audit('geokret.deleted', $geokret);
    Metrics::counter('geokrety_deleted_total', 'Total number of GeoKrety deleted');
});
$events->on('geokret.owner_code.created', function (GeoKrety\Model\OwnerCode $ownerCode) {
    audit('geokret.owner_code.created', $ownerCode);
    Metrics::counter('geokrety_owner_code_created_total', 'Total number of GeoKrety owner code created');
});
$events->on('geokret.claimed', function (GeoKrety\Model\Geokret $geokret, $context) {  // context => $oldUser, $newUser
    \GeoKrety\Service\UserBanner::generate($context['newUser']);
    if (!is_null($context['oldUser'])) {
        \GeoKrety\Service\UserBanner::generate($context['oldUser']);
    }
    audit('geokret.claimed', $geokret); // TODO: context
    Metrics::counter('geokrety_claimed_total', 'Total number of GeoKrety claimed');
});
$events->on('user.statpic.template.changed', function (GeoKrety\Model\User $user) {
    \GeoKrety\Service\UserBanner::generate($user);
    audit('user.statpic.template.changed', $user);
    Metrics::counter('user_statpic_template_changed_total', 'Total number of user statpic selected template');
});
$events->on('user.statpic.generated', function (GeoKrety\Model\User $user) {
    audit('user.statpic.generated', $user);
    Metrics::counter('user_statpic_generated_total', 'Total number of user statpic generation');
});
$events->on('cron.dailymail.nomail', function (GeoKrety\Model\User $user) {
    audit('cron.dailymail.nomail', $user);
    Metrics::counter('cron_dailymail', 'Total number of dailymail notification', ['status'], ['nomail']);
});
$events->on('cron.dailymail.deny', function (GeoKrety\Model\User $user) {
    audit('cron.dailymail.deny', $user);
    Metrics::counter('cron_dailymail', 'Total number of dailymail notification', ['status'], ['deny']);
});
$events->on('cron.dailymail.empty', function (GeoKrety\Model\User $user) {
    audit('cron.dailymail.empty', $user);
    Metrics::counter('cron_dailymail', 'Total number of dailymail notification', ['status'], ['empty']);
});
$events->on('cron.dailymail.error', function (GeoKrety\Model\User $user) {
    audit('cron.dailymail.error', $user);
    Metrics::counter('cron_dailymail', 'Total number of dailymail notification', ['status'], ['error']);
});
$events->on('cron.dailymail.sent', function (GeoKrety\Model\User $user) {
    audit('cron.dailymail.sent', $user);
    Metrics::counter('cron_dailymail', 'Total number of dailymail notification', ['status'], ['sent']);
});
$events->on('awarded.created', function (GeoKrety\Model\AwardsWon $award) {
    audit('awarded.created', $award);
    Metrics::counter('award_awarded', 'Total number of awards awarded', ['award'], ['created']);
    $mail = new \GeoKrety\Email\Awards();
    $mail->sendAwardReceived($award);
});
$events->on('awarded.updated', function (GeoKrety\Model\AwardsWon $award) {
    audit('awarded.updated', $award);
    Metrics::counter('award_updated', 'Total number of awards awarded', ['award'], ['updated']);
});
$events->on('awarded.deleted', function (GeoKrety\Model\AwardsWon $award) {
    audit('awarded.deleted', $award);
    Metrics::counter('award_deleted', 'Total number of awards awarded', ['award'], ['deleted']);
});
