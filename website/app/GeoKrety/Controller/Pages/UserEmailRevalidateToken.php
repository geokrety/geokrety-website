<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Model\EmailRevalidateToken;
use GeoKrety\Service\Smarty;

class UserEmailRevalidateToken extends Base {
    public function get(\Base $f3) {
        $token = new EmailRevalidateToken();
        $_token = $f3->get('PARAMS.token') ?? ($f3->get('POST.token') ?? null);
        Smarty::assign('token', $token);

        if ($f3->exists('POST.token')) {
            $this->checkCsrf(function ($error) {
                Flash::instance()->addMessage($error, 'danger');
                Smarty::render('pages/email_revalidate.tpl');
                exit();
            });
        }

        if ($_token) {
            $token->load([
                'token = ? AND used = ? AND created_on_datetime > NOW() - cast(? as interval)',
                $_token,
                EmailRevalidateToken::TOKEN_UNUSED,
                GK_SITE_EMAIL_REVALIDATE_CODE_DAYS_VALIDITY.' DAY',
            ]);

            if ($token->valid()) {
                $token->used = EmailRevalidateToken::TOKEN_VALIDATED;
                $token->save();
                Flash::instance()->addMessage(_('You have successfully validated your email address.'), 'success');
                $f3->reroute(sprintf('@user_details(@userid=%d)', $token->user->id));
            }
            Flash::instance()->addMessage(_('Sorry this token is not valid, already used or expired.'), 'danger');
            $token->token = $_token;
        }

        Smarty::render('pages/email_revalidate.tpl');
    }
}
