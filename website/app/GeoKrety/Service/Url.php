<?php

namespace GeoKrety\Service;

class Url extends \Prefab {
    public static function getGoto(string $alias = 'home') {
        $f3 = \Base::instance();
        $query = [
            'goto' => urlencode($f3->get('ALIAS')),
            'params' => urlencode(base64_encode($f3->serialize($f3->get('PARAMS')))),
            'query' => urlencode(base64_encode($f3->serialize($f3->get('GET')))),
        ];

        return GK_SITE_BASE_SERVER_URL.\Base::instance()->alias($alias, null, $query);
    }
}
