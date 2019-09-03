<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\EmailActivation;

class UserEmailChangeRevertToken extends Base {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $token = new EmailActivation();

        // Check database for provided token
        if ($f3->exists('PARAMS.token')) {
            $token->load(array('revert_token = ? AND used = ? AND DATE_ADD(used_on_datetime, INTERVAL ? DAY) >= NOW()', $f3->get('PARAMS.token'), EmailActivation::TOKEN_CHANGED, GK_SITE_EMAIL_REVERT_CODE_DAYS_VALIDITY));
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
        Smarty::render('pages/email_change_revert_token.tpl');
    }

    public function accept($f3) {
        // Mark token as used
        $this->token->used = EmailActivation::TOKEN_VALIDATED;
    }

    public function refuse($f3) {
        // Mark token as used
        $this->token->used = EmailActivation::TOKEN_REVERTED;
        $this->token->touch('reverted_on_datetime');
        $this->token->user->email = $this->token->previous_email;

        if (!$this->token->user->validate()) {
            $this->get($f3);
            die();
        }
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

        $this->token->reverting_ip = \Base::instance()->get('IP');
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
            \Flash::instance()->addMessage(_('Perfect! Enjoy your new email address. (This token is now revoked)'), 'success');
        } else {
            \Event::instance()->emit('user.email.changed', $this->token);
            if ($this->sendEmail($this->token->user)) {
                \Flash::instance()->addMessage(_('Your email address has been reverted.'), 'success');
            }
        }

        $f3->reroute(sprintf('user_details(@userid=%d)', $this->token->user->id));
    }

    protected function sendEmail($user) {
        $subject = GK_EMAIL_SUBJECT_PREFIX.'✉️ '._('Email address reverted');
        $smtp = new \SMTP(GK_SMTP_HOST, GK_SMTP_PORT, GK_SMTP_SCHEME, GK_SMTP_USER, GK_SMTP_PASSWORD);
        $smtp->set('From', GK_SITE_EMAIL);
        $smtp->set('Errors-To', GK_SITE_EMAIL);
        $smtp->set('Content-Type', 'text/html; charset=UTF-8');
        $smtp->set('Subject', '=?utf-8?B?'.base64_encode($subject).'?=');
        Smarty::assign('subject', $subject);

        $smtp->set('To', $user->email);
        if (!$smtp->send(Smarty::fetch('email-address-reverted.html'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');

            return false;
        }

        return true;
    }
}
