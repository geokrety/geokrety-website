<?php

namespace Geokrety\Service\Xml;

// Most simple, just render the id
class GeokretyRuchy extends GeokretyBase {

    public function __construct() {
        parent::__construct();
        $this->xmlGeokrety = $this->xml->addChild('geokrety');
    }

    public function addGeokret(\Geokrety\Domain\Konkret $geokret) {
        $gk = $this->xmlGeokrety->addChild('geokret');
        $gk->addAttribute('id', $geokret->id);
    }
}
