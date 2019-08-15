<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\EmailActivation;

class UserValidateEmail extends Base {
    public function get($f3) {
        $activation = new EmailActivation();
        $activation->load(array('token = ?', $f3->get('PARAMS.token')));
        if ($activation === false) {
            \Flash::instance()->addMessage(_('Sorry this validation token is invalid or expired.'), 'danger');
        } else {
            $f3->get('DB')->begin();
            $user = $activation->user;
            $user->email = $activation->email;
            if ($user->validate()) {
                $user->save();
                $activation->erase(array('token = ?', $f3->get('PARAMS.token')));
            }
            $f3->get('DB')->commit();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to activate your email address.'), 'danger');
            } else {
                \Flash::instance()->addMessage(_('Your email address has been validated.'), 'success');
            }

        }
        $f3->reroute('@home');
    }
}
