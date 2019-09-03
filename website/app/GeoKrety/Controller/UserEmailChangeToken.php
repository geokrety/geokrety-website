<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\EmailActivation;

class UserEmailChangeToken extends Base {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $token = new EmailActivation();

        // Check database for provided token
        if ($f3->exists('PARAMS.token')) {
            $token->load(array('token = ? AND used = ? AND DATE_ADD(created_on_datetime, INTERVAL ? DAY) >= NOW()', $f3->get('PARAMS.token'), EmailActivation::TOKEN_UNUSED, GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY));
            if ($token->dry()) {
                \Flash::instance()->addMessage(_('Sorry this token is not valid, already used or expired.'), 'danger');
                $f3->reroute('user_update_email_validate');
            }
            $token->token = $f3->get('PARAMS.token');
        }

        $this->token = $token;
        Smarty::assign('token', $this->token);
    }

    public function get($f3) {
        // Reset eventual transaction
        if ($f3->get('DB')->trans()) {
            $f3->get('DB')->rollback();
        }
        Smarty::render('pages/email_change_token.tpl');
    }

    public function accept($f3) {
        // Mark token used
        EmailActivation::disableOtherTokensForUser($this->token->user, $this->token->token);
        $this->token->used = EmailActivation::TOKEN_VALIDATED;
        $this->token->touch('used_on_datetime');
        $this->token->previous_email = $this->token->user->email;

        // Save the new email
        $this->token->user->email = $this->token->email;
        $this->token->user->email_invalid = 0;

        if (!$this->token->user->validate()) {
            $this->get($f3);
            die();
        }

    }

    public function refuse($f3) {
        // Mark token as used
        $this->token->used = EmailActivation::TOKEN_REFUSED;
    }

    public function post($f3) {
        $f3->get('DB')->begin();

        // Check the wanted action
        if ($f3->get('POST.validate') === 'true') {
            $this->accept($f3);
        } elseif ($f3->get('POST.validate') === 'false') {
            $this->refuse($f3);
        } else {
            \Flash::instance()->addMessage(_('Unexpected value.'), 'danger');
            $this->get($f3);
            die();
        }

        if (!$this->token->validate()) {
            $this->get($f3);
            die();
        }

        $this->token->user->save();
        $this->token->save();

        if ($f3->get('ERROR')) {
            \Flash::instance()->addMessage(_('Something went wrong, operation aborted.'), 'danger');
            $this->get($f3);
            die();
        }

        $f3->get('DB')->commit();
        \Event::instance()->emit('email.token.used', $this->token);

        // Notifications
        if ($f3->get('POST.validate') === 'true') {
            \Event::instance()->emit('user.email.changed', $this->token);
            if ($this->sendEmail($this->token->user)) {
                \Flash::instance()->addMessage(_('Your email address has been validated.'), 'success');
            }
        } elseif ($f3->get('POST.validate') === 'false') {
            \Flash::instance()->addMessage(_('No change has been processed. This token is now revoked.'), 'warning');
        }

        $f3->reroute(sprintf('user_details(@userid=%d)', $this->token->user->id));
    }

    protected function sendEmail($user) {
        $subject = GK_EMAIL_SUBJECT_PREFIX.'✉️ '._('Email address changed');
        $smtp = new \SMTP(GK_SMTP_HOST, GK_SMTP_PORT, GK_SMTP_SCHEME, GK_SMTP_USER, GK_SMTP_PASSWORD);
        $smtp->set('From', GK_SITE_EMAIL);
        $smtp->set('Errors-To', GK_SITE_EMAIL);
        $smtp->set('Content-Type', 'text/html; charset=UTF-8');
        $smtp->set('Subject', '=?utf-8?B?'.base64_encode($subject).'?=');
        Smarty::assign('subject', $subject);

        $smtp->set('To', $user->email);
        if (!$smtp->send(Smarty::fetch('email-address-changed.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');

            return false;
        }

        return true;
    }
}
