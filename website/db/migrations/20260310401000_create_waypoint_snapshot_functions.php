<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateWaypointSnapshotFunctions extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION stats.fn_snapshot_waypoints()
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_rows BIGINT := 0;
BEGIN
  WITH ranked_waypoints AS (
    SELECT
      UPPER(BTRIM(m.waypoint)) AS waypoint_code,
      CASE
        WHEN m.position IS NULL THEN NULL
        ELSE public.ST_Y(m.position::public.geometry)::DOUBLE PRECISION
      END AS lat,
      CASE
        WHEN m.position IS NULL THEN NULL
        ELSE public.ST_X(m.position::public.geometry)::DOUBLE PRECISION
      END AS lon,
      CASE
        WHEN m.country IS NULL OR BTRIM(m.country) = '' THEN NULL
        ELSE UPPER(BTRIM(m.country))::CHAR(2)
      END AS country,
      MIN(m.moved_on_datetime) OVER (PARTITION BY UPPER(BTRIM(m.waypoint))) AS first_seen_at,
      ROW_NUMBER() OVER (
        PARTITION BY UPPER(BTRIM(m.waypoint))
        ORDER BY
          CASE
            WHEN m.position IS NOT NULL OR (m.country IS NOT NULL AND BTRIM(m.country) <> '') THEN 0
            ELSE 1
          END,
          m.moved_on_datetime,
          m.id
      ) AS representative_rank
    FROM geokrety.gk_moves m
    WHERE m.waypoint IS NOT NULL
      AND BTRIM(m.waypoint) <> ''
      AND m.move_type <> 2
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
    ranked_waypoints.waypoint_code,
    'UK'::CHAR(2) AS source,
    ranked_waypoints.lat,
    ranked_waypoints.lon,
    ranked_waypoints.country,
    ranked_waypoints.first_seen_at
  FROM ranked_waypoints
  WHERE ranked_waypoints.representative_rank = 1
  ON CONFLICT (waypoint_code) DO UPDATE SET
    lat = CASE
      WHEN stats.waypoints.source = 'UK'
        THEN COALESCE(EXCLUDED.lat, stats.waypoints.lat)
      ELSE COALESCE(stats.waypoints.lat, EXCLUDED.lat)
    END,
    lon = CASE
      WHEN stats.waypoints.source = 'UK'
        THEN COALESCE(EXCLUDED.lon, stats.waypoints.lon)
      ELSE COALESCE(stats.waypoints.lon, EXCLUDED.lon)
    END,
    country = CASE
      WHEN stats.waypoints.source = 'UK'
        THEN COALESCE(EXCLUDED.country, stats.waypoints.country)
      ELSE COALESCE(stats.waypoints.country, EXCLUDED.country)
    END,
    first_seen_at = LEAST(stats.waypoints.first_seen_at, EXCLUDED.first_seen_at);

  GET DIAGNOSTICS v_rows = ROW_COUNT;

  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_snapshot_waypoints',
    'ok',
    jsonb_build_object('rows_affected', v_rows),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_rows;
EXCEPTION WHEN OTHERS THEN
  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_snapshot_waypoints',
    'error',
    jsonb_build_object('error', SQLERRM),
    v_started_at,
    clock_timestamp()
  );

  RAISE;
END;
$$;

CREATE OR REPLACE FUNCTION stats.fn_snapshot_cache_visits()
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_gk_rows BIGINT := 0;
  v_user_rows BIGINT := 0;
BEGIN
  DELETE FROM stats.gk_cache_visits;
  DELETE FROM stats.user_cache_visits;

  INSERT INTO stats.gk_cache_visits (
    gk_id,
    waypoint_id,
    visit_count,
    first_visited_at,
    last_visited_at
  )
  SELECT
    m.geokret,
    w.id,
    COUNT(*)::BIGINT,
    MIN(m.moved_on_datetime),
    MAX(m.moved_on_datetime)
  FROM geokrety.gk_moves m
  JOIN stats.waypoints w
    ON w.waypoint_code = UPPER(BTRIM(m.waypoint))
  WHERE m.waypoint IS NOT NULL
    AND BTRIM(m.waypoint) <> ''
    AND m.move_type <> 2
  GROUP BY m.geokret, w.id;

  GET DIAGNOSTICS v_gk_rows = ROW_COUNT;

  INSERT INTO stats.user_cache_visits (
    user_id,
    waypoint_id,
    visit_count,
    first_visited_at,
    last_visited_at
  )
  SELECT
    m.author,
    w.id,
    COUNT(*)::BIGINT,
    MIN(m.moved_on_datetime),
    MAX(m.moved_on_datetime)
  FROM geokrety.gk_moves m
  JOIN stats.waypoints w
    ON w.waypoint_code = UPPER(BTRIM(m.waypoint))
  WHERE m.waypoint IS NOT NULL
    AND BTRIM(m.waypoint) <> ''
    AND m.author IS NOT NULL
    AND m.move_type <> 2
  GROUP BY m.author, w.id;

  GET DIAGNOSTICS v_user_rows = ROW_COUNT;

  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_snapshot_cache_visits',
    'ok',
    jsonb_build_object(
      'gk_rows', v_gk_rows,
      'user_rows', v_user_rows,
      'rows_affected', v_gk_rows + v_user_rows
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_gk_rows + v_user_rows;
EXCEPTION WHEN OTHERS THEN
  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_snapshot_cache_visits',
    'error',
    jsonb_build_object('error', SQLERRM),
    v_started_at,
    clock_timestamp()
  );

  RAISE;
