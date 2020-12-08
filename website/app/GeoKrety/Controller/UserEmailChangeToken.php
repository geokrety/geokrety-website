<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Email\EmailChange;
use GeoKrety\Model\EmailActivationToken;
use GeoKrety\Service\Smarty;
use Sugar\Event;

class UserEmailChangeToken extends Base {
    /**
     * @var EmailActivationToken
     */
    private $token;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);

        $token = new EmailActivationToken();

        // Check database for provided token
        if ($f3->exists('PARAMS.token')) {
            $token->load(['token = ? AND used = ? AND created_on_datetime > NOW() - cast(? as interval)', $f3->get('PARAMS.token'), EmailActivationToken::TOKEN_UNUSED, GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY.' DAY']);
            if ($token->dry()) {
                Flash::instance()->addMessage(_('Sorry this token is not valid, already used or expired.'), 'danger');
                $f3->reroute('@user_update_email_validate');
            }
            $token->token = $f3->get('PARAMS.token');
        }

        $this->token = $token;
        Smarty::assign('token', $this->token);
    }

    public function post(\Base $f3) {
        $f3->get('DB')->begin();

        // Check the wanted action
        if ($f3->get('POST.validate') === 'true') {
            $this->accept($f3);
        } elseif ($f3->get('POST.validate') === 'false') {
            $this->refuse();
        } else {
            Flash::instance()->addMessage(_('Unexpected value.'), 'danger');
            $this->get($f3);
            exit();
        }

        if (!$this->token->validate()) {
            $this->get($f3);
            exit();
        }

        $this->token->user->save();
        $this->token->save();

        if ($f3->get('ERROR')) {
            Flash::instance()->addMessage(_('Something went wrong, operation aborted.'), 'danger');
            $this->get($f3);
            exit();
        }

        $f3->get('DB')->commit();
        Event::instance()->emit('email.token.used', $this->token);

        // Notifications
        if ($f3->get('POST.validate') === 'true') {
            Event::instance()->emit('user.email.changed', $this->token->user);
            Flash::instance()->addMessage(_('Your email address has been validated.'), 'success');
            $f3->reroute(sprintf('@user_details(@userid=%d)', $this->token->user->id), false, false);
            if (!GK_DEVEL) {
                $f3->abort(); // Send response to client now
            }
            $smtp = new EmailChange();
            $smtp->sendEmailChangedNotification($this->token);
            exit();
        } else {
            Flash::instance()->addMessage(_('No change has been processed. This token is now revoked.'), 'warning');
        }

        $f3->reroute(sprintf('@user_details(@userid=%d)', $this->token->user->id));
    }

    public function accept(\Base $f3) {
        // Mark token used
        EmailActivationToken::disableOtherTokensForUser($this->token->user, $this->token->token);
        $this->token->used = EmailActivationToken::TOKEN_CHANGED;
        $this->token->touch('used_on_datetime');
        $this->token->updating_ip = \Base::instance()->get('IP');
        $this->token->_previous_email = $this->token->user->email;

        // Save the new email
        $this->token->user->_email = $this->token->email;
        $this->token->user->email_invalid = 0;

        if (!$this->token->user->validate()) {
            $this->get($f3);
            exit();
        }
    }

    public function get(\Base $f3) {
        // Reset eventual transaction
        if ($f3->get('DB')->trans()) {
            $f3->get('DB')->rollback();
        }
        Smarty::render('pages/email_change_token.tpl');
    }

    public function refuse() {
        // Mark token as used
        $this->token->used = EmailActivationToken::TOKEN_REFUSED;
        $this->token->touch('reverted_on_datetime');
        $this->token->reverting_ip = \Base::instance()->get('IP');
    }
}
