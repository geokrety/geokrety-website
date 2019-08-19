<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\User;

class UserRefreshSecid extends Base {
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
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_refresh_secid.tpl');
    }

    public function get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/user_refresh_secid.tpl');
    }

    public function post(\Base $f3) {
        $user = $this->user;
        $user->refreshSecid();

        if ($user->validate()) {
            $user->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to refresh your secid.'), 'danger');
            } else {
                \Event::instance()->emit('user.secid.changed', $user);
                \Flash::instance()->addMessage(_('Your secid has been refreshed. Don\'t forget to re-authenticate any other application connected to your GeoKrety account.'), 'success');
            }
        }

        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }
}
