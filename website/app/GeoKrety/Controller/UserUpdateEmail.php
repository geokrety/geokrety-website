<?php

namespace GeoKrety\Controller;

use Carbon\Carbon;
use GeoKrety\Service\Smarty;
use GeoKrety\Model\EmailActivation;
use GeoKrety\Model\User;

class UserUpdateEmail extends Base {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $user = new User();
        $user->load(array('id = ?', $f3->get('SESSION.CURRENT_USER')));
        if ($user->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        $this->user = $user;
        Smarty::assign('user', $this->user);
    }

    public function get(\Base $f3) {
        // Reset eventual transaction
        if ($f3->get('DB')->trans()) {
            $f3->get('DB')->rollback();
        }
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_update_email.tpl');
    }

    public function get_ajax(\Base $f3) {
        // Reset eventual transaction
        if ($f3->get('DB')->trans()) {
            $f3->get('DB')->rollback();
        }
        Smarty::render('extends:base_modal.tpl|dialog/user_update_email.tpl');
    }

    public function post(\Base $f3) {
        $f3->get('DB')->begin();
        $user = $this->user;
        $daily_mail = filter_var($f3->get('POST.daily_mails'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        // Save user preferences
        if ($user->daily_mails !== $daily_mail) { // If preferences changed
            $user->daily_mails = $daily_mail;
            if (!$user->validate()) {
                $this->get($f3);
                die();
            }
            $user->save();
            \Flash::instance()->addMessage(_('Your email preferences were saved.'), 'success');
        }

        // Generate activation token and send mail
        if ($user->email !== $f3->get('POST.email')) { // If email changed
            $token = new EmailActivation();
            Smarty::assign('token', $token);
            EmailActivation::expireOldTokens();

            // Resend validation
            $token->load(array('email = ? AND used = ? AND DATE_ADD(created_on_datetime, INTERVAL ? DAY) >= NOW()', $f3->get('POST.email'), EmailActivation::TOKEN_UNUSED, GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY));
            if ($token->valid()) {
                $this->sendEmail($user);
                \Flash::instance()->addMessage(sprintf(_('The confirmation email was sent again to your new address. You must click on the link provided in the email to confirm the change to your email address. The confirmation link expires %s.'), Carbon::instance($token->expire_on_datetime)->diffForHumans(['parts' => 3, 'join' => true])), 'success');
                $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
            }

            // Check email unicity over both tables
            if ($user->count(array('email = ?', $f3->get('POST.email')), null, 0)) { // no cache
                \Flash::instance()->addMessage(_('Sorry but this mail address is already in use.'), 'danger');
                $this->get($f3);
                die();
            }

            // Saving…
            $token->user = $f3->get('SESSION.CURRENT_USER');
            $token->email = $f3->get('POST.email');
            if (!$token->validate()) {
                $this->get($f3);
                die();
            }
            $token->save();
            $this->sendEmail($user);
            \Event::instance()->emit('user.email.change', $token);
            \Flash::instance()->addMessage(sprintf(_('A confirmation email was sent to your new address. You must click on the link provided in the email to confirm the change to your email address. The confirmation link expires %s.'), Carbon::instance($token->expire_on_datetime)->longAbsoluteDiffForHumans(['parts' => 3, 'join' => true])), 'success');
        }

        $f3->get('DB')->commit();
        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }

    protected function sendEmail($user) {
        $subject = GK_EMAIL_SUBJECT_PREFIX.'✉️ '._('Changing your email address');
        $smtp = new \SMTP(GK_SMTP_HOST, GK_SMTP_PORT, GK_SMTP_SCHEME, GK_SMTP_USER, GK_SMTP_PASSWORD);
        $smtp->set('From', GK_SITE_EMAIL);
        $smtp->set('Errors-To', GK_SITE_EMAIL);
        $smtp->set('Content-Type', 'text/html; charset=UTF-8');
        $smtp->set('Subject', '=?utf-8?B?'.base64_encode($subject).'?=');
        Smarty::assign('subject', $subject);

        $smtp->set('To', $user->email);
        if (!$smtp->send(Smarty::fetch('email-change-to-old-address.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');
        }
        $smtp->set('To', \Base::instance()->get('POST.email'));
        if (!$smtp->send(Smarty::fetch('email-change-to-new-address.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');
        }
    }
}
