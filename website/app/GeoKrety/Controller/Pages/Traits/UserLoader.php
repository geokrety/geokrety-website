<?php

use GeoKrety\Model\EmailActivationToken;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

trait UserLoader {
    protected User $user;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);
        $this->loadUser($f3);
    }

    public function loadUser(Base $f3) {
        $user = new User();
        $this->user = $user;
        $user->filter('badges', null, ['order' => 'awarded_on_datetime ASC']);
        $user->filter('avatars', ['uploaded_on_datetime != ?', null]);
        $user->filter('email_activation', ['used = ?', EmailActivationToken::TOKEN_UNUSED]);
        $this->filterHook();
        $user->load(['id = ?', $f3->get('PARAMS.userid')]);
        if ($user->dry()) {
            http_response_code(404);
            Smarty::render('dialog/alert_404.tpl');
            exit();
        }
        Smarty::assign('user', $user);
    }

    protected function filterHook() {
        // empty
    }
}
