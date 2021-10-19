<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Base;
use GeoKrety\Model\Scripts as Script;
use GeoKrety\Service\Smarty;

class Scripts extends Base {
    public function get() {
        $script = new Script();
        $options = [
            'order' => 'locked_datetime DESC, name ASC',
        ];
        $scripts = $script->find(null, $options);
        Smarty::assign('scripts', $scripts);

        Smarty::render('admin/pages/scripts.tpl');
    }

    public function unlock(\Base $f3) {
        $script_id = $f3->get('PARAMS.scriptid');
        $script = new Script();
        $script->load(['id = ?', $script_id]);
        if ($script->dry()) {
            \Flash::instance()->addMessage(sprintf(_('No such script %d found'), $script_id), 'danger');
            $f3->reroute('@admin_scripts');
        }
        $script->locked_datetime = null;
        $script->save();
        \Flash::instance()->addMessage(sprintf(_('Script "%s" has been unlocked'), $script->name), 'success');
        $f3->reroute('@admin_scripts');
    }
}
