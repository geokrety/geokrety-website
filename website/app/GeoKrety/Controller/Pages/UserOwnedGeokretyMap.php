<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class UserOwnedGeokretyMap extends Base {
    use \GeoKrety\Traits\UserLoader;

    public function get($f3) {
        Smarty::render('pages/user_owned_geokrety_map.tpl');
    }

    public function geojson($f3) {
        header('Content-Type: application/json; charset=utf-8');
        $sql = <<<EOT
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', COALESCE(json_agg(public.ST_AsGeoJSON(t.*)::json), '[]')::jsonb
            ) AS geojson
            FROM (
                SELECT position, gkid, name, waypoint, lat, lon, elevation, country, distance, author, author_username,
                    moved_on_datetime, caches_count, avatar_key, owner, owner, owner_username,
                    coalesce(TRUNC(EXTRACT(EPOCH FROM (NOW() - moved_on_datetime))/86400), 0) AS days
                FROM "gk_geokrety_in_caches"
                WHERE owner = ?
                ORDER BY days DESC
            ) as t;
EOT;
        $result = $f3->get('DB')->exec($sql, [$this->user->id]);
        exit($result[0]['geojson']);
    }
}
