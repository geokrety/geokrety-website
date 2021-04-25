<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Xml\GeokretyExportOC;

class ExportOCXML extends BaseExportXML {
    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        //$this->addOneOfRequiredFilter(['userid', 'gkid', 'wpt']);

        $this->checkRequiredFilter();
        $this->xml = new GeokretyExportOC(true, $this->f3->get('GET.compress'));
    }

    public function get(\Base $f3) {
        $xml = $this->xml;

        $this->loadGeokretyPaginated();

        // Render XML
        $xml->end();
        $xml->finish();
    }
}
