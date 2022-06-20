<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\RateLimit;
use GeoKrety\Service\Xml\GeokretyExport;

class ExportXML extends BaseExportXML {
    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        $this->checkRequiredFilter();
        $this->filtersHook();
        $this->xml = new GeokretyExport(true, $this->getCompressionMethod());
    }

    public function get(\Base $f3) {
        RateLimit::check_rate_limit_xml('API_V1_EXPORT', $this->f3->get('GET.secid'));
        $xml = $this->xml;

        $this->loadGeokretyPaginated();
        $this->loadMoves();

        // Render XML
        $xml->end();
        $xml->finish();
    }
}
