<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class BaseUser extends Base {
    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $user = new User();
        $user->load(['id = ?', $f3->get('PARAMS.userid')]);
        if ($user->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        $this->user = $user;
        Smarty::assign('user', $this->user);
    }
}
