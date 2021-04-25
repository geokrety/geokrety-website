<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Xml\GeokretyExport;

class ExportXML extends BaseExportXML {
    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        //$this->addOneOfRequiredFilter(['userid', 'gkid', 'wpt']);

        $this->checkRequiredFilter();
        $this->xml = new GeokretyExport(true, $this->f3->get('GET.compress'));
    }

    public function get(\Base $f3) {
        $xml = $this->xml;

        $this->loadGeokretyPaginated();
        $this->loadMoves();

        // Render XML
        $xml->end();
        $xml->finish();
    }
}
