<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class BaseRegistration extends Base {
    protected User $user;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);

        $user = new User();
        $this->user = $user;
        Smarty::assign('user', $this->user);
    }

    protected function checkUniqueEmail(string $func = 'get') {
        if ($this->user->isEmailUnique()) {
            $link = $this->f3->alias('password_recovery');
            Flash::instance()->addMessage(
                sprintf(_('Sorry but this mail address is already in use. Do you want to <a href="%s">reset your password</a>?'), $link),
                'danger');
            $this->$func($this->f3);
            exit();
        }
    }
}
