<?php

use GeoKrety\Model\EmailActivationToken;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

/**
 * Load the currently displayed user into `user` variable in php and smarty.
 */
trait UserLoader {
    protected User $user;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);
        $this->loadUser($f3);
    }

    public function loadUser(Base $f3) {
        $user = new User();
        $this->user = $user;
        $user->filter('awards', null, ['order' => 'awarded_on_datetime ASC']);
        $user->filter('avatars', ['uploaded_on_datetime != ?', null]);
        $user->filter('email_activation', ['used = ?', EmailActivationToken::TOKEN_UNUSED]);
        $this->filterHook();
        $user->load(['id = ?', $f3->get('PARAMS.userid')]);
        if ($user->dry()) {
            $f3->error(404, _('This user does not exist.'));
        }
        Smarty::assign('user', $user);
    }

    protected function filterHook() {
        // empty
    }
}
