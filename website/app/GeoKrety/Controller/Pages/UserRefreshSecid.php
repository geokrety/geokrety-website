<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class UserRefreshSecid extends Base {
    use \CurrentUserLoader;

    public function get() {
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_refresh_secid.tpl');
    }

    public function get_ajax() {
        Smarty::render('extends:base_modal.tpl|dialog/user_refresh_secid.tpl');
    }

    public function post(\Base $f3) {
        $user = $this->currentUser;
        $user->_secid = '';

        if ($user->validate()) {
            $user->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to refresh your secid.'), 'danger');
            } else {
                \Sugar\Event::instance()->emit('user.secid.changed', $user);
                \Flash::instance()->addMessage(_('Your secid has been refreshed. Don\'t forget to re-authenticate any other application connected to your GeoKrety account.'), 'success');
            }
        }

        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }
}
