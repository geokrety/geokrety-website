<?php

use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

trait UserLoader {
    /**
     * @var User
     */
    protected $user;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);

        // load User
        $user = new User();
        $this->user = $user;
        $user->filter('badges', null, ['order' => 'awarded_on_datetime ASC']);
        $user->filter('avatars', ['uploaded_on_datetime != ?', null]);
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
