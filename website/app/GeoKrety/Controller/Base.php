<?php

namespace GeoKrety\Controller;

abstract class Base {
    public function beforeRoute($f3) {
        if (!$f3->exists('DB')) {
            $f3->set('DB', new \DB\SQL(GK_DB_DSN, GK_DB_USER, GK_DB_PASSWORD, [\PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8mb4;']));
        }
    }
    // public function afterRoute($f3) {
    //     \Flash::instance()->addMessage('<pre>'.$f3->get('DB')->log().'</pre>', 'warning');
    // }
}
