<?php

namespace GeoKrety\Service\Xml;

abstract class GeokretyBaseExport extends Base {
    abstract public function addGeokret(\GeoKrety\Model\Geokret &$geokret);

    public function addGeokrety(array $geokrety) {
        foreach ($geokrety as $geokret) {
            $this->addGeokret($geokret);
        }
    }
}
