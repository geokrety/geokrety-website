<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateWaypointRegistry extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE TABLE stats.waypoints (
  id BIGSERIAL PRIMARY KEY,
  waypoint_code VARCHAR(20) NOT NULL,
  source CHAR(2) NOT NULL DEFAULT 'UK',
  lat DOUBLE PRECISION,
  lon DOUBLE PRECISION,
  country CHAR(2),
  first_seen_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  CONSTRAINT uq_waypoints_code UNIQUE (waypoint_code),
  CONSTRAINT chk_waypoints_source CHECK (source IN ('GC', 'OC', 'UK'))
);

COMMENT ON TABLE stats.waypoints IS 'Canonical deduplicated waypoint registry; each distinct waypoint code appears exactly once';
COMMENT ON COLUMN stats.waypoints.waypoint_code IS 'Uppercase normalised waypoint identifier, e.g. GC1A2B3, OKXXXX';
COMMENT ON COLUMN stats.waypoints.source IS 'Provenance: GC=geocaching.com seed, OC=opencaching seed, UK=first seen in move stream';
COMMENT ON COLUMN stats.waypoints.country IS 'ISO 3166-1 alpha-2 country code derived from waypoint seed tables; may be NULL for UK-sourced';

CREATE INDEX IF NOT EXISTS idx_waypoints_country
  ON stats.waypoints (country)
  WHERE country IS NOT NULL;

CREATE OR REPLACE VIEW stats.v_waypoints_source_union AS
SELECT
  UPPER(waypoint) AS waypoint_code,
  'GC'::CHAR(2) AS source,
  lat::DOUBLE PRECISION AS lat,
  lon::DOUBLE PRECISION AS lon,
  UPPER(country)::CHAR(2) AS country
FROM geokrety.gk_waypoints_gc
WHERE waypoint IS NOT NULL
  AND BTRIM(waypoint) <> ''

UNION ALL

SELECT
  UPPER(waypoint) AS waypoint_code,
  'OC'::CHAR(2) AS source,
  lat::DOUBLE PRECISION AS lat,
  lon::DOUBLE PRECISION AS lon,
  UPPER(country)::CHAR(2) AS country
FROM geokrety.gk_waypoints_oc
WHERE waypoint IS NOT NULL
  AND BTRIM(waypoint) <> '';

COMMENT ON VIEW stats.v_waypoints_source_union IS 'Union of GC and OC waypoint tables for seeding and diagnostics; does not deduplicate';

CREATE OR REPLACE FUNCTION stats.fn_seed_waypoints()
RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
  v_inserted BIGINT := 0;
BEGIN
  WITH prioritised_waypoints AS (
    SELECT DISTINCT ON (source_union.waypoint_code)
      source_union.waypoint_code,
      source_union.source,
      source_union.lat,
      source_union.lon,
      source_union.country
    FROM stats.v_waypoints_source_union AS source_union
    ORDER BY
      source_union.waypoint_code,
      CASE source_union.source
        WHEN 'GC' THEN 0
        WHEN 'OC' THEN 1
        ELSE 2
      END,
      source_union.country,
      source_union.lat,
      source_union.lon
  )
  INSERT INTO stats.waypoints (
    waypoint_code,
    source,
    lat,
    lon,
    country,
    first_seen_at
  )
  SELECT
    prioritised_waypoints.waypoint_code,
    prioritised_waypoints.source,
    prioritised_waypoints.lat,
    prioritised_waypoints.lon,
    prioritised_waypoints.country,
    now()
  FROM prioritised_waypoints
  ON CONFLICT (waypoint_code) DO NOTHING;

  GET DIAGNOSTICS v_inserted = ROW_COUNT;

  INSERT INTO stats.job_log (job_name, status, metadata, started_at, completed_at)
  VALUES (
    'fn_seed_waypoints',
    'completed',
    jsonb_build_object('inserted', v_inserted),
    now(),
    now()
  );

  RETURN v_inserted;
END;
$$;

SELECT stats.fn_seed_waypoints();
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP FUNCTION IF EXISTS stats.fn_seed_waypoints();
DROP VIEW IF EXISTS stats.v_waypoints_source_union;
DROP TABLE IF EXISTS stats.waypoints;
SQL
        );
    }
}
