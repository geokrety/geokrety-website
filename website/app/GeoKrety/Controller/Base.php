<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\User;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Smarty;

abstract class Base {
    public function beforeRoute(\Base $f3) {
        // Load supported languages
        Smarty::assign('languages', LanguageService::getSupportedLanguages(true));

        // Load current user
        if ($f3->exists('SESSION.CURRENT_USER')) {
            $user = new User();
            $user->filter('email_activation', ['used = 0']);
            $user->load(['id = ?', $f3->get('SESSION.CURRENT_USER')]);
            if ($user->valid()) {
                Smarty::assign('current_user', $user);
            }
        }
    }

//    public function afterRoute($f3) {
//        \Flash::instance()->addMessage('<pre>'.$f3->get('DB')->log().'</pre>', 'warning');
//    }
}
