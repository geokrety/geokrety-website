<?php

namespace GeoKrety\Controller\API\v1;

use GeoKrety\Controller\API\BaseJson;

class Statistics extends BaseJson {
    /**
     * Get GeoKrety count per country (currently in cache)
     * Uses materialized view for performance.
     */
    public function geokrety_per_country() {
        $db = \Base::instance()->get('DB');
        $ttl = GK_SITE_CACHE_TTL_STATISTICS_COUNTRY_TRENDS;

        // Query the materialized view with JSON construction in SQL
        $sql = <<<SQL
SELECT json_build_object(
    'data', COALESCE(json_agg(
        json_build_object(
            'country', country,
            'count', COALESCE(geokret_count, 0),
            'percentage', COALESCE(percentage, 0),
            'trend', COALESCE(array_to_json(trend_counts), '[]'::json),
            'dip_trend', COALESCE(array_to_json(trend_dip_counts), '[]'::json)
        )
        ORDER BY geokret_count DESC
    ), '[]'::json),
    'ttl', {$ttl}
)::text AS response
FROM geokrety.gk_statistics_country_trends
SQL;

        $result = $db->exec($sql, null, 0);

        echo $result[0]['response'] ?? '{"data":[],"ttl":0}';
    }

    /**
     * Get total moves per country.
     */
    public function moves_per_country() {
        $db = \Base::instance()->get('DB');
        $ttl = GK_SITE_CACHE_TTL_STATISTICS_COUNTRIES;

        $sql = <<<SQL
SELECT json_build_object(
    'data', COALESCE(json_agg(
        json_build_object(
            'country', country,
            'count', count,
            'last_activity', last_activity
        )
        ORDER BY count DESC
    ), '[]'::json),
    'ttl', {$ttl}
)::text AS response
FROM (
    SELECT
        lower(country) AS country,
        COUNT(*) AS count,
        TO_CHAR(MAX(moved_on_datetime), 'YYYY-MM-DD"T"HH24:MI:SS"Z"') AS last_activity
    FROM gk_moves
    WHERE country IS NOT NULL
    AND move_type IN (0, 1, 3, 5)
    GROUP BY lower(country)
) stats
SQL;

        $result = $db->exec($sql, null, 0); // Don't cache here, rely on materialized view caching

        echo $result[0]['response'] ?? '{"data":[],"ttl":0}';
    }

    /**
     * Get user registrations over time (monthly aggregation).
     */
    public function users_registrations() {
        $db = \Base::instance()->get('DB');
        $ttl = GK_SITE_CACHE_TTL_STATISTICS_REGISTRATIONS;

        $sql = <<<SQL
SELECT json_build_object(
    'data', COALESCE(json_agg(
        json_build_object(
            'date', date,
            'count', count,
            'cumulative', cumulative
        )
        ORDER BY month
    ), '[]'::json),
    'ttl', {$ttl}
)::text AS response
FROM (
    SELECT
        DATE_TRUNC('month', joined_on_datetime) AS month,
        TO_CHAR(DATE_TRUNC('month', joined_on_datetime), 'YYYY-MM-DD') AS date,
        COUNT(*) AS count,
        SUM(COUNT(*)) OVER (ORDER BY DATE_TRUNC('month', joined_on_datetime)) AS cumulative
    FROM gk_users
    WHERE joined_on_datetime IS NOT NULL
    GROUP BY DATE_TRUNC('month', joined_on_datetime)
) stats
SQL;

        $result = $db->exec($sql, null, $ttl);

        echo $result[0]['response'] ?? '{"data":[],"ttl":0}';
    }

    /**
     * Get GeoKrety registrations over time (monthly aggregation).
     */
    public function geokrety_registrations() {
        $db = \Base::instance()->get('DB');
        $ttl = GK_SITE_CACHE_TTL_STATISTICS_REGISTRATIONS;

        $sql = <<<SQL
SELECT json_build_object(
    'data', COALESCE(json_agg(
        json_build_object(
            'date', date,
            'count', count,
            'cumulative', cumulative
        )
        ORDER BY month
    ), '[]'::json),
    'ttl', {$ttl}
)::text AS response
FROM (
    SELECT
        DATE_TRUNC('month', created_on_datetime) AS month,
        TO_CHAR(DATE_TRUNC('month', created_on_datetime), 'YYYY-MM-DD') AS date,
        COUNT(*) AS count,
        SUM(COUNT(*)) OVER (ORDER BY DATE_TRUNC('month', created_on_datetime)) AS cumulative
    FROM gk_geokrety
    WHERE created_on_datetime IS NOT NULL
    GROUP BY DATE_TRUNC('month', created_on_datetime)
) stats
SQL;

        $result = $db->exec($sql, null, $ttl);

        echo $result[0]['response'] ?? '{"data":[],"ttl":0}';
    }

