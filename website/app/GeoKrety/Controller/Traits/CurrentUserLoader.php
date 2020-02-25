<?php

use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

trait CurrentUserLoader {
    /**
     * @var User
     */
    protected $currentUser;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);

        if (!$f3->get('SESSION.IS_LOGGED_IN')) {
            // TODO auth first
            Smarty::render('dialog/login.tpl');
            die();
        }

        $user = new User();
        $user->load(['id = ?', $f3->get('SESSION.CURRENT_USER')]);
        if ($user->dry()) {
            // TODO:
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        $this->currentUser = $user;
        Smarty::assign('currentUser', $this->currentUser);

        if (method_exists($this, '_beforeRoute')) {
            $this->_beforeRoute($f3);
        }
    }
}
