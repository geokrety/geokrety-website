<?php

namespace GeoKrety\Controller;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Smarty;

abstract class Base {
    public function beforeRoute($f3) {
        if (!$f3->exists('DB')) {
            $f3->set('DB', new \DB\SQL(GK_DB_DSN, GK_DB_USER, GK_DB_PASSWORD, [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;']));
        }

        // Load supported languages
        Smarty::assign('languages', LanguageService::getSupportedLanguages(true));

        // Load current user
        if ($f3->exists('SESSION.CURRENT_USER')) {
            $user = new \GeoKrety\Model\User();
            $user->load(array('id = ?', $f3->get('SESSION.CURRENT_USER')));
            if ($user->valid()) {
                \GeoKrety\Service\Smarty::assign('user', $user);
            }
        }
    }

    // public function afterRoute($f3) {
    //     \Flash::instance()->addMessage('<pre>'.$f3->get('DB')->log().'</pre>', 'warning');
    // }
}
