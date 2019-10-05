<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Service\Smarty;

class SmartyTemplates {
    public function clearCompiledTemplates() {
        $smarty = Smarty::getSmarty();
        $smarty->clearCompiledTemplate();
        echo "\e[0;32mSmarty templates cleared\e[0m".PHP_EOL;
    }

    public function compileAllTemplates() {
        $smarty = Smarty::getSmarty();
        $smarty->compileAllTemplates('.tpl', true);
        $smarty->compileAllTemplates('.tpl.js', true);
        echo sprintf("\e[0;32mSmarty templates compiled. host: %s\e[0m", gethostname()).PHP_EOL;

        if (exec('chown -R www-data.www-data '.GK_SMARTY_COMPILE_DIR) === null) {
            echo "\e[0;31mError changing templates owner to php user\e[0m".PHP_EOL;
        }
    }
}
