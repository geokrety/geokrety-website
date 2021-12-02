<?php

use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

trait CurrentUserLoader {
    protected User $currentUser;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);

        if (!$f3->get('SESSION.IS_LOGGED_IN')) {
            // TODO auth first
            Smarty::render('dialog/login.tpl');
            exit();
        }

        $user = new User();
        $user->load(['id = ?', $f3->get('SESSION.CURRENT_USER')]);
        if ($user->dry()) {
            // TODO:
            http_response_code(404);
            Smarty::render('dialog/alert_404.tpl');
            exit();
        }
        $this->currentUser = $user;
        Smarty::assign('currentUser', $this->currentUser);

        if (method_exists($this, '_beforeRoute')) {
            $this->_beforeRoute($f3);
        }
    }
}
