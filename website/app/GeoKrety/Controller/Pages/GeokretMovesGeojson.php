<?php

namespace GeoKrety\Controller;

use GeoKrety\Traits\GeokretLoader;

class GeokretMovesGeojson extends Base {
    use GeokretLoader;

    public function get($f3) {
        header('Content-Type: application/json; charset=utf-8');
        $page = $f3->get('PARAMS.page');
        if (!ctype_digit(strval($page)) || $page == 0) {
            $page = 1;
        }
        $start = ($page - 1) * GK_PAGINATION_GEOKRET_MOVES;
        $per_page = GK_PAGINATION_GEOKRET_MOVES;

        $sql = <<<EOT
WITH waypoints AS (
    SELECT gk_moves.id AS move_id, gk_moves.*, COALESCE(gk_users.username, gk_moves.username) AS author_username,
        ROW_NUMBER() OVER (ORDER BY moved_on_datetime ASC) AS step
    FROM gk_moves
    LEFT JOIN gk_users ON author = gk_users.id
    WHERE geokret=?
    AND position is not NULL
    ORDER BY moved_on_datetime DESC
    LIMIT ?
    OFFSET ?
),
t1 AS (
    SELECT
        waypoint, position, elevation, distance, country, step,
        COALESCE(TRUNC(EXTRACT(EPOCH FROM (moved_on_datetime - lag(moved_on_datetime) over (order by moved_on_datetime DESC)::timestamptz))/60), 0) AS minutes,
        lat, lon,
        moved_on_datetime,
        move_id,
        move_type,
        author, author_username,
        COALESCE (lag(step) over (order by moved_on_datetime DESC), step) AS next_step,
        COALESCE (lag(step) over (order by moved_on_datetime ASC), step) AS previous_step
    FROM waypoints
),
t AS (
    select public.ST_AsGeoJSON(t1.*) AS step
    from t1
),
f AS (
    SELECT
    public.ST_AsGeoJSON(
        public.ST_MakeLine(
            public.ST_MakePoint(
                public.ST_X(public.ST_AsEWKT(waypoints.position::public.geometry)),
                public.ST_Y(public.ST_AsEWKT(waypoints.position::public.geometry)),
                step
            )
        )
    )
    AS line
    FROM waypoints
),
features AS (
        SELECT t.step AS feat
        FROM t
        UNION
        SELECT f.line
        FROM f
)
    SELECT json_build_object(
        'type', 'FeatureCollection',
        'features', COALESCE(json_agg(features.feat::json), '[]')::jsonb
    ) AS geojson
    FROM features
EOT;
        $result = $f3->get('DB')->exec($sql, [$this->geokret->id, 500, $start]);
        exit($result[0]['geojson']);
    }
}
