<?php

namespace GeoKrety\Service\Xml;

// Most simple, just render the id
class GeokretyRuchy extends GeokretyBaseExport {
    public function __construct() {
        parent::__construct();
        $this->xml->startElement('geokrety');
    }

    public function addGeokret(\GeoKrety\Model\Geokret &$geokret) {
        $this->xml->startElement('geokret');
        $this->xml->writeAttribute('id', $geokret->gkid());
        $this->xml->endElement();
    }

    public function end() {
        $this->xml->endElement();
        parent::end();
    }
}
