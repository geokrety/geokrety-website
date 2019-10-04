<?php

namespace GeoKrety\Controller\Cli;

class GetText {
    public function buildTranslations() {
        if (exec('find ../app/languages/ -name \*.po -execdir msgfmt -v messages.po -o messages.mo \;') === null) {
            echo "\e[0;31mError building .mo files from .po\e[0m".PHP_EOL;
        } else {
            echo "\e[0;32mTranslation files built successfully\e[0m".PHP_EOL;
            echo "\e[0;33;41mWarning: `php` must be restarted for the new files to be read again\e[0m".PHP_EOL;
        }
    }
}
