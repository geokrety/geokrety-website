<?php

namespace GeoKrety\Controller;

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
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_update_email.tpl');
    }

    public function get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/user_update_email.tpl');
    }

    public function post(\Base $f3) {
        $f3->get('DB')->begin();
        $userid = $this->user->id;
        $daily_mail = filter_var($f3->get('POST.daily_mails'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $error = false;

        // Save user preferences
        $user = $this->user;
        if ($user->daily_mails !== $daily_mail) {
            $user->daily_mails = $daily_mail;
            if ($user->validate()) {
                $user->save();
                \Flash::instance()->addMessage(_('Your email preferences were saved.'), 'success');
            } else {
                $error = true;
            }
        }

        // Generate activation token and send mail
        if ($this->user->email !== $f3->get('POST.email')) {
            $activation = new EmailActivation();
            // Purge expired tokens
            $activation->erase(array('NOW() >= DATE_ADD(created_on_datetime, INTERVAL ? DAY)', GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY));

            // Check email unicity over both tables
            if ($user->count(array('email = ?', $f3->get('POST.email'))) || $activation->count(array('email = ?', $f3->get('POST.email')), null, 0)) {
                \Flash::instance()->addMessage(_('Sorry but this mail address is already in use.'), 'danger');
                $error = true;
            } else {
                // Purge eventual old tokens
                $activation->erase(array('user = ?', $f3->get('SESSION.CURRENT_USER')));
                // Saving…
                $activation->user = $f3->get('SESSION.CURRENT_USER');
                $activation->email = $f3->get('POST.email');
                if ($activation->validate()) {
                    $activation->save();
                    Smarty::assign('token', $activation->token);
                    $this->sendEmail($user);
                    \Flash::instance()->addMessage(sprintf(_('A confirmation email was sent to your new address. You must click on the link provided in the email to confirm the change to your email address. The confirmation link is valid for %d days.'), GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY), 'warning');
                } else {
                    $error = true;
                }
            }
        }

        // Was there some errors?
        if ($error) {
            $user->email = $f3->get('POST.email'); // Copy value to not reset form ;)
            $this->get($f3);
            die();
        }

        $f3->get('DB')->commit();
        $f3->reroute("@user_details(@userid=$userid)");
    }

    protected function sendEmail($user) {
        $smtp = new \SMTP(GK_SMTP_HOST, GK_SMTP_PORT, GK_SMTP_SCHEME, GK_SMTP_USER, GK_SMTP_PASSWORD);
        $smtp->set('From', GK_SITE_EMAIL);
        $smtp->set('Errors-To', GK_SITE_EMAIL);
        $smtp->set('Content-Type', 'text/html; charset=UTF-8');
        $smtp->set('Subject', _('GeoKrety: changing your email address'));

        $smtp->set('To', $user->email);
        if (!$smtp->send(Smarty::fetch('mails/email_changed_to_old_address.tpl'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');
        }
        $smtp->set('To', \Base::instance()->get('POST.email'));
        if (!$smtp->send(Smarty::fetch('mails/email_changed_to_new_address.tpl'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');
        }
    }
}
