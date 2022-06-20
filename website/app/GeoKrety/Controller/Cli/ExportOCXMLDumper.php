<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Controller\Cli\Traits\XMLDumper;
use GeoKrety\Controller\ExportOCXML;

class ExportOCXMLDumper extends ExportOCXML {
    use XMLDumper;

    protected function getScriptName(): string {
        return 'GeoKrety\Controller\Cli\ExportOCXMLDumper::get';
    }
}
