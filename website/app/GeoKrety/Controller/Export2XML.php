<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Xml\GeokretyExport2;

class Export2XML extends BaseExportXML {
    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        $this->addOneOfRequiredFilter(['userid', 'gkid', 'wpt', 'coordinates']);

        $this->checkRequiredFilter();
        $this->xml = new GeokretyExport2(true, $this->f3->get('GET.compress'));
    }

    public function get(\Base $f3) {
        $xml = $this->xml;
        $this->loadGeokretyPaginated();

        // Render XML
        $xml->end();
        $xml->finish();
    }
}
