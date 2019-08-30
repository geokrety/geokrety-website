<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Controller\StaticPages;

class Help extends Base {
    public function get($f3) {

        foreach (explode(',', $f3->get('LANGUAGE')) as $lang) {
            $file = 'help-pages/'.$lang.'/help.html';
            if (file_exists(GK_SMARTY_TEMPLATES_DIR.'/'.$file)) {
                Smarty::assign('file', $file);
                Smarty::render('pages/help.tpl');
                die();
            }
        }
        StaticPages::_404($f3);
    }
}
