<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateWaypointSnapshotFunctions extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION stats.fn_snapshot_waypoints(
  p_period daterange DEFAULT NULL
)
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_rows BIGINT := 0;
  v_completed_at TIMESTAMPTZ;
  v_elapsed_ms BIGINT := 0;
  v_period_start TIMESTAMPTZ;
  v_period_end TIMESTAMPTZ;
  v_waypoint_code VARCHAR;
  v_last_rows BIGINT := 0;
BEGIN
  v_period_start := CASE WHEN p_period IS NULL THEN NULL ELSE lower(p_period)::TIMESTAMPTZ END;
  v_period_end := CASE WHEN p_period IS NULL THEN NULL ELSE upper(p_period)::TIMESTAMPTZ END;

  IF p_period IS NULL THEN
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
  ELSE
    DROP TABLE IF EXISTS tmp_snapshot_touched_waypoints;

    CREATE TEMP TABLE tmp_snapshot_touched_waypoints ON COMMIT DROP AS
    SELECT DISTINCT UPPER(BTRIM(m.waypoint)) AS waypoint_code
    FROM geokrety.gk_moves m
    WHERE m.waypoint IS NOT NULL
      AND BTRIM(m.waypoint) <> ''
      AND m.move_type <> 2
      AND m.moved_on_datetime >= v_period_start
      AND m.moved_on_datetime < v_period_end;

    FOR v_waypoint_code IN
      SELECT tw.waypoint_code
      FROM tmp_snapshot_touched_waypoints tw
      ORDER BY tw.waypoint_code
    LOOP
      WITH ranked_waypoint AS (
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
          MIN(m.moved_on_datetime) OVER () AS first_seen_at,
          ROW_NUMBER() OVER (
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
          AND UPPER(BTRIM(m.waypoint)) = v_waypoint_code
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
        ranked_waypoint.waypoint_code,
        'UK'::CHAR(2) AS source,
        ranked_waypoint.lat,
        ranked_waypoint.lon,
        ranked_waypoint.country,
        ranked_waypoint.first_seen_at
      FROM ranked_waypoint
      WHERE ranked_waypoint.representative_rank = 1
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

      GET DIAGNOSTICS v_last_rows = ROW_COUNT;
      v_rows := v_rows + v_last_rows;
    END LOOP;
  END IF;

  IF p_period IS NULL THEN
    GET DIAGNOSTICS v_rows = ROW_COUNT;
  END IF;

  v_completed_at := clock_timestamp();
  v_elapsed_ms := (EXTRACT(EPOCH FROM (v_completed_at - v_started_at)) * 1000)::BIGINT;

  RAISE INFO 'fn_snapshot_waypoints completed in % ms (requested_period=% rows=%)', v_elapsed_ms, p_period, v_rows;

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
    jsonb_build_object('rows_affected', v_rows, 'requested_period', p_period, 'timing_ms', v_elapsed_ms),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_rows;
EXCEPTION WHEN OTHERS THEN
  v_completed_at := clock_timestamp();
  v_elapsed_ms := (EXTRACT(EPOCH FROM (v_completed_at - v_started_at)) * 1000)::BIGINT;

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
    jsonb_build_object('error', SQLERRM, 'requested_period', p_period, 'timing_ms', v_elapsed_ms),
    v_started_at,
    clock_timestamp()
  );

  RAISE INFO 'fn_snapshot_waypoints failed after % ms (requested_period=%): %', v_elapsed_ms, p_period, SQLERRM;

  RAISE;
END;
$$;

CREATE OR REPLACE FUNCTION stats.fn_snapshot_cache_visits(
  p_period daterange DEFAULT NULL
)
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_gk_rows BIGINT := 0;
  v_user_rows BIGINT := 0;
  v_completed_at TIMESTAMPTZ;
  v_elapsed_ms BIGINT := 0;
  v_period_start TIMESTAMPTZ;
  v_period_end TIMESTAMPTZ;
  v_waypoint RECORD;
  v_last_rows BIGINT := 0;
BEGIN
  v_period_start := CASE WHEN p_period IS NULL THEN NULL ELSE lower(p_period)::TIMESTAMPTZ END;
  v_period_end := CASE WHEN p_period IS NULL THEN NULL ELSE upper(p_period)::TIMESTAMPTZ END;

  IF p_period IS NULL THEN
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
  ELSE
    DROP TABLE IF EXISTS tmp_snapshot_touched_waypoints;

    CREATE TEMP TABLE tmp_snapshot_touched_waypoints ON COMMIT DROP AS
    SELECT DISTINCT w.id, w.waypoint_code
    FROM geokrety.gk_moves m
    JOIN stats.waypoints w
      ON w.waypoint_code = UPPER(BTRIM(m.waypoint))
    WHERE m.waypoint IS NOT NULL
      AND BTRIM(m.waypoint) <> ''
      AND m.move_type <> 2
      AND m.moved_on_datetime >= v_period_start
      AND m.moved_on_datetime < v_period_end;

    DELETE FROM stats.gk_cache_visits gcv
     WHERE EXISTS (
       SELECT 1
       FROM tmp_snapshot_touched_waypoints tw
       WHERE tw.id = gcv.waypoint_id
     );

    DELETE FROM stats.user_cache_visits ucv
     WHERE EXISTS (
       SELECT 1
       FROM tmp_snapshot_touched_waypoints tw
       WHERE tw.id = ucv.waypoint_id
     );

    FOR v_waypoint IN
      SELECT tw.id, tw.waypoint_code
      FROM tmp_snapshot_touched_waypoints tw
      ORDER BY tw.waypoint_code
    LOOP
      INSERT INTO stats.gk_cache_visits (
        gk_id,
        waypoint_id,
        visit_count,
        first_visited_at,
        last_visited_at
      )
      SELECT
        m.geokret,
        v_waypoint.id,
        COUNT(*)::BIGINT,
        MIN(m.moved_on_datetime),
        MAX(m.moved_on_datetime)
      FROM geokrety.gk_moves m
      WHERE m.waypoint IS NOT NULL
        AND BTRIM(m.waypoint) <> ''
        AND m.move_type <> 2
        AND UPPER(BTRIM(m.waypoint)) = v_waypoint.waypoint_code
      GROUP BY m.geokret;

      GET DIAGNOSTICS v_last_rows = ROW_COUNT;
      v_gk_rows := v_gk_rows + v_last_rows;

      INSERT INTO stats.user_cache_visits (
        user_id,
        waypoint_id,
        visit_count,
        first_visited_at,
        last_visited_at
      )
      SELECT
        m.author,
        v_waypoint.id,
        COUNT(*)::BIGINT,
        MIN(m.moved_on_datetime),
        MAX(m.moved_on_datetime)
      FROM geokrety.gk_moves m
      WHERE m.waypoint IS NOT NULL
        AND BTRIM(m.waypoint) <> ''
        AND m.author IS NOT NULL
        AND m.move_type <> 2
        AND UPPER(BTRIM(m.waypoint)) = v_waypoint.waypoint_code
      GROUP BY m.author;

      GET DIAGNOSTICS v_last_rows = ROW_COUNT;
      v_user_rows := v_user_rows + v_last_rows;
    END LOOP;
  END IF;

  IF p_period IS NULL THEN
    GET DIAGNOSTICS v_gk_rows = ROW_COUNT;
  END IF;

  IF p_period IS NULL THEN
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
  ELSE
    NULL;
  END IF;

  IF p_period IS NULL THEN
    GET DIAGNOSTICS v_user_rows = ROW_COUNT;
  END IF;

  v_completed_at := clock_timestamp();
  v_elapsed_ms := (EXTRACT(EPOCH FROM (v_completed_at - v_started_at)) * 1000)::BIGINT;

  RAISE INFO 'fn_snapshot_cache_visits completed in % ms (requested_period=% gk_rows=% user_rows=% total=%)', v_elapsed_ms, p_period, v_gk_rows, v_user_rows, v_gk_rows + v_user_rows;

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
      'rows_affected', v_gk_rows + v_user_rows,
      'requested_period', p_period,
      'timing_ms', v_elapsed_ms
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_gk_rows + v_user_rows;
EXCEPTION WHEN OTHERS THEN
  v_completed_at := clock_timestamp();
  v_elapsed_ms := (EXTRACT(EPOCH FROM (v_completed_at - v_started_at)) * 1000)::BIGINT;

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
    jsonb_build_object('error', SQLERRM, 'requested_period', p_period, 'timing_ms', v_elapsed_ms),
    v_started_at,
    clock_timestamp()
  );

  RAISE INFO 'fn_snapshot_cache_visits failed after % ms (requested_period=%): %', v_elapsed_ms, p_period, SQLERRM;

  RAISE;
