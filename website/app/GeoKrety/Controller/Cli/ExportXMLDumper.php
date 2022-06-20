<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Controller\Cli\Traits\XMLDumper;
use GeoKrety\Controller\ExportXML;

class ExportXMLDumper extends ExportXML {
    use XMLDumper;

    protected function getScriptName(): string {
        return 'GeoKrety\Controller\Cli\ExportXMLDumper::get';
    }
}
