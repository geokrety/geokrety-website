<?php

namespace GeoKrety\Controller\Admin;

use Flash;
use GeoKrety\Controller\Admin\Traits\ScriptLoader;
use GeoKrety\Controller\Base;
use GeoKrety\Service\Smarty;

class ScriptsUnlock extends Base {
    use ScriptLoader;

    public function get() {
        Smarty::render('extends:base_modal.tpl|dialog/admin_dialog_script_unlock.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf(function ($error) use ($f3) {
            Flash::instance()->addMessage($error, 'danger');
            $f3->reroute('@admin_scripts');
        });
        $this->script->locked_on_datetime = null;
        $this->script->acked_on_datetime = null;
        $this->script->save();
        \Flash::instance()->addMessage(sprintf(_('Script "%s" has been unlocked'), $this->script->name), 'success');
        \Sugar\Event::instance()->emit('scripts.unlocked', $this->script);
        $f3->reroute('@admin_scripts');
    }
}
