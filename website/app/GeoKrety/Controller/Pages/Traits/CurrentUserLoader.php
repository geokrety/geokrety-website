<?php

use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

trait CurrentUserLoader {
    protected User $currentUser;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);

        if (!$f3->get('SESSION.IS_LOGGED_IN')) {
            $f3->error(401);
        }

        $user = new User();
        $user->load(['id = ?', $f3->get('SESSION.CURRENT_USER')]);
        if ($user->dry()) {
            $f3->error(404, _('This page doesn\'t exists.'));
        }
        $this->currentUser = $user;
        Smarty::assign('currentUser', $this->currentUser);

        if (method_exists($this, '_beforeRoute')) {
            $this->_beforeRoute($f3);
        }
    }
}