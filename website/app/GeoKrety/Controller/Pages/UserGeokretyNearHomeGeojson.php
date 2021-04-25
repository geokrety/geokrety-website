<?php

namespace GeoKrety\Controller;

use CurrentUserLoader;

class UserGeokretyNearHomeGeojson extends Base {
    use CurrentUserLoader;

    public function get($f3) {
        header('Content-Type: application/json; charset=utf-8');
        $sql = <<<EOT
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', json_agg(public.ST_AsGeoJSON(t.*)::json)::jsonb
            ) AS geojson
            FROM (
                SELECT position, gkid, name, waypoint, lat, lon, elevation, country, distance, author, author_username,
                    moved_on_datetime, caches_count, avatar_key,
                    coalesce(TRUNC(EXTRACT(EPOCH FROM (NOW() - moved_on_datetime))/86400), 0) AS days
                FROM "gk_geokrety_near_users_homes"
                WHERE "c_user_id" = ?
                ORDER BY days DESC, home_distance ASC
                LIMIT 500
            ) as t;
EOT;
        $result = $f3->get('DB')->exec($sql, [$this->current_user->id]);
        exit($result[0]['geojson']);
    }
}
