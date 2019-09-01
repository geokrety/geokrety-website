<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Service\Smarty;

class GetText {
    public function buildTranslations() {
        if (exec('find app/languages/ -name \*.po -execdir msgfmt -v messages.po -o messages.mo \;') === null) {
            echo "\e[0;31mError building .mo files to .po\e[0m".PHP_EOL;
        } else {
            echo "\e[0;32mTranslation files built successfully\e[0m".PHP_EOL;
        }
    }
}
