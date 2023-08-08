<?php

namespace GeoKrety\Controller;

use Carbon\Carbon;
use GeoKrety\Email\EmailChange;
use GeoKrety\Model\EmailActivationToken;
use GeoKrety\Service\Smarty;
use Sugar\Event;

class UserUpdateEmail extends Base {
    use \CurrentUserLoader;

    public function get_ajax(\Base $f3) {
        // Reset eventual transaction
        if ($f3->get('DB')->trans()) {
            $f3->get('DB')->rollback();
        }
        Smarty::render('extends:base_modal.tpl|dialog/user_update_email.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf();
        $f3->get('DB')->begin();
        $user = $this->currentUser;
        $daily_mail = filter_var($f3->get('POST.daily_mails'), FILTER_VALIDATE_BOOLEAN);

        // Save user preferences
        if ($user->daily_mails !== $daily_mail) { // If preferences changed
            $user->daily_mails = $daily_mail;
            if (!$user->validate()) {
                $this->get($f3);
                exit;
            }
            $user->save();
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
                \Flash::instance()->addMessage(sprintf(_('The confirmation email was sent again to your new address. You must click on the link provided in the email to confirm the change to your email address. The confirmation link expires in %s.'), Carbon::instance($token->update_expire_on_datetime)->diffForHumans(['parts' => 3, 'join' => true])), 'success');
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
            if ($user->count(['_email_hash = public.digest(lower(?), \'sha256\')', $f3->get('POST.email')], null, 0)) { // no cache
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
            \Flash::instance()->addMessage(sprintf(_('A confirmation email was sent to your new address. You must click on the link provided in the email to confirm the change to your email address. The confirmation link expires in %s.'), Carbon::instance($token->update_expire_on_datetime)->longAbsoluteDiffForHumans(['parts' => 3, 'join' => true])), 'success');
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

    public function get(\Base $f3) {
        // Reset eventual transaction
        if ($f3->get('DB')->trans()) {
            $f3->get('DB')->rollback();
        }
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_update_email.tpl');
    }
}
