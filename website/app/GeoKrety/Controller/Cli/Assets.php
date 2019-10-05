<?php

namespace GeoKrety\Controller\Cli;

class Assets {
    public function clear() {
        \Assets::instance()->clear();
        echo sprintf("\e[0;32mAssets cleared. host: %s\e[0m", gethostname()).PHP_EOL;
    }
}
