<?php

use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

trait UserLoader {
    /**
     * @var User
     */
    protected $user;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);

        // load User
        $user = new User();
        $user->filter('badges', null, ['order' => 'awarded_on_datetime ASC']);
        $user->load(['id = ?', $f3->get('PARAMS.userid')]);
        if ($user->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        $this->user = $user;
        Smarty::assign('user', $user);
    }
}
