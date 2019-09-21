<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\User;
use GeoKrety\Email\PasswordChange as PasswordChangeEmail;

class UserUpdatePassword extends Base {
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
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_update_password.tpl');
    }

    public function get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/user_update_password.tpl');
    }

    public function post(\Base $f3) {
        $password_old = $f3->get('POST.password_old');
        $password_new = $f3->get('POST.password_new');
        $password_new_confirm = $f3->get('POST.password_new_confirm');

        // Load current user
        $user = new \GeoKrety\Model\User();
        $user->load(array('id = ?', $f3->get('SESSION.CURRENT_USER')));

        // Check old password
        $auth = new \GeoKrety\Auth('geokrety', array('id' => 'username', 'pw' => 'password'));
        $check_result = $auth->login($user->username, $f3->get('POST.password_old'));
        if (!$check_result) {
            \Flash::instance()->addMessage(_('Your old password is invalid.'), 'danger');
            $this->get($f3);
            die();
        }

        // Check passwords are equals
        if ($password_new !== $password_new_confirm) {
            \Flash::instance()->addMessage(_('New passwords doesn\'t match.'), 'danger');
            $this->get($f3);
            die();
        }

        // Save new password
        $user->password = $password_new;
        if ($user->validate()) {
            $user->save();

            \Event::instance()->emit('user.password.changed', $user);
            \Flash::instance()->addMessage(_('Your password has been changed.'), 'success');

            // Send email
            $smtp = new PasswordChangeEmail();
            $smtp->sendPasswordChangedNotification($user);
        }

        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }
}
