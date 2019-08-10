<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class Login extends Base {
    public function loginForm($f3) {
        Smarty::render('extends:base.tpl|dialog/login.tpl');
    }

    // public function loginFormFragment($f3) {
    //     Smarty::render('dialog/login.tpl');
    // }

    public function login($f3) {
        $auth = new \GeoKrety\Auth('geokrety', array('id' => 'username', 'pw' => 'password'));
        $login_result = $auth->login($f3->get('POST.login'), $f3->get('POST.password'));
        if ($login_result) {
            $user = new \GeoKrety\Model\User();
            $user->load(array('username = ?', $f3->get('POST.login')));
            if ($user->valid()) {
                $f3->set('SESSION.CURRENT_USER', $user->id);
                $f3->set('SESSION.IS_LOGGED_IN', true);
                $f3->reroute('@home');
            }
        }
        Smarty::render('extends:base.tpl|dialog/login.tpl');
    }

    // public function loginFragment($f3) {
    //     $this->authenticate($f3);
    //     $this->loginFormFragment($f3);
    // }

    public function logout($f3) {
        $f3->set('SESSION.CURRENT_USER', null);
        $f3->set('SESSION.IS_LOGGED_IN', null);
        $f3->reroute('@home');
    }
}
