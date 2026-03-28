<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMaterializedViews extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
DROP MATERIALIZED VIEW IF EXISTS stats.mv_country_month_rollup;
CREATE MATERIALIZED VIEW stats.mv_country_month_rollup AS
SELECT
  from_country,
  to_country,
  year_month,
  move_count,
  unique_gk_count
FROM stats.country_pair_flows
WITH DATA;

CREATE UNIQUE INDEX idx_mv_country_month_rollup_pk
  ON stats.mv_country_month_rollup (from_country, to_country, year_month);

DROP MATERIALIZED VIEW IF EXISTS stats.mv_top_caches_global;
CREATE MATERIALIZED VIEW stats.mv_top_caches_global AS
SELECT
  waypoint_code,
  total_gk_visits,
  distinct_gks
FROM stats.v_uc10_cache_popularity
WITH DATA;

CREATE UNIQUE INDEX idx_mv_top_caches_global_pk
  ON stats.mv_top_caches_global (waypoint_code);

DROP MATERIALIZED VIEW IF EXISTS stats.mv_global_kpi;
CREATE MATERIALIZED VIEW stats.mv_global_kpi AS
SELECT
  1::SMALLINT AS singleton_key,
  (SELECT COUNT(*)::BIGINT FROM geokrety.gk_geokrety) AS total_geokrety,
  (SELECT COUNT(*)::BIGINT FROM geokrety.gk_moves) AS total_moves,
  (SELECT COALESCE(SUM(km_distance), 0)::NUMERIC(14,3) FROM geokrety.gk_moves WHERE km_distance IS NOT NULL) AS total_km,
  (SELECT COUNT(*)::BIGINT FROM geokrety.gk_users) AS total_users,
  clock_timestamp() AS computed_at
WITH DATA;

CREATE UNIQUE INDEX idx_mv_global_kpi_pk
  ON stats.mv_global_kpi (singleton_key);
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP MATERIALIZED VIEW IF EXISTS stats.mv_global_kpi;
DROP MATERIALIZED VIEW IF EXISTS stats.mv_top_caches_global;
DROP MATERIALIZED VIEW IF EXISTS stats.mv_country_month_rollup;
SQL
        );
    }
}