END;
$$;

CREATE OR REPLACE FUNCTION stats.fn_snapshot_relations(
  p_period daterange DEFAULT NULL
)
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_gk_rows BIGINT := 0;
  v_user_rows BIGINT := 0;
  v_completed_at TIMESTAMPTZ;
  v_elapsed_ms BIGINT := 0;
  v_period_start TIMESTAMPTZ;
  v_period_end TIMESTAMPTZ;
BEGIN
  v_period_start := CASE WHEN p_period IS NULL THEN NULL ELSE lower(p_period)::TIMESTAMPTZ END;
  v_period_end := CASE WHEN p_period IS NULL THEN NULL ELSE upper(p_period)::TIMESTAMPTZ END;

  IF p_period IS NULL THEN
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
  ELSE
    DROP TABLE IF EXISTS tmp_snapshot_touched_geokrety;
    DROP TABLE IF EXISTS tmp_snapshot_impacted_users;

    CREATE TEMP TABLE tmp_snapshot_touched_geokrety ON COMMIT DROP AS
    SELECT DISTINCT m.geokret AS geokrety_id
    FROM geokrety.gk_moves m
    WHERE m.author IS NOT NULL
      AND m.move_type IN (0, 1, 3, 5)
      AND m.moved_on_datetime >= v_period_start
      AND m.moved_on_datetime < v_period_end;

    DELETE FROM stats.gk_related_users gru
     WHERE EXISTS (
       SELECT 1
       FROM tmp_snapshot_touched_geokrety tg
       WHERE tg.geokrety_id = gru.geokrety_id
     );

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
    JOIN tmp_snapshot_touched_geokrety touched_geokrety
      ON touched_geokrety.geokrety_id = m.geokret
    WHERE m.author IS NOT NULL
      AND m.move_type IN (0, 1, 3, 5)
    GROUP BY m.geokret, m.author;

    CREATE TEMP TABLE tmp_snapshot_impacted_users ON COMMIT DROP AS
    SELECT DISTINCT m.author AS user_id
    FROM geokrety.gk_moves m
    JOIN tmp_snapshot_touched_geokrety tg
      ON tg.geokrety_id = m.geokret
    WHERE m.author IS NOT NULL
      AND m.move_type IN (0, 1, 3, 5);
  END IF;

  GET DIAGNOSTICS v_gk_rows = ROW_COUNT;

  IF p_period IS NULL THEN
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
  ELSE
    DELETE FROM stats.user_related_users uru
     WHERE EXISTS (
       SELECT 1
       FROM tmp_snapshot_impacted_users iu
       WHERE iu.user_id = uru.user_id
     );

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
    WHERE left_side.user_id IN (
      SELECT iu.user_id
      FROM tmp_snapshot_impacted_users iu
    )
    GROUP BY left_side.user_id, right_side.user_id;
  END IF;

  GET DIAGNOSTICS v_user_rows = ROW_COUNT;

  v_completed_at := clock_timestamp();
  v_elapsed_ms := (EXTRACT(EPOCH FROM (v_completed_at - v_started_at)) * 1000)::BIGINT;

  RAISE INFO 'fn_snapshot_relations completed in % ms (requested_period=% gk_rows=% user_rows=% total=%)', v_elapsed_ms, p_period, v_gk_rows, v_user_rows, v_gk_rows + v_user_rows;

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
      'rows_affected', v_gk_rows + v_user_rows,
      'requested_period', p_period,
      'timing_ms', v_elapsed_ms
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_gk_rows + v_user_rows;
EXCEPTION WHEN OTHERS THEN
  v_completed_at := clock_timestamp();
  v_elapsed_ms := (EXTRACT(EPOCH FROM (v_completed_at - v_started_at)) * 1000)::BIGINT;

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
    jsonb_build_object('error', SQLERRM, 'requested_period', p_period, 'timing_ms', v_elapsed_ms),
    v_started_at,
    clock_timestamp()
  );

  RAISE INFO 'fn_snapshot_relations failed after % ms (requested_period=%): %', v_elapsed_ms, p_period, SQLERRM;

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
  v_completed_at TIMESTAMPTZ;
  v_elapsed_ms BIGINT := 0;