    /**
     * Get activity snapshot for KPI tiles.
     * Optional query parameter: min_moves (default 10) for active countries.
     */
    public function activity_snapshot() {
        $db = \Base::instance()->get('DB');
        $ttl = GK_SITE_CACHE_TTL_STATISTICS_COUNTRIES;
        $minMoves = (int) \Base::instance()->get('REQUEST.min_moves');
        if ($minMoves <= 0) {
            $minMoves = 10;
        }

        $sql = <<<SQL
SELECT json_build_object(
    'data', json_build_object(
        'active_users', json_build_object(
            'days_30', (SELECT COUNT(DISTINCT author)
                FROM gk_moves
                WHERE author IS NOT NULL
                AND moved_on_datetime >= NOW() - INTERVAL '30 days'
                AND move_type IN (0, 1, 3, 5)
            ),
            'days_90', (SELECT COUNT(DISTINCT author)
                FROM gk_moves
                WHERE author IS NOT NULL
                AND moved_on_datetime >= NOW() - INTERVAL '90 days'
                AND move_type IN (0, 1, 3, 5)
            ),
            'days_365', (SELECT COUNT(DISTINCT author)
                FROM gk_moves
                WHERE author IS NOT NULL
                AND moved_on_datetime >= NOW() - INTERVAL '365 days'
                AND move_type IN (0, 1, 3, 5)
            )
        ),
        'active_geokrety', json_build_object(
            'days_30', (SELECT COUNT(DISTINCT geokret)
                FROM gk_moves
                WHERE geokret IS NOT NULL
                AND moved_on_datetime >= NOW() - INTERVAL '30 days'
                AND move_type IN (0, 1, 3, 5)
            ),
            'days_90', (SELECT COUNT(DISTINCT geokret)
                FROM gk_moves
                WHERE geokret IS NOT NULL
                AND moved_on_datetime >= NOW() - INTERVAL '90 days'
                AND move_type IN (0, 1, 3, 5)
            ),
            'days_365', (SELECT COUNT(DISTINCT geokret)
                FROM gk_moves
                WHERE geokret IS NOT NULL
                AND moved_on_datetime >= NOW() - INTERVAL '365 days'
                AND move_type IN (0, 1, 3, 5)
            )
        ),
        'countries_active_30', json_build_object(
            'count', (SELECT COUNT(*)
                FROM (
                    SELECT lower(country) AS country
                    FROM gk_moves
                    WHERE country IS NOT NULL
                    AND moved_on_datetime >= NOW() - INTERVAL '30 days'
                    AND move_type IN (0, 1, 3, 5)
                    GROUP BY lower(country)
                    HAVING COUNT(*) >= {$minMoves}
                ) active_countries
            ),
            'min_moves', {$minMoves}
        )
    ),
    'ttl', {$ttl}
)::text AS response
SQL;

        $result = $db->exec($sql, null, $ttl);

        echo $result[0]['response'] ?? '{"data":{},"ttl":0}';
    }

    /**
     * Get move type distribution
     * Optional query parameter: year (filters to specific year, or 'all' for all time).
     */
    public function move_type_distribution() {
        $db = \Base::instance()->get('DB');
        $year = \Base::instance()->get('REQUEST.year');
        $ttl = GK_SITE_CACHE_TTL_STATISTICS_COUNTRIES;

        $whereClause = '';
        if ($year && $year !== 'all' && is_numeric($year)) {
            $whereClause = 'WHERE EXTRACT(YEAR FROM moved_on_datetime) = '.(int) $year;
        }

        $sql = <<<SQL
SELECT json_build_object(
    'data', COALESCE(json_agg(
        json_build_object(
            'type', type,
            'label', label,
            'count', count,
            'percentage', percentage
        )
        ORDER BY type
    ), '[]'::json),
    'ttl', {$ttl}
)::text AS response
FROM (
    SELECT
        move_type AS type,
        CASE move_type
            WHEN 0 THEN 'Drop'
            WHEN 1 THEN 'Grab'
            WHEN 2 THEN 'Comment'
            WHEN 3 THEN 'Met'
            WHEN 4 THEN 'Archived'
            WHEN 5 THEN 'Dip'
            WHEN 6 THEN 'Seen'
            ELSE 'Unknown'
        END AS label,
        COUNT(*) AS count,
        ROUND(COUNT(*)::numeric * 100.0 / NULLIF(SUM(COUNT(*)) OVER (), 0), 2) AS percentage
    FROM gk_moves
    {$whereClause}
    GROUP BY move_type
) stats
SQL;

        $result = $db->exec($sql, null, $ttl);

        echo $result[0]['response'] ?? '{"data":[],"ttl":0}';
    }

