<?php

namespace GeoKrety\Controller;

abstract class BaseGKT extends BaseExport {
    protected array $geokrety = [];

    protected function render() {
        header('Content-Type: application/json');
        echo json_encode($this->geokrety);
    }

    abstract protected function processAddGeokret(&$geokret);

    protected function processAddMove(&$move) {
        // Not used here
    }

    protected function processPostHook() {
        // Not used here
    }
}