BEGIN
  v_rows := v_rows + stats.fn_snapshot_waypoints(p_period);
  v_rows := v_rows + stats.fn_snapshot_cache_visits(p_period);
  v_rows := v_rows + stats.fn_snapshot_relations(p_period);

  v_completed_at := clock_timestamp();
  v_elapsed_ms := (EXTRACT(EPOCH FROM (v_completed_at - v_started_at)) * 1000)::BIGINT;

  RAISE INFO 'fn_snapshot_relationship_tables completed in % ms (requested_period=% rows=%)', v_elapsed_ms, p_period, v_rows;

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
      ),
      'timing_ms', v_elapsed_ms
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_rows;
EXCEPTION WHEN OTHERS THEN
  v_completed_at := clock_timestamp();
  v_elapsed_ms := (EXTRACT(EPOCH FROM (v_completed_at - v_started_at)) * 1000)::BIGINT;

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
    jsonb_build_object('error', SQLERRM, 'requested_period', p_period, 'timing_ms', v_elapsed_ms),
    v_started_at,
    clock_timestamp()
  );

  RAISE INFO 'fn_snapshot_relationship_tables failed after % ms (requested_period=%): %', v_elapsed_ms, p_period, SQLERRM;

  RAISE;
END;
$$;
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP FUNCTION IF EXISTS stats.fn_snapshot_relationship_tables(daterange);
DROP FUNCTION IF EXISTS stats.fn_snapshot_relations(daterange);
DROP FUNCTION IF EXISTS stats.fn_snapshot_relations();
DROP FUNCTION IF EXISTS stats.fn_snapshot_cache_visits(daterange);
DROP FUNCTION IF EXISTS stats.fn_snapshot_cache_visits();
DROP FUNCTION IF EXISTS stats.fn_snapshot_waypoints(daterange);
DROP FUNCTION IF EXISTS stats.fn_snapshot_waypoints();
SQL
        );
    }
}
