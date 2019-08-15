<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\User;

class BaseUser extends Base {
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
}