    /**
     * Get GeoKret type distribution
     * Optional query parameter: year (filters to GeoKrety created in specific year, or 'all' for all time).
     */
    public function geokrety_type_distribution() {
        $db = \Base::instance()->get('DB');
        $year = \Base::instance()->get('REQUEST.year');

        $whereClause = '';
        if ($year && $year !== 'all' && is_numeric($year)) {
            $whereClause = 'WHERE EXTRACT(YEAR FROM created_on_datetime) = '.(int) $year;
        }

        $sql = <<<SQL
SELECT
    type,
    CASE type
        WHEN 0 THEN 'Traditional'
        WHEN 1 THEN 'Book/CD/DVD'
        WHEN 2 THEN 'Human'
        WHEN 3 THEN 'Coin'
        WHEN 4 THEN 'KretyPost'
        WHEN 5 THEN 'Pebble'
        WHEN 6 THEN 'Car'
        WHEN 7 THEN 'Playing Card'
        WHEN 8 THEN 'Dog tag/pet'
        WHEN 9 THEN 'Jigsaw part'
        WHEN 10 THEN 'Easter Egg'
        ELSE 'Unknown'
    END AS label,
    COUNT(*) AS count,
    ROUND(COUNT(*)::numeric * 100.0 / NULLIF(SUM(COUNT(*)) OVER (), 0), 2) AS percentage,
    ROUND(AVG(distance), 2) AS avg_distance
FROM gk_geokrety
{$whereClause}
GROUP BY type
ORDER BY type
SQL;

        $rows = $db->exec($sql, null, GK_SITE_CACHE_TTL_STATISTICS_COUNTRIES) ?: [];

        $response = [
            'data' => $rows,
            'ttl' => GK_SITE_CACHE_TTL_STATISTICS_COUNTRIES,
        ];

        echo json_encode($response);
    }

    /**
     * Get top waypoints by visits.
     */
    public function top_waypoints() {
        $db = \Base::instance()->get('DB');
        $sql = <<<'SQL'
WITH waypoint_stats AS (
    SELECT
        waypoint,
        COUNT(*) AS visit_count,
        COUNT(DISTINCT geokret) AS unique_geokrety,
        MAX(moved_on_datetime) AS last_visit
    FROM gk_moves
    WHERE waypoint IS NOT NULL
    AND move_type IN (0, 3, 5)
    GROUP BY waypoint
),
top_waypoints AS (
    SELECT
        waypoint,
        visit_count,
        unique_geokrety,
        last_visit
    FROM waypoint_stats
    ORDER BY unique_geokrety DESC
    LIMIT 50
),
year_range AS (
    SELECT generate_series(2007, EXTRACT(YEAR FROM CURRENT_DATE)::integer) AS year
),
waypoint_yearly_counts AS (
    SELECT
        waypoint,
        EXTRACT(YEAR FROM moved_on_datetime)::integer AS year,
        COUNT(DISTINCT geokret) AS visit_count
    FROM gk_moves
    WHERE waypoint IS NOT NULL
    AND move_type IN (0, 3, 5)
    AND waypoint IN (SELECT waypoint FROM top_waypoints)
    GROUP BY waypoint, EXTRACT(YEAR FROM moved_on_datetime)
),
waypoint_trends AS (
    SELECT
        tw.waypoint,
        ARRAY_AGG(COALESCE(wyc.visit_count, 0) ORDER BY yr.year) AS trend
    FROM top_waypoints tw
    CROSS JOIN year_range yr
    LEFT JOIN waypoint_yearly_counts wyc
        ON wyc.waypoint = tw.waypoint
        AND wyc.year = yr.year
    GROUP BY tw.waypoint
)
SELECT
    tw.waypoint,
    tw.visit_count,
    tw.unique_geokrety,
    TO_CHAR(tw.last_visit, 'YYYY-MM-DD"T"HH24:MI:SS"Z"') AS last_visit,
    COALESCE(wt.trend, '{}'::integer[]) AS trend
FROM top_waypoints tw
LEFT JOIN waypoint_trends wt ON tw.waypoint = wt.waypoint
ORDER BY tw.unique_geokrety DESC
SQL;

        $rows = $db->exec($sql, null, GK_SITE_CACHE_TTL_STATISTICS_COUNTRIES) ?: [];

        $response = [
            'data' => $rows,
            'ttl' => GK_SITE_CACHE_TTL_STATISTICS_COUNTRIES,
        ];

        echo json_encode($response);
    }

