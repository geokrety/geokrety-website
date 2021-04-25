<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class BaseRegistration extends Base {
    /**
     * @var User
     */
    protected $user;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);

        $user = new User();
        $this->user = $user;
        Smarty::assign('user', $this->user);
    }

    protected function checkUniqueEmail(string $func = 'get') {
        if ($this->user->isEmailUnique()) {
            Flash::instance()->addMessage(_('Sorry but this mail address is already in use.'), 'danger');
            $this->$func($this->f3);
            exit();
        }
    }
}
