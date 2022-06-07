<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;

abstract class BaseDatatableGeokrety extends BaseDatatable {
    protected function getObject(): \GeoKrety\Model\Base {
        return new Geokret();
    }

    protected function getObjectName(): string {
        return 'geokrety';
    }
}
