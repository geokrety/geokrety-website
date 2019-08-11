<?php

namespace GeoKrety\Service\Xml;

abstract class GeokretyBase extends Base {
    protected $xmlGeokrety;

    abstract public function addGeokret(\GeoKrety\Model\Geokret &$geokret);

    public function addGeokrety(array $geokrety) {
        foreach ($geokrety as $geokret) {
            $this->addGeokret($geokret);
        }
    }
}
