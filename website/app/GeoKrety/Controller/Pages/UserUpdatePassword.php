<?php

namespace GeoKrety\Controller;

use CurrentUserLoader;
use Flash;
use GeoKrety\Auth;
use GeoKrety\Email\PasswordChange as PasswordChangeEmail;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;
use Sugar\Event;

class UserUpdatePassword extends Base {
    use CurrentUserLoader;

    public function get() {
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_update_password.tpl');
    }

    public function get_ajax() {
        Smarty::render('extends:base_modal.tpl|dialog/user_update_password.tpl');
    }

    public function post(\Base $f3) {
        $password_old = $f3->get('POST.password_old');
        $password_new = $f3->get('POST.password_new');
        $password_new_confirm = $f3->get('POST.password_new_confirm');

        // Load current user
        $user = new User();
        $user->load(['id = ?', $f3->get('SESSION.CURRENT_USER')]);

        // Check old password
        $auth = new Auth('password', ['id' => 'username', 'pw' => 'password']);
        $check_result = $auth->login($user->username, $password_old);
        if (!$check_result) {
            Flash::instance()->addMessage(_('Your old password is invalid.'), 'danger');
            $this->get();
            exit();
        }

        // Check passwords are equals
        if ($password_new !== $password_new_confirm) {
            Flash::instance()->addMessage(_('New passwords doesn\'t match.'), 'danger');
            $this->get();
            exit();
        }

        // Save new password
        $user->password = $password_new;
        if ($user->validate()) {
            $user->save();

            Event::instance()->emit('user.password.changed', $user);
            Flash::instance()->addMessage(_('Your password has been changed.'), 'success');

            // Send email
            $smtp = new PasswordChangeEmail();
            $smtp->sendPasswordChangedNotification($user);
        }

        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }
}