    /**
     * Get country activity leaderboard.
     */
    public function country_activity() {
        $db = \Base::instance()->get('DB');
        $sql = <<<'SQL'
SELECT
    lower(m.country) AS country,
    COUNT(DISTINCT m.geokret) AS unique_geokrety,
    COUNT(*) AS total_moves,
    COUNT(DISTINCT m.author) AS active_users,
    ROUND(AVG(m.distance), 2) AS avg_move_distance,
    TO_CHAR(MAX(m.moved_on_datetime), 'YYYY-MM-DD"T"HH24:MI:SS"Z"') AS last_activity
FROM gk_moves m
WHERE m.country IS NOT NULL
AND m.move_type IN (0, 1, 3, 5)
GROUP BY lower(m.country)
ORDER BY total_moves DESC
LIMIT 50
SQL;

        $rows = $db->exec($sql, null, GK_SITE_CACHE_TTL_STATISTICS_COUNTRIES) ?: [];

        $response = [
            'data' => $rows,
            'ttl' => GK_SITE_CACHE_TTL_STATISTICS_COUNTRIES,
        ];

        echo json_encode($response);
    }

    /**
     * Get picture upload trends (monthly aggregation).
     */
    public function picture_trends() {
        $db = \Base::instance()->get('DB');
        $sql = <<<'SQL'
SELECT
    TO_CHAR(DATE_TRUNC('month', uploaded_on_datetime), 'YYYY-MM-DD') AS date,
    type,
    CASE type
        WHEN 0 THEN 'GeoKret Avatar'
        WHEN 1 THEN 'Move Picture'
        WHEN 2 THEN 'User Avatar'
        ELSE 'Unknown'
    END AS type_label,
    COUNT(*) AS count
FROM gk_pictures
WHERE uploaded_on_datetime IS NOT NULL
AND uploaded_on_datetime >= DATE '2023-10-01'
GROUP BY DATE_TRUNC('month', uploaded_on_datetime), type
ORDER BY DATE_TRUNC('month', uploaded_on_datetime), type
SQL;

        $rows = $db->exec($sql, null, GK_SITE_CACHE_TTL_STATISTICS_REGISTRATIONS) ?: [];

        $response = [
            'data' => $rows,
            'ttl' => GK_SITE_CACHE_TTL_STATISTICS_REGISTRATIONS,
        ];

        echo json_encode($response);
    }

    /**
     * Get comment statistics.
     */
    public function comment_statistics() {
        $db = \Base::instance()->get('DB');
        $sql = <<<'SQL'
SELECT
    TO_CHAR(DATE_TRUNC('month', created_on_datetime), 'YYYY-MM-DD') AS date,
    type,
    CASE type
        WHEN 0 THEN 'Comment'
        WHEN 1 THEN 'Missing Report'
        ELSE 'Unknown'
    END AS type_label,
    COUNT(*) AS count
FROM gk_moves_comments
GROUP BY DATE_TRUNC('month', created_on_datetime), type
ORDER BY DATE_TRUNC('month', created_on_datetime), type
SQL;

        $rows = $db->exec($sql, null, GK_SITE_CACHE_TTL_STATISTICS_REGISTRATIONS) ?: [];

        $response = [
            'data' => $rows,
            'ttl' => GK_SITE_CACHE_TTL_STATISTICS_REGISTRATIONS,
        ];

        echo json_encode($response);
    }
}
