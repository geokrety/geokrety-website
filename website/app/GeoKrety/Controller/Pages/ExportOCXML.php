<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\RateLimit;
use GeoKrety\Service\Xml\GeokretyExportOC;

class ExportOCXML extends BaseExportXML {
    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        $this->checkRequiredFilter();
        $this->filtersHook();
        $this->xml = new GeokretyExportOC(true, $this->getCompressionMethod());
    }

    public function get(\Base $f3) {
        RateLimit::check_rate_limit_xml('API_V1_EXPORT_OC', $this->f3->get('GET.secid'));
        $xml = $this->xml;

        $this->loadGeokretyPaginated();

        // Render XML
        $xml->end();
        $xml->finish();
    }
}
