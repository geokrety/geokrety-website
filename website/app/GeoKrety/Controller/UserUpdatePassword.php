<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\User;
use Hautelook\Phpass\PasswordHash;

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
        $hasher = new PasswordHash(GK_PASSWORD_HASH_ROTATION, false);
        $user->password = $hasher->HashPassword($password_new.GK_PASSWORD_HASH.GK_PASSWORD_SEED);
        if ($user->validate()) {
            $user->save();
            $this->sendEmail($user);
            \Flash::instance()->addMessage(_('Your password has been changed.'), 'success');
        }

        $f3->reroute(sprintf('@user_details(@userid=%d)', $user->id));
    }

    protected function sendEmail($user) {
        $smtp = new \SMTP(GK_SMTP_HOST, GK_SMTP_PORT, GK_SMTP_SCHEME, GK_SMTP_USER, GK_SMTP_PASSWORD);
        $smtp->set('From', GK_SITE_EMAIL);
        $smtp->set('To', $user->email);
        $smtp->set('Errors-To', GK_SITE_EMAIL);
        $smtp->set('Content-Type', 'text/html; charset=UTF-8');
        $smtp->set('Subject', _('GeoKrety: Your password has been changed'));
        if (!$smtp->send(Smarty::fetch('mails/password_changed.tpl'))) {
            \Flash::instance()->addMessage(_('An error occured while sending the confirmation mail.'), 'danger');
        }
    }
}
