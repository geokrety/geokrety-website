<?php

namespace Geokrety\Service\Xml;

class Geokrety extends Base {
    protected $xmlGeokrety;

    public function __construct() {
        parent::__construct();
        $this->xmlGeokrety = $this->xml->addChild('geokrety');
    }

    public function addGeokret(\Geokrety\Domain\Konkret $geokret) {
        $gk = $this->xmlGeokrety->addChild('geokret');
        $gk->addAttribute('id', $geokret->id);
    }

    public function addGeokrety(array $geokrety) {
        foreach ($geokrety as $geokret) {
            $this->addGeokret($geokret);
        }
    }
}
