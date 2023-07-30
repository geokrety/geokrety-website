<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\UsersAuthenticationHistory;

abstract class BaseDatatableUserAuthenticationHistory extends BaseDatatable {
    protected function getObject(): \GeoKrety\Model\Base {
        return new UsersAuthenticationHistory();
    }

    protected function getObjectName(): string {
        return 'authentications';
    }

    /**
     * @return string[]
     */
    protected function getSearchable(): array {
        return [];
    }
}