END;
$$;

CREATE OR REPLACE FUNCTION stats.fn_snapshot_relations()
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_gk_rows BIGINT := 0;
  v_user_rows BIGINT := 0;
BEGIN
  DELETE FROM stats.gk_related_users;
  DELETE FROM stats.user_related_users;

  INSERT INTO stats.gk_related_users (
    geokrety_id,
    user_id,
    interaction_count,
    first_interaction,
    last_interaction
  )
  SELECT
    m.geokret,
    m.author,
    COUNT(*)::BIGINT,
    MIN(m.moved_on_datetime),
    MAX(m.moved_on_datetime)
  FROM geokrety.gk_moves m
  WHERE m.author IS NOT NULL
    AND m.move_type IN (0, 1, 3, 5)
  GROUP BY m.geokret, m.author;

  GET DIAGNOSTICS v_gk_rows = ROW_COUNT;

  INSERT INTO stats.user_related_users (
    user_id,
    related_user_id,
    shared_geokrety_count,
    first_seen_at,
    last_seen_at
  )
  SELECT
    left_side.user_id,
    right_side.user_id AS related_user_id,
    COUNT(DISTINCT left_side.geokrety_id)::BIGINT,
    MIN(LEAST(left_side.first_interaction, right_side.first_interaction)),
    MAX(GREATEST(left_side.last_interaction, right_side.last_interaction))
  FROM stats.gk_related_users left_side
  JOIN stats.gk_related_users right_side
    ON right_side.geokrety_id = left_side.geokrety_id
   AND right_side.user_id <> left_side.user_id
  GROUP BY left_side.user_id, right_side.user_id;

  GET DIAGNOSTICS v_user_rows = ROW_COUNT;

  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_snapshot_relations',
    'ok',
    jsonb_build_object(
      'gk_rows', v_gk_rows,
      'user_rows', v_user_rows,
      'rows_affected', v_gk_rows + v_user_rows
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_gk_rows + v_user_rows;
EXCEPTION WHEN OTHERS THEN
  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_snapshot_relations',
    'error',
    jsonb_build_object('error', SQLERRM),
    v_started_at,
    clock_timestamp()
  );

  RAISE;
END;
$$;

CREATE OR REPLACE FUNCTION stats.fn_snapshot_relationship_tables(
  p_period daterange DEFAULT NULL
)
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_rows BIGINT := 0;
BEGIN
  v_rows := v_rows + stats.fn_snapshot_waypoints();
  v_rows := v_rows + stats.fn_snapshot_cache_visits();
  v_rows := v_rows + stats.fn_snapshot_relations();

  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_snapshot_relationship_tables',
    'ok',
    jsonb_build_object(
      'rows_affected', v_rows,
      'requested_period', p_period,
      'delegated_helpers', jsonb_build_array(
        'fn_snapshot_waypoints',
        'fn_snapshot_cache_visits',
        'fn_snapshot_relations'
      )
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_rows;
EXCEPTION WHEN OTHERS THEN
  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_snapshot_relationship_tables',
    'error',
    jsonb_build_object('error', SQLERRM, 'requested_period', p_period),
    v_started_at,
    clock_timestamp()
  );

  RAISE;
END;
$$;
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP FUNCTION IF EXISTS stats.fn_snapshot_relationship_tables(daterange);
DROP FUNCTION IF EXISTS stats.fn_snapshot_relations();
DROP FUNCTION IF EXISTS stats.fn_snapshot_cache_visits();
DROP FUNCTION IF EXISTS stats.fn_snapshot_waypoints();
SQL
        );
    }
}
