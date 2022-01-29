<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

abstract class BaseDialog extends Base {
    abstract protected function template(): string;

    public function get(\Base $f3) {
        Smarty::render(sprintf('extends:full_screen_modal.tpl|dialog/%s.tpl', $this->template()));
    }

    public function get_ajax(\Base $f3) {
        Smarty::render(sprintf('extends:base_modal.tpl|dialog/%s.tpl', $this->template()));
    }
}
