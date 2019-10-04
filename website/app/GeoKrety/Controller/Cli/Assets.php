<?php

namespace GeoKrety\Controller\Cli;

class Assets {
    public function clear() {
        \Assets::instance()->clear();
        echo "\e[0;32mAssets cleared\e[0m".PHP_EOL;
    }
}
