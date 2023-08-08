<?php

namespace GeoKrety\Controller\Admin\Traits;

use GeoKrety\Model\Scripts;
use GeoKrety\Service\Smarty;

trait ScriptLoader {
    protected Scripts $script;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        $script_id = $f3->get('PARAMS.scriptid');

        $script = new Scripts();
        $this->script = $script;
        $this->filterHook();
        $script->load(['id = ?', $script_id]);
        if ($script->dry()) {
            $f3->error(404, _('This script does not exist.'));
        }
        Smarty::assign('script', $script);
    }

    protected function filterHook() {
        // empty
    }
}
