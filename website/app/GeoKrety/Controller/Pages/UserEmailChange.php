<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Model\EmailActivationToken;
use GeoKrety\Service\Smarty;

class UserEmailChange extends Base {
    public function get(\Base $f3) {
        $token = new EmailActivationToken();
        Smarty::assign('token', $token);

        // Check database for provided token
        if ($f3->exists('POST.token')) {
            $this->checkCsrf(function ($error) {
                Flash::instance()->addMessage($error, 'danger');
                Smarty::render('pages/email_change.tpl');
                exit();
            });
            $token->load(['token = ? AND used = ? AND created_on_datetime > NOW() - cast(? as interval)', $f3->get('POST.token'), EmailActivationToken::TOKEN_UNUSED, GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY.' DAY']);
            if ($token->valid()) {
                $f3->reroute(sprintf('@user_update_email_validate_token(@token=%s)', $f3->get('POST.token')));
            }
            Flash::instance()->addMessage(_('Sorry this token is not valid, already used or expired.'), 'danger');
            $token->token = $f3->get('POST.token');
        }

        Smarty::render('pages/email_change.tpl');
    }
}
