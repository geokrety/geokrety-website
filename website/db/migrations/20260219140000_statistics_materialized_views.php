<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StatisticsMaterializedViews extends AbstractMigration {
    public function up(): void {
        // Create materialized view for country statistics with yearly trends
        $this->execute('
            CREATE MATERIALIZED VIEW geokrety.gk_statistics_country_trends AS
            WITH years AS (
                SELECT generate_series(2007, EXTRACT(YEAR FROM CURRENT_DATE)::integer) AS year
            ),
            country_yearly_counts AS (
                SELECT
                    y.year,
                    c.country,
                    COALESCE(direct_counts.count, 0) AS count
                FROM years y
                CROSS JOIN (
                    SELECT DISTINCT lower(country) AS country
                    FROM geokrety.gk_moves
                    WHERE country IS NOT NULL
                    AND move_type IN (0, 3, 5)
                ) c
                LEFT JOIN (
                    SELECT
                        EXTRACT(YEAR FROM m.moved_on_datetime)::integer AS year,
                        lower(m.country) AS country,
                        COUNT(DISTINCT m.geokret) AS count
                    FROM geokrety.gk_moves m
                    WHERE m.country IS NOT NULL
                    AND m.move_type IN (0, 3, 5)
                    GROUP BY EXTRACT(YEAR FROM m.moved_on_datetime), lower(m.country)
                ) direct_counts ON y.year = direct_counts.year AND c.country = direct_counts.country
            ),
            country_yearly_dip_counts AS (
                SELECT
                    EXTRACT(YEAR FROM m.moved_on_datetime)::integer AS year,
                    lower(m.country) AS country,
                    COUNT(DISTINCT m.geokret) AS count
                FROM geokrety.gk_moves m
                WHERE m.country IS NOT NULL
                AND m.move_type = 5
                GROUP BY EXTRACT(YEAR FROM m.moved_on_datetime), lower(m.country)
            ),
            current_stats AS (
                SELECT
                    lower(m.country) AS country,
                    COUNT(DISTINCT m.geokret) AS geokret_count,
                    ROUND(COUNT(DISTINCT m.geokret)::numeric * 100.0 / NULLIF((SELECT COUNT(DISTINCT geokret) FROM geokrety.gk_moves WHERE country IS NOT NULL), 0), 2) AS percentage
                FROM geokrety.gk_moves m
                INNER JOIN (
                    SELECT geokret, MAX(id) as last_move_id
                    FROM geokrety.gk_moves
                    WHERE move_type IN (0, 3, 5)
                    GROUP BY geokret
                ) latest ON m.id = latest.last_move_id
                WHERE m.country IS NOT NULL
                AND m.move_type IN (0, 3, 5)
                GROUP BY lower(m.country)
            )
            SELECT
                cs.country,
                cs.geokret_count,
                cs.percentage,
                (SELECT ARRAY_AGG(count ORDER BY year) FROM country_yearly_counts WHERE country = cs.country) AS trend_counts,
                (SELECT ARRAY_AGG(count ORDER BY year) FROM country_yearly_dip_counts WHERE country = cs.country) AS trend_dip_counts
            FROM current_stats cs
            ORDER BY cs.geokret_count DESC;
        ');

        // Create index on country for faster lookups
        $this->execute('
            CREATE UNIQUE INDEX idx_gk_statistics_country_trends_country
            ON geokrety.gk_statistics_country_trends (country);
        ');

        // Add comment to document the materialized view
        $this->execute("
            COMMENT ON MATERIALIZED VIEW geokrety.gk_statistics_country_trends IS
            'Materialized view containing country statistics with all-time trend data. Refresh periodically to update stats.';
        ");

        // Populate the materialized view immediately
        $this->execute('
            REFRESH MATERIALIZED VIEW geokrety.gk_statistics_country_trends;
        ');
    }

    public function down(): void {
        // Drop the materialized view
        $this->execute('
            DROP MATERIALIZED VIEW IF EXISTS geokrety.gk_statistics_country_trends;
        ');
    }
}
