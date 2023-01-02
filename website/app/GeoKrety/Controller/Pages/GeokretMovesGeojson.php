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

        $sql = <<<'EOT'
WITH w1 AS (
    SELECT gk_moves.id AS move_id, gk_moves.*, COALESCE(gk_users.username, gk_moves.username) AS author_username,
        ROW_NUMBER() OVER (ORDER BY moved_on_datetime ASC) AS step
    FROM gk_moves
    LEFT JOIN gk_users ON author = gk_users.id
    WHERE geokret=?
    ORDER BY moved_on_datetime DESC
),
max AS (
    SELECT
        GREATEST(0, LEAST(MAX(step) - ?, (? - 0) * ?)) AS page,
        MIN(step) AS min_step,
        MAX(step) AS max_step
    FROM w1
),
waypoints AS (
    SELECT w1.*, max.min_step, max.max_step
    FROM w1, max
    WHERE max.max_step - w1.step +10 >= max.page
    AND max.max_step - w1.step <= max.page + ?
),
t1 AS (
    SELECT
        w.waypoint, w.position, w.elevation, w.distance, w.country, w.step,
        COALESCE(TRUNC(EXTRACT(EPOCH FROM (moved_on_datetime - lag(moved_on_datetime) over (order by moved_on_datetime DESC)::timestamptz))/60), 0) AS minutes,
        lat, lon,
        moved_on_datetime,
        move_id,
        move_type,
        author, author_username,
        COALESCE (lag(step) over (order by moved_on_datetime DESC), step) AS next_step,
        COALESCE (lag(step) over (order by moved_on_datetime ASC), step) AS previous_step,
        w.min_step, w.max_step
    FROM waypoints AS w
    WHERE position is not NULL
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
    WHERE position is not NULL
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
        $result = $f3->get('DB')->exec($sql, [
            $this->geokret->id,
            GK_PAGINATION_GEOKRET_MOVES_MAP, $page, GK_PAGINATION_GEOKRET_MOVES,
            GK_PAGINATION_GEOKRET_MOVES_MAP,
        ]);
        exit($result[0]['geojson']);
    }
}
