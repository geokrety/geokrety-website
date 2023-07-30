<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Move;

abstract class BaseDatatableMoves extends BaseDatatable {
    protected function getObject(): \GeoKrety\Model\Base {
        return new Move();
    }

    protected function getObjectName(): string {
        return 'move';
    }

    /**
     * @return string[]
     */
    protected function getSearchable(): array {
        return ['gkid', 'name'];
    }
}
