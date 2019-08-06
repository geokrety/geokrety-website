<?php

namespace Geokrety\Service\Xml;

abstract class GeokretyBase extends Base {
    protected $xmlGeokrety;

    abstract public function addGeokret(\Geokrety\Domain\Konkret &$geokret);

    public function addGeokrety(array $geokrety) {
        foreach ($geokrety as $geokret) {
            $this->addGeokret($geokret);
        }
    }
}
