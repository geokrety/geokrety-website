<?php

namespace GeoKrety;

class Pagination extends \Pagination {
    public function getRouteKey() {
        return 'page';
    }

    public function getRouteKeyPrefix() {
        return '/page/';
    }

    public function getRoute() {
        $f3 = \Base::instance();
        $routeKey = $this->getRouteKey();
        $routeKeyPrefix = $this->getRouteKeyPrefix();
        $route = $f3->get('PATH');
        $route = preg_replace('@'.preg_quote($routeKeyPrefix.$f3->get('PARAMS.'.$routeKey)).'$@', '', $route);

        return $route;
    }

    public function getPath() {
        return $this->getRoute().$this->getRouteKeyPrefix();
    }
}
