<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Controller\Cli\Traits\XMLDumper;
use GeoKrety\Controller\Export2XML;

class Export2XMLDumper extends Export2XML {
    use XMLDumper;

    protected function getScriptName(): string {
        return 'GeoKrety\Controller\Cli\Export2XMLDumper::get';
    }
}
