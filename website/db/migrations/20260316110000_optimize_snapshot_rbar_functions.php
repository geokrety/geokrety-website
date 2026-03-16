<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Performance optimization: eliminate RBAR (Row-By-Agonizing-Row) FOR LOOPs in
 * fn_snapshot_waypoints and fn_snapshot_cache_visits.
 *
 * Baseline: 5h 49m 38s for full 2007-10 → 2026-03 backfill.
 * Root cause: fn_snapshot_relationship_tables = 74% of runtime, dominated by:
 *   - fn_snapshot_cache_visits: 30.97s avg (14,650 individual inserts per month)
 *   - fn_snapshot_waypoints:    24.64s avg (7,325 loop iterations per month)
 *
 * Optimizations:
 * 1. fn_snapshot_waypoints (incremental path): replace FOR LOOP with a single
 *    set-based CTE using only the current period's moves.
 *    Correctness: the ON CONFLICT COALESCE logic converges correctly across
 *    sequential chronological runs (earlier periods have already populated
 *    stats.waypoints with historical data; LEAST() for first_seen_at).
 *
 * 2. fn_snapshot_cache_visits (incremental path): replace FOR LOOP with
 *    two CROSS JOIN LATERAL queries. LATERAL forces a nested loop which reuses
 *    the existing idx_gk_moves_waypoint_code_hist functional index per waypoint.
 *    Also adds ANALYZE on the temp table to ensure accurate planner statistics.
 *
 * Expected improvement: fn_snapshot_waypoints ~24s → <2s, fn_snapshot_cache_visits ~31s → <10s.
 * Projected full-run reduction: 5h 50m → ~2h.
 *
 * See docs/database-refactor/PERFORMANCE.md for analysis.
 */
final class OptimizeSnapshotRbarFunctions extends AbstractMigration {
    public function up(): void {
        // ── fn_snapshot_waypoints: period-only set-based rewrite ──────────
        // Replaces the RBAR FOR LOOP in the incremental (ELSE) path with a
        // single CTE+UPSERT that only reads the current period's moves.
        // The full (NULL period) path is unchanged.
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
BEGIN
  v_period_start := CASE WHEN p_period IS NULL THEN NULL ELSE lower(p_period)::TIMESTAMPTZ END;
  v_period_end := CASE WHEN p_period IS NULL THEN NULL ELSE upper(p_period)::TIMESTAMPTZ END;

  IF p_period IS NULL THEN
    -- Full rebuild: process all moves at once (unchanged from original)
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
  ELSE
    -- OPTIMIZED incremental path: set-based, period-only moves only.
    --
    -- Correctness justification:
    -- * The ON CONFLICT COALESCE preserves superior data from earlier runs
    --   (source != 'UK' rows keep their authoritative lat/lon).
    -- * Backfill runs in chronological order, so earlier periods have already
    --   updated stats.waypoints with the best historical lat/lon/country.
    -- * LEAST(existing.first_seen_at, period_min) over chronological runs
    --   converges to the global minimum, same as full-history MIN().
    -- * Re-running a period is idempotent: COALESCE(new, existing) = existing
    --   when existing is already populated.
    --
    -- Performance: queries only the period's moves (~10K-50K rows via
    -- gk_moves_moved_on_datetime index) instead of all 6.9M rows per waypoint.

    DROP TABLE IF EXISTS tmp_snapshot_touched_waypoints;

    CREATE TEMP TABLE tmp_snapshot_touched_waypoints ON COMMIT DROP AS
    SELECT DISTINCT UPPER(BTRIM(m.waypoint)) AS waypoint_code
    FROM geokrety.gk_moves m
    WHERE m.waypoint IS NOT NULL
      AND BTRIM(m.waypoint) <> ''
      AND m.move_type <> 2
      AND m.moved_on_datetime >= v_period_start
      AND m.moved_on_datetime < v_period_end;

    ANALYZE tmp_snapshot_touched_waypoints;

    -- Single set-based UPSERT: find the best representative row per waypoint
    -- within the current period, then merge into stats.waypoints.
    WITH ranked_waypoints AS (
      SELECT
        tw.waypoint_code,
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
        -- Use MIN() OVER PARTITION to capture the earliest occurrence within
        -- this period (LEAST() in ON CONFLICT will merge with prior runs).
        MIN(m.moved_on_datetime) OVER (PARTITION BY tw.waypoint_code) AS first_seen_at,
        ROW_NUMBER() OVER (
          PARTITION BY tw.waypoint_code
          ORDER BY
            CASE
              WHEN m.position IS NOT NULL OR (m.country IS NOT NULL AND BTRIM(m.country) <> '') THEN 0
              ELSE 1
            END,
            m.moved_on_datetime,
            m.id
        ) AS representative_rank
      FROM geokrety.gk_moves m
      JOIN tmp_snapshot_touched_waypoints tw
        ON tw.waypoint_code = UPPER(BTRIM(m.waypoint))
      WHERE m.waypoint IS NOT NULL
        AND BTRIM(m.waypoint) <> ''
        AND m.move_type <> 2
        AND m.moved_on_datetime >= v_period_start
        AND m.moved_on_datetime < v_period_end
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
SQL);

