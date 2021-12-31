<?php

namespace GeoKrety\Controller\Admin;

use Flash;
use GeoKrety\Controller\Admin\Traits\ScriptLoader;
use GeoKrety\Controller\Base;
use GeoKrety\Service\Smarty;

class ScriptsAck extends Base {
    use ScriptLoader;

    public function get() {
        Smarty::render('extends:base_modal.tpl|dialog/admin_dialog_script_ack.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf(function ($error) use ($f3) {
            Flash::instance()->addMessage($error, 'danger');
            $f3->reroute('@admin_scripts');
        });
        $this->script->touch('acked_on_datetime');
        $this->script->save();
        \Flash::instance()->addMessage(sprintf(_('Script "%s" has been acked'), $this->script->name), 'success');
        \Sugar\Event::instance()->emit('scripts.acked', $this->script);
        $f3->reroute('@admin_scripts');
    }
}
