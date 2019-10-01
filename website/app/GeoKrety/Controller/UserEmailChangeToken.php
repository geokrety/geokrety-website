<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\EmailActivationToken;
use GeoKrety\Email\EmailChange;

class UserEmailChangeToken extends Base {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $token = new EmailActivationToken();

        // Check database for provided token
        if ($f3->exists('PARAMS.token')) {
            $token->load(array('token = ? AND used = ? AND DATE_ADD(created_on_datetime, INTERVAL ? DAY) >= NOW()', $f3->get('PARAMS.token'), EmailActivationToken::TOKEN_UNUSED, GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY));
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
        EmailActivationToken::disableOtherTokensForUser($this->token->user, $this->token->token);
        $this->token->used = EmailActivationToken::TOKEN_CHANGED;
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
        $this->token->used = EmailActivationToken::TOKEN_REFUSED;
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

        $this->token->updating_ip = \Base::instance()->get('IP');
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
            $smtp = new EmailChange();
            $smtp->sendEmailChangedNotification($this->token);
            \Flash::instance()->addMessage(_('Your email address has been validated.'), 'success');
        } else {
            \Flash::instance()->addMessage(_('No change has been processed. This token is now revoked.'), 'warning');
        }

        $f3->reroute(sprintf('user_details(@userid=%d)', $this->token->user->id));
    }
}
