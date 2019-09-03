<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\EmailActivation;

class UserEmailChange extends Base {
    public function get($f3) {
        $token = new EmailActivation();

        // Check database for provided token
        if ($f3->exists('POST.token')) {
            $token->load(array('token = ? AND used = 0 AND DATE_ADD(created_on_datetime, INTERVAL '.GK_SITE_EMAIL_ACTIVATION_CODE_DAYS_VALIDITY.' DAY) >= ?', $f3->get('POST.token'), (new \DateTime())->format('Y-m-d H:i:s')));
            if ($token->valid()) {
                $f3->reroute(sprintf('@user_update_email_validate_token(@token=%s)', $f3->get('POST.token')));
            }
            \Flash::instance()->addMessage(_('Sorry this token is not valid, already used or expired.'), 'danger');
            $token->token = $f3->get('POST.token');
        }

        Smarty::assign('token', $token);
        Smarty::render('pages/email_change.tpl');
    }
}
