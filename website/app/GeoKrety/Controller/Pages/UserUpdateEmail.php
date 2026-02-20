<?php

namespace GeoKrety\Controller;

use Carbon\Carbon;
use GeoKrety\Email\EmailChange;
use GeoKrety\Model\EmailActivationToken;
use GeoKrety\Service\Smarty;
use GeoKrety\Service\UserSettings;
use GeoKrety\Traits\CurrentUserLoader;
use Sugar\Event;

class UserUpdateEmail extends Base {
    use CurrentUserLoader;

    public function get(\Base $f3) {
        // Reset eventual transaction
        if ($f3->get('DB')->trans()) {
            $f3->get('DB')->rollback();
        }
        Smarty::assign('daily_digest', UserSettings::getForCurrentUser('DAILY_DIGEST'));
        Smarty::assign('instant_notifications', UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS'));
        Smarty::assign('instant_notifications_moves_own_gk', UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_MOVES_OWN_GK'));
        Smarty::assign('instant_notifications_moves_watched_gk', UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_MOVES_WATCHED_GK'));
        Smarty::assign('instant_notifications_moves_around_home', UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_MOVES_AROUND_HOME'));
        Smarty::assign('instant_notifications_move_comments', UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_MOVE_COMMENTS'));
        Smarty::assign('instant_notifications_loves', UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_LOVES'));
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_update_email.tpl');
    }

    public function get_ajax(\Base $f3) {
        // Reset eventual transaction
        if ($f3->get('DB')->trans()) {
            $f3->get('DB')->rollback();
        }
        Smarty::assign('daily_digest', UserSettings::getForCurrentUser('DAILY_DIGEST'));
        Smarty::assign('instant_notifications', UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS'));
        Smarty::assign('instant_notifications_moves_own_gk', UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_MOVES_OWN_GK'));
        Smarty::assign('instant_notifications_moves_watched_gk', UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_MOVES_WATCHED_GK'));
        Smarty::assign('instant_notifications_moves_around_home', UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_MOVES_AROUND_HOME'));
        Smarty::assign('instant_notifications_move_comments', UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_MOVE_COMMENTS'));
        Smarty::assign('instant_notifications_loves', UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_LOVES'));
        Smarty::render('extends:base_modal.tpl|dialog/user_update_email.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf();
        $f3->get('DB')->begin();
        $user = $this->currentUser;

        // Get values from POST
        $daily_digest = filter_var($f3->get('POST.daily_digest'), FILTER_VALIDATE_BOOLEAN);
        $instant_notifications = filter_var($f3->get('POST.instant_notifications'), FILTER_VALIDATE_BOOLEAN);
        $instant_notifications_moves_own_gk = filter_var($f3->get('POST.instant_notifications_moves_own_gk'), FILTER_VALIDATE_BOOLEAN);
        $instant_notifications_moves_watched_gk = filter_var($f3->get('POST.instant_notifications_moves_watched_gk'), FILTER_VALIDATE_BOOLEAN);
        $instant_notifications_moves_around_home = filter_var($f3->get('POST.instant_notifications_moves_around_home'), FILTER_VALIDATE_BOOLEAN);
        $instant_notifications_move_comments = filter_var($f3->get('POST.instant_notifications_move_comments'), FILTER_VALIDATE_BOOLEAN);
        $instant_notifications_loves = filter_var($f3->get('POST.instant_notifications_loves'), FILTER_VALIDATE_BOOLEAN);

        // If instant_notifications is enabled, default all granular settings to true if not explicitly set
        if ($instant_notifications) {
            $instant_notifications_moves_own_gk = $instant_notifications_moves_own_gk ?? true;
            $instant_notifications_moves_watched_gk = $instant_notifications_moves_watched_gk ?? true;
            $instant_notifications_moves_around_home = $instant_notifications_moves_around_home ?? true;
            $instant_notifications_move_comments = $instant_notifications_move_comments ?? true;
        }

        // Save user preferences using UserSettings service
        $userSettings = UserSettings::instance();
        $changed = false;

        if (UserSettings::getForCurrentUser('DAILY_DIGEST') !== $daily_digest) {
            $userSettings->put($user, 'DAILY_DIGEST', $daily_digest ? 'true' : 'false');
            $changed = true;
        }

        if (UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS') !== $instant_notifications) {
            $userSettings->put($user, 'INSTANT_NOTIFICATIONS', $instant_notifications ? 'true' : 'false');
            $changed = true;
        }

        if (UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_MOVES_OWN_GK') !== $instant_notifications_moves_own_gk) {
            $userSettings->put($user, 'INSTANT_NOTIFICATIONS_MOVES_OWN_GK', $instant_notifications_moves_own_gk ? 'true' : 'false');
            $changed = true;
        }

        if (UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_MOVES_WATCHED_GK') !== $instant_notifications_moves_watched_gk) {
            $userSettings->put($user, 'INSTANT_NOTIFICATIONS_MOVES_WATCHED_GK', $instant_notifications_moves_watched_gk ? 'true' : 'false');
            $changed = true;
        }

        if (UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_MOVES_AROUND_HOME') !== $instant_notifications_moves_around_home) {
            $userSettings->put($user, 'INSTANT_NOTIFICATIONS_MOVES_AROUND_HOME', $instant_notifications_moves_around_home ? 'true' : 'false');
            $changed = true;
        }

        if (UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_MOVE_COMMENTS') !== $instant_notifications_move_comments) {
            $userSettings->put($user, 'INSTANT_NOTIFICATIONS_MOVE_COMMENTS', $instant_notifications_move_comments ? 'true' : 'false');
            $changed = true;
        }

        if (UserSettings::getForCurrentUser('INSTANT_NOTIFICATIONS_LOVES') !== $instant_notifications_loves) {
            $userSettings->put($user, 'INSTANT_NOTIFICATIONS_LOVES', $instant_notifications_loves ? 'true' : 'false');
            $changed = true;
        }

        if ($changed) {
            \Flash::instance()->addMessage(_('Your email preferences were saved.'), 'success');
        }

        // Generate activation token and send mail
        if ($user->email !== $f3->get('POST.email')) { // If email changed
            $token = new EmailActivationToken();
            Smarty::assign('token', $token);
            $smtp = new EmailChange();

            // Resend validation - implicit mail unicity from token table too
            $token->load(['user = ? AND _email_hash = public.digest(lower(?), \'sha256\') AND used = ? AND created_on_datetime > NOW() - cast(? as interval)', $user->id, $f3->get('POST.email'), EmailActivationToken::TOKEN_UNUSED, GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY.' DAY']);
            if ($token->valid()) {
                $smtp->sendEmailChangeNotification($token);
                \Flash::instance()->clearMessages(); // Reset previous messages
                \Flash::instance()->addMessage(sprintf(_('The confirmation email was sent again to your new address. You must click on the link provided in the email to confirm the change to your email address. The confirmation link expires in %s.'), Carbon::instance($token->expire_on_datetime)->diffForHumans(['parts' => 3, 'join' => true])), 'success');
                $f3->get('DB')->rollback();
                $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
            }

            // Someone else wishes to use this address
            $token->load(['user != ? AND _email_hash = public.digest(lower(?), \'sha256\') AND used = ? AND created_on_datetime > NOW() - cast(? as interval)', $user->id, $f3->get('POST.email'), EmailActivationToken::TOKEN_UNUSED, GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY.' DAY']);
            if ($token->valid()) {
                \Flash::instance()->addMessage(_('Sorry but this mail address is already in use.'), 'danger');
                $this->get($f3);
                exit;
            }

            // Check email unicity over users table
            if ($user->count(['_email_hash = public.digest(lower(?), \'sha256\')', $f3->get('POST.email')], ttl: 0)) { // no cache
                \Flash::instance()->addMessage(_('Sorry but this mail address is already in use.'), 'danger');
                $this->get($f3);
                exit;
            }

            // Saving…
            $token->user = $this->currentUser;
            $token->_email = $f3->get('POST.email');
            if (!$token->validate()) {
                $this->get($f3);
                exit;
            }
            $token->save();
            \Flash::instance()->addMessage(sprintf(_('A confirmation email was sent to your new address. You must click on the link provided in the email to confirm the change to your email address. The confirmation link expires in %s.'), Carbon::instance($token->expire_on_datetime)->longAbsoluteDiffForHumans(['parts' => 3, 'join' => true])), 'success');
            Event::instance()->emit('user.email.change', $token->user);
            // Redirect before sending emails
            $f3->get('DB')->commit();
            $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id), false, false); // Set die false to continue
            if (!GK_DEVEL) {
                $f3->abort(); // Send response to client now
            }
            $smtp->sendEmailChangeNotification($token);
            exit;
        }

        $f3->get('DB')->commit();
        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }
}
