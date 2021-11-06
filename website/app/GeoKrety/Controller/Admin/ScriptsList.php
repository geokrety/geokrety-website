<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Base;
use GeoKrety\Model\Scripts as Script;
use GeoKrety\Service\Smarty;

class ScriptsList extends Base {
    public function get() {
        $script = new Script();
        $options = [
            'order' => 'locked_on_datetime DESC, name ASC',
        ];
        $scripts = $script->find(null, $options);
        Smarty::assign('scripts', $scripts);

        Smarty::render('admin/pages/scripts.tpl');
    }
}
