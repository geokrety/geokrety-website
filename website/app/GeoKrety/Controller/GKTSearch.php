<?php

namespace GeoKrety\Controller;

use GeoKrety\GeokretyType;
use GeoKrety\LogType;

class GKTSearch extends BaseGKT {
    public function get(\Base $f3) {
        if (!$f3->exists('GET.lat') or !$f3->exists('GET.lon')) {
            return;
        }
        $lat = $f3->get('GET.lat');
        $lon = $f3->get('GET.lon');
        if (!is_numeric($lat) or !is_numeric($lon)) {
            return;
        }

        $this->setFilter(
            'public.ST_Intersects(position, public.ST_Buffer(public.ST_SetSRID(public.ST_MakePoint((?),(?)), 4326), ?)) AND missing = ? AND move_type IN ? AND type != ?',
            floatval($lon), floatval($lat), GK_GKT_SEARCH_DISTANCE_LIMIT / 10000, false, LogType::LOG_TYPES_THEORICALLY_IN_CACHE, GeokretyType::GEOKRETY_TYPE_HUMAN,
        );

        $this->loadGeokrety();
        $this->render();
    }

    protected function processAddGeokret(&$geokret) {
        $this->geokrety[] = [
            'id' => $geokret->gkid(),
            'n' => $geokret->name,
        ];
    }
}