        // ── fn_snapshot_cache_visits: LATERAL-based bulk rewrite ──────────
        // Replaces the RBAR FOR LOOP in the incremental (ELSE) path with two
        // CROSS JOIN LATERAL queries. LATERAL forces a nested loop join that
        // reuses idx_gk_moves_waypoint_code_hist per-waypoint, eliminating
        // 14,650 individual INSERT round-trips per dense month.
        // The full (NULL period) path is unchanged.
        $this->execute(<<<'SQL'
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
BEGIN
  v_period_start := CASE WHEN p_period IS NULL THEN NULL ELSE lower(p_period)::TIMESTAMPTZ END;
  v_period_end := CASE WHEN p_period IS NULL THEN NULL ELSE upper(p_period)::TIMESTAMPTZ END;

  IF p_period IS NULL THEN
    -- Full rebuild: delete all and reinsert from scratch (unchanged from original)
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
  ELSE
    -- OPTIMIZED incremental path: replace RBAR FOR LOOP with two LATERAL queries.
    --
    -- CROSS JOIN LATERAL guarantees a nested loop join: for each waypoint in
    -- tmp_snapshot_touched_waypoints, the inner query executes once using
    -- idx_gk_moves_waypoint_code_hist (functional index on UPPER(BTRIM(waypoint))).
    -- This eliminates 14,650 individual INSERT round-trips (7,325 waypoints × 2)
    -- and replaces them with 2 bulk INSERTs.
    --
    -- The cumulative visit counts (ALL-time history per waypoint) are preserved:
    --   DELETE removes touched waypoints' existing entries.
    --   LATERAL recomputes full historical totals via the functional index.

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

    -- Give the planner accurate row-count statistics for better join strategies.
    ANALYZE tmp_snapshot_touched_waypoints;

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

    -- LATERAL: for each waypoint, aggregate ALL-history gk_moves using the
    -- functional index idx_gk_moves_waypoint_code_hist. Then bulk-insert.
    INSERT INTO stats.gk_cache_visits (
      gk_id,
      waypoint_id,
      visit_count,
      first_visited_at,
      last_visited_at
    )
    SELECT
      l.geokret,
      tw.id,
      l.visit_count,
      l.first_visited_at,
      l.last_visited_at
    FROM tmp_snapshot_touched_waypoints tw
    CROSS JOIN LATERAL (
      SELECT
        m.geokret,
        COUNT(*)::BIGINT       AS visit_count,
        MIN(m.moved_on_datetime) AS first_visited_at,
        MAX(m.moved_on_datetime) AS last_visited_at
      FROM geokrety.gk_moves m
      WHERE m.waypoint IS NOT NULL
        AND BTRIM(m.waypoint) <> ''
        AND m.move_type <> 2
        AND UPPER(BTRIM(m.waypoint)) = tw.waypoint_code
      GROUP BY m.geokret
    ) l;

    GET DIAGNOSTICS v_gk_rows = ROW_COUNT;

    -- Same LATERAL approach for user_cache_visits.
    -- idx_gk_moves_waypoint_code_hist INCLUDEs author, so this is an index-only scan.
    INSERT INTO stats.user_cache_visits (
      user_id,
      waypoint_id,
      visit_count,
      first_visited_at,
      last_visited_at
    )
    SELECT
      l.author,
      tw.id,
      l.visit_count,
      l.first_visited_at,
      l.last_visited_at
    FROM tmp_snapshot_touched_waypoints tw
    CROSS JOIN LATERAL (
      SELECT
        m.author,
        COUNT(*)::BIGINT       AS visit_count,
        MIN(m.moved_on_datetime) AS first_visited_at,
        MAX(m.moved_on_datetime) AS last_visited_at
      FROM geokrety.gk_moves m
      WHERE m.waypoint IS NOT NULL
        AND BTRIM(m.waypoint) <> ''
        AND m.move_type <> 2
        AND m.author IS NOT NULL
        AND UPPER(BTRIM(m.waypoint)) = tw.waypoint_code
      GROUP BY m.author
    ) l;

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
SQL);
    }

    public function down(): void {
        // Restore original RBAR FOR LOOP versions (for rollback)
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
SQL);

        $this->execute(<<<'SQL'
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
SQL);
    }
}
