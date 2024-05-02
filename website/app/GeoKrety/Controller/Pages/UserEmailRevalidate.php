<?php

namespace GeoKrety\Controller;

use GeoKrety\Email\AccountActivation;
use GeoKrety\Model\AccountActivationToken;
use GeoKrety\Model\EmailRevalidateToken;

class UserEmailRevalidate extends Base {
    public function get(\Base $f3) {
        $csrf = $f3->get('PARAMS.csrf');
        $tokenid = $f3->get('PARAMS.tokenid');

        if ($csrf !== $f3->get(AccountActivation::SESSION_SEND_ACTIVATION_AGAIN)) {
            \Flash::instance()->addMessage(_('Sorry this link is invalid.'), 'danger');
            http_response_code(400);
            $f3->reroute('@home', $die = true);
        }

        $token = new AccountActivationToken();
        $token->load(['id = ?', $tokenid]);
        if ($token->dry()) {
            \Flash::instance()->addMessage(_('Sorry this link is invalid.'), 'danger');
            http_response_code(400);
            $f3->reroute('@home', $die = true);
        }

        $smtp = new AccountActivation();
        $smtp->_sendActivation($token->user);
        $f3->reroute('@home', $die = true);
    }

    public function get_account_imported(\Base $f3) {
        if (!$this->current_user->isAccountImported()) {
            \Flash::instance()->addMessage(_('Your account is already validated.'), 'success');
            $f3->reroute(sprintf('@user_details(@userid=%d)', $this->current_user->id), $die = true);
        }

        $token = new EmailRevalidateToken();
        $token->load([
            'user = ? AND used = ? AND created_on_datetime >= now() - cast(? as interval)',
            $this->current_user->id,
            EmailRevalidateToken::TOKEN_UNUSED,
            GK_SITE_EMAIL_REVALIDATE_CODE_DAYS_VALIDITY.' DAY',
        ]);

        if ($token->dry()) {
            $token->user = $this->current_user;
            $token->set_email($this->current_user->email);
            $token->save();
        } elseif (!$token->sendIntervalValid()) {
            \Flash::instance()->addMessage(sprintf(
                _('The last notification was sent less than %d minutes ago. Please try again later.'),
                GK_SITE_EMAIL_REVALIDATE_SEND_INTERVAL_MINUTES
            ), 'warning');
            $f3->reroute(sprintf('@user_details(@userid=%d)', $this->current_user->id), $die = true);
        }

        $smtp = new \GeoKrety\Email\EmailRevalidate();
        $smtp->sendRevalidation($token);
        $f3->reroute(sprintf('@user_details(@userid=%d)', $this->current_user->id), $die = true);
    }
}
