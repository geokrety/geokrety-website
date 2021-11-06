<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Admin\Traits\ScriptLoader;
use GeoKrety\Controller\Base;
use GeoKrety\Service\Smarty;

class ScriptsUnlock extends Base {
    use ScriptLoader;

    public function get() {
        Smarty::render('dialog/admin_dialog_script_unlock.tpl');
    }

    public function post(\Base $f3) {
        $this->script->locked_on_datetime = null;
        $this->script->acked_on_datetime = null;
        $this->script->save();
        \Flash::instance()->addMessage(sprintf(_('Script "%s" has been unlocked'), $this->script->name), 'success');
        \Sugar\Event::instance()->emit('scripts.unlocked', $this->script);
        $f3->reroute('@admin_scripts');
    }
}
