<?php

namespace GeoKrety\Controller;

use CurrentUserLoader;
use Flash;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;
use Sugar\Event;

class UserUpdateUsername extends Base {
    use CurrentUserLoader;

    public function post(\Base $f3) {
        $this->check_email($f3);

        $f3->get('DB')->begin();
        $context = ['old_username' => $this->currentUser->username];
        $newUsername = $f3->get('POST.username');
        Smarty::assign('newUsername', $newUsername);
        $this->currentUser->username = $newUsername;

        // reCaptcha
        $this->checkCaptcha();

        // Check
        if (!$this->currentUser->validate()) {
            $f3->get('DB')->rollback();
            $this->get($f3);
            exit();
        }

        // Save
        $this->currentUser->save();
        $f3->get('DB')->commit();
        Event::instance()->emit('user.renamed', $this->currentUser, $context);
        (new Login())->disconnectUser($f3, false); // TODO: close all other sessions for this user?
        Flash::instance()->addMessage(_('Username changed. Please login again.'), 'success');
        $f3->reroute('@home');
    }

    public function get(\Base $f3) {
        $this->check_email($f3);
        Smarty::render('pages/user_update_username.tpl');
    }

    private function check_email(\Base $f3): void {
        if (!$this->current_user->isEmailValid() or !$this->current_user->hasEmail()) {
            Flash::instance()->addMessage(_('Sorry, to use this feature, you must have a valid registered email address.'), 'danger');
            $f3->reroute(['user_details', ['userid' => $this->current_user->id]]);
        }
    }
}
