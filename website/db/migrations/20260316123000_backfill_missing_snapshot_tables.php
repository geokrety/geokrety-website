<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BackfillMissingSnapshotTables extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION stats.fn_snapshot_daily_entity_counts()
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_completed_at TIMESTAMPTZ;
  v_inserted_rows BIGINT := 0;
  v_elapsed_ms BIGINT := 0;
BEGIN
  PERFORM pg_advisory_xact_lock(20260316, 1);

  DROP TABLE IF EXISTS tmp_daily_entity_counts_snapshot;

  CREATE TEMP TABLE tmp_daily_entity_counts_snapshot ON COMMIT DROP AS
  WITH entity_catalog AS (
    SELECT entity
    FROM (
      VALUES
        ('gk_moves'),
        ('gk_moves_type_0'),
        ('gk_moves_type_1'),
        ('gk_moves_type_2'),
        ('gk_moves_type_3'),
        ('gk_moves_type_4'),
        ('gk_moves_type_5'),
        ('gk_geokrety'),
        ('gk_geokrety_type_0'),
        ('gk_geokrety_type_1'),
        ('gk_geokrety_type_2'),
        ('gk_geokrety_type_3'),
        ('gk_geokrety_type_4'),
        ('gk_geokrety_type_5'),
        ('gk_geokrety_type_6'),
        ('gk_geokrety_type_7'),
        ('gk_geokrety_type_8'),
        ('gk_geokrety_type_9'),
        ('gk_geokrety_type_10'),
        ('gk_pictures'),
        ('gk_pictures_type_0'),
        ('gk_pictures_type_1'),
        ('gk_pictures_type_2'),
        ('gk_users'),
        ('gk_loves')
    ) AS entities(entity)
  ),
  bounds AS (
    SELECT
      MIN(day_key) AS min_day,
      MAX(day_key) AS max_day
    FROM (
      SELECT MIN(moved_on_datetime::DATE) AS day_key FROM geokrety.gk_moves
      UNION ALL
      SELECT MIN(created_on_datetime::DATE) FROM geokrety.gk_geokrety
      UNION ALL
      SELECT MIN(uploaded_on_datetime::DATE) FROM geokrety.gk_pictures WHERE uploaded_on_datetime IS NOT NULL
      UNION ALL
      SELECT MIN(joined_on_datetime::DATE) FROM geokrety.gk_users
      UNION ALL
      SELECT MIN(created_on_datetime::DATE) FROM geokrety.gk_loves
      UNION ALL
      SELECT MAX(moved_on_datetime::DATE) AS day_key FROM geokrety.gk_moves
      UNION ALL
      SELECT MAX(created_on_datetime::DATE) FROM geokrety.gk_geokrety
      UNION ALL
      SELECT MAX(uploaded_on_datetime::DATE) FROM geokrety.gk_pictures WHERE uploaded_on_datetime IS NOT NULL
      UNION ALL
      SELECT MAX(joined_on_datetime::DATE) FROM geokrety.gk_users
      UNION ALL
      SELECT MAX(created_on_datetime::DATE) FROM geokrety.gk_loves
    ) AS day_bounds
    WHERE day_key IS NOT NULL
  ),
  day_grid AS (
    SELECT generate_series(min_day, max_day, '1 day'::INTERVAL)::DATE AS count_date
    FROM bounds
    WHERE min_day IS NOT NULL
      AND max_day IS NOT NULL
  ),
  daily_deltas AS (
    SELECT
      moved_on_datetime::DATE AS count_date,
      'gk_moves'::TEXT AS entity,
      COUNT(*)::BIGINT AS delta
    FROM geokrety.gk_moves
    GROUP BY moved_on_datetime::DATE

    UNION ALL

    SELECT
      moved_on_datetime::DATE,
      format('gk_moves_type_%s', move_type),
      COUNT(*)::BIGINT
    FROM geokrety.gk_moves
    GROUP BY moved_on_datetime::DATE, move_type

    UNION ALL

    SELECT
      created_on_datetime::DATE,
      'gk_geokrety'::TEXT,
      COUNT(*)::BIGINT
    FROM geokrety.gk_geokrety
    GROUP BY created_on_datetime::DATE

    UNION ALL

    SELECT
      created_on_datetime::DATE,
      format('gk_geokrety_type_%s', type),
      COUNT(*)::BIGINT
    FROM geokrety.gk_geokrety
    GROUP BY created_on_datetime::DATE, type

    UNION ALL

    SELECT
      uploaded_on_datetime::DATE,
      'gk_pictures'::TEXT,
      COUNT(*)::BIGINT
    FROM geokrety.gk_pictures
    WHERE uploaded_on_datetime IS NOT NULL
    GROUP BY uploaded_on_datetime::DATE

    UNION ALL

    SELECT
      uploaded_on_datetime::DATE,
      format('gk_pictures_type_%s', type),
      COUNT(*)::BIGINT
    FROM geokrety.gk_pictures
    WHERE uploaded_on_datetime IS NOT NULL
    GROUP BY uploaded_on_datetime::DATE, type

    UNION ALL

    SELECT
      joined_on_datetime::DATE,
      'gk_users'::TEXT,
      COUNT(*)::BIGINT
    FROM geokrety.gk_users
    GROUP BY joined_on_datetime::DATE

    UNION ALL

    SELECT
      created_on_datetime::DATE,
      'gk_loves'::TEXT,
      COUNT(*)::BIGINT
    FROM geokrety.gk_loves
    GROUP BY created_on_datetime::DATE
  ),
  daily_entity_deltas AS (
    SELECT
      count_date,
      entity,
      SUM(delta)::BIGINT AS delta
    FROM daily_deltas
    GROUP BY count_date, entity
  )
  SELECT
    day_grid.count_date,
    entity_catalog.entity,
    SUM(COALESCE(daily_entity_deltas.delta, 0)) OVER (
      PARTITION BY entity_catalog.entity
      ORDER BY day_grid.count_date
      ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
    )::BIGINT AS cnt
  FROM day_grid
  CROSS JOIN entity_catalog
  LEFT JOIN daily_entity_deltas
    ON daily_entity_deltas.count_date = day_grid.count_date
   AND daily_entity_deltas.entity = entity_catalog.entity;

  TRUNCATE TABLE stats.daily_entity_counts;

  INSERT INTO stats.daily_entity_counts (
    count_date,
    entity,
    cnt
  )
  SELECT
    count_date,
    entity,
    cnt
  FROM tmp_daily_entity_counts_snapshot
  ORDER BY count_date, entity;

  GET DIAGNOSTICS v_inserted_rows = ROW_COUNT;

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
    'fn_snapshot_daily_entity_counts',
    'ok',
    jsonb_build_object(
      'rows_affected', v_inserted_rows,
      'snapshot_rows', (SELECT COUNT(*)::BIGINT FROM tmp_daily_entity_counts_snapshot),
      'timing_ms', v_elapsed_ms
    ),
    v_started_at,
    v_completed_at
  );

  RETURN v_inserted_rows;
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
    'fn_snapshot_daily_entity_counts',
    'error',
    jsonb_build_object('error', SQLERRM, 'timing_ms', v_elapsed_ms),
    v_started_at,
    v_completed_at
  );

  RAISE;
END;
$$;

COMMENT ON FUNCTION stats.fn_snapshot_daily_entity_counts() IS 'Rebuilds daily_entity_counts as cumulative end-of-day totals for the canonical 25 entity counters.';

CREATE OR REPLACE FUNCTION stats.fn_snapshot_gk_country_history()
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_completed_at TIMESTAMPTZ;
  v_inserted_rows BIGINT := 0;
  v_elapsed_ms BIGINT := 0;
BEGIN
  PERFORM pg_advisory_xact_lock(20260316, 2);

  DROP TABLE IF EXISTS tmp_gk_country_history_snapshot;

  CREATE TEMP TABLE tmp_gk_country_history_snapshot ON COMMIT DROP AS
  WITH ordered_moves AS (
    SELECT
      m.id AS move_id,
      m.geokret AS geokrety_id,
      geokrety.fn_normalize_country_code(m.country) AS country_code,
      m.moved_on_datetime AS arrived_at,
      LAG(geokrety.fn_normalize_country_code(m.country)) OVER (
        PARTITION BY m.geokret
        ORDER BY m.moved_on_datetime, m.id
      ) AS previous_country_code,
      LEAD(m.moved_on_datetime) OVER (
        PARTITION BY m.geokret
        ORDER BY m.moved_on_datetime, m.id
      ) AS departed_at
    FROM geokrety.gk_moves m
    WHERE m.country IS NOT NULL
      AND m.move_type IN (0, 1, 3, 5)
  )
  SELECT
    geokrety_id,
    country_code,
    arrived_at,
    departed_at,
    move_id
  FROM ordered_moves
  WHERE previous_country_code IS DISTINCT FROM country_code;

  TRUNCATE TABLE stats.gk_country_history RESTART IDENTITY;

  INSERT INTO stats.gk_country_history (
    geokrety_id,
    country_code,
    arrived_at,
    departed_at,
    move_id
  )
  SELECT
    geokrety_id,
    country_code,
    arrived_at,
    departed_at,
    move_id
  FROM tmp_gk_country_history_snapshot
  ORDER BY geokrety_id, arrived_at, move_id;

  GET DIAGNOSTICS v_inserted_rows = ROW_COUNT;

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
    'fn_snapshot_gk_country_history',
    'ok',
    jsonb_build_object(
      'rows_affected', v_inserted_rows,
      'snapshot_rows', (SELECT COUNT(*)::BIGINT FROM tmp_gk_country_history_snapshot),
      'timing_ms', v_elapsed_ms
    ),
    v_started_at,
    v_completed_at
  );

  RETURN v_inserted_rows;
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
    'fn_snapshot_gk_country_history',
    'error',
    jsonb_build_object('error', SQLERRM, 'timing_ms', v_elapsed_ms),
    v_started_at,
    v_completed_at
  );

  RAISE;
END;
$$;

COMMENT ON FUNCTION stats.fn_snapshot_gk_country_history() IS 'Rebuilds gk_country_history from qualifying move chronology using the canonical country normalization rules.';

CREATE OR REPLACE FUNCTION stats.fn_snapshot_first_finder_events()
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_completed_at TIMESTAMPTZ;
  v_inserted_rows BIGINT := 0;
  v_elapsed_ms BIGINT := 0;
BEGIN
  PERFORM pg_advisory_xact_lock(20260316, 3);

  DROP TABLE IF EXISTS tmp_first_finder_events_snapshot;

  CREATE TEMP TABLE tmp_first_finder_events_snapshot ON COMMIT DROP AS
  WITH ranked_candidates AS (
    SELECT
      g.id AS gk_id,
      m.author AS finder_user_id,
      m.id AS move_id,
      m.move_type,
      FLOOR(EXTRACT(EPOCH FROM (m.moved_on_datetime - g.created_on_datetime)) / 3600)::SMALLINT AS hours_since_creation,
      m.moved_on_datetime AS found_at,
      g.created_on_datetime AS gk_created_at,
      ROW_NUMBER() OVER (
        PARTITION BY g.id
        ORDER BY m.moved_on_datetime, m.id
      ) AS candidate_rank
    FROM geokrety.gk_geokrety g
    JOIN geokrety.gk_moves m
      ON m.geokret = g.id
    WHERE m.author IS NOT NULL
      AND m.move_type IN (0, 1, 3, 5)
      AND (g.owner IS NULL OR m.author <> g.owner)
      AND m.moved_on_datetime >= g.created_on_datetime
      AND m.moved_on_datetime <= g.created_on_datetime + INTERVAL '168 hours'
  )
  SELECT
    gk_id,
    finder_user_id,
    move_id,
    move_type,
    hours_since_creation,
    found_at,
    gk_created_at
  FROM ranked_candidates
  WHERE candidate_rank = 1;

  TRUNCATE TABLE stats.first_finder_events;

  INSERT INTO stats.first_finder_events (
    gk_id,
    finder_user_id,
    move_id,
    move_type,
    hours_since_creation,
    found_at,
    gk_created_at
  )
  SELECT
    gk_id,
    finder_user_id,
    move_id,
    move_type,
    hours_since_creation,
    found_at,
    gk_created_at
  FROM tmp_first_finder_events_snapshot
  ORDER BY gk_id;

  GET DIAGNOSTICS v_inserted_rows = ROW_COUNT;

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
    'fn_snapshot_first_finder_events',
    'ok',
    jsonb_build_object(
      'rows_affected', v_inserted_rows,
      'snapshot_rows', (SELECT COUNT(*)::BIGINT FROM tmp_first_finder_events_snapshot),
      'timing_ms', v_elapsed_ms
    ),
    v_started_at,
    v_completed_at
  );

  RETURN v_inserted_rows;
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
    'fn_snapshot_first_finder_events',
    'error',
    jsonb_build_object('error', SQLERRM, 'timing_ms', v_elapsed_ms),
    v_started_at,
    v_completed_at
  );

  RAISE;
END;
$$;

COMMENT ON FUNCTION stats.fn_snapshot_first_finder_events() IS 'Rebuilds first_finder_events from the earliest qualifying non-owner authenticated move within the 168-hour eligibility window.';

CREATE OR REPLACE FUNCTION stats.fn_snapshot_gk_milestone_events()
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_completed_at TIMESTAMPTZ;
  v_deleted_rows BIGINT := 0;
  v_upserted_rows BIGINT := 0;
  v_elapsed_ms BIGINT := 0;
BEGIN
  PERFORM pg_advisory_xact_lock(20260316, 4);

  DROP TABLE IF EXISTS tmp_gk_milestone_events_snapshot;

  CREATE TEMP TABLE tmp_gk_milestone_events_snapshot ON COMMIT DROP AS
  WITH qualifying_moves AS (
    SELECT
      m.geokret AS gk_id,
      m.id AS move_id,
      m.author AS actor_user_id,
      m.moved_on_datetime AS occurred_at,
      COALESCE(m.km_distance, 0)::NUMERIC AS km_distance
    FROM geokrety.gk_moves m
    WHERE m.move_type IN (0, 1, 3, 5)
  ),
  km_thresholds AS (
    SELECT 100::NUMERIC AS threshold_value, 'km_100'::TEXT AS event_type
    UNION ALL
    SELECT 1000::NUMERIC, 'km_1000'::TEXT
    UNION ALL
    SELECT 10000::NUMERIC, 'km_10000'::TEXT
  ),
  km_crossings AS (
    SELECT
      qualifying_moves.gk_id,
      qualifying_moves.move_id,
      qualifying_moves.actor_user_id,
      qualifying_moves.occurred_at,
      SUM(qualifying_moves.km_distance) OVER (
        PARTITION BY qualifying_moves.gk_id
        ORDER BY qualifying_moves.occurred_at, qualifying_moves.move_id
      ) AS running_km
    FROM qualifying_moves
  ),
  km_events AS (
    SELECT DISTINCT ON (km_crossings.gk_id, km_thresholds.event_type)
      km_crossings.gk_id,
      km_thresholds.event_type,
      km_thresholds.threshold_value AS event_value,
      jsonb_strip_nulls(jsonb_build_object(
        'move_id', km_crossings.move_id,
        'actor_user_id', km_crossings.actor_user_id
      )) AS additional_data,
      km_crossings.occurred_at
    FROM km_crossings
    JOIN km_thresholds
      ON km_crossings.running_km >= km_thresholds.threshold_value
    ORDER BY km_crossings.gk_id, km_thresholds.event_type, km_crossings.occurred_at, km_crossings.move_id
  ),
  author_first_seen AS (
    SELECT
      distinct_authors.gk_id,
      distinct_authors.actor_user_id,
      distinct_authors.move_id,
      distinct_authors.occurred_at,
      ROW_NUMBER() OVER (
        PARTITION BY distinct_authors.gk_id
        ORDER BY distinct_authors.occurred_at, distinct_authors.move_id
      ) AS distinct_user_rank
    FROM (
      SELECT DISTINCT ON (m.geokret, m.author)
        m.geokret AS gk_id,
        m.author AS actor_user_id,
        m.id AS move_id,
        m.moved_on_datetime AS occurred_at
      FROM geokrety.gk_moves m
      WHERE m.author IS NOT NULL
        AND m.move_type IN (0, 1, 3, 5)
      ORDER BY m.geokret, m.author, m.moved_on_datetime, m.id
    ) AS distinct_authors
  ),
  user_thresholds AS (
    SELECT 10 AS distinct_user_rank, 'users_10'::TEXT AS event_type, 10::NUMERIC AS event_value
    UNION ALL
    SELECT 50, 'users_50', 50::NUMERIC
    UNION ALL
    SELECT 100, 'users_100', 100::NUMERIC
  ),
  user_events AS (
    SELECT
      author_first_seen.gk_id,
      user_thresholds.event_type,
      user_thresholds.event_value,
      jsonb_strip_nulls(jsonb_build_object(
        'move_id', author_first_seen.move_id,
        'actor_user_id', author_first_seen.actor_user_id
      )) AS additional_data,
      author_first_seen.occurred_at
    FROM author_first_seen
    JOIN user_thresholds
      ON user_thresholds.distinct_user_rank = author_first_seen.distinct_user_rank
  ),
  first_find_events AS (
    SELECT
      gk_id,
      'first_find'::TEXT AS event_type,
      hours_since_creation::NUMERIC AS event_value,
      jsonb_strip_nulls(jsonb_build_object(
        'move_id', move_id,
        'actor_user_id', finder_user_id
      )) AS additional_data,
      found_at AS occurred_at
    FROM stats.first_finder_events
  )
  SELECT * FROM km_events
  UNION ALL
  SELECT * FROM user_events
  UNION ALL
  SELECT * FROM first_find_events;

  DELETE FROM stats.gk_milestone_events
  WHERE event_type IN (
    'km_100',
    'km_1000',
    'km_10000',
    'users_10',
    'users_50',
    'users_100',
    'first_find'
  );

  GET DIAGNOSTICS v_deleted_rows = ROW_COUNT;

  INSERT INTO stats.gk_milestone_events (
    gk_id,
    event_type,
    event_value,
    additional_data,
    occurred_at
  )
  SELECT
    gk_id,
    event_type,
    event_value,
    additional_data,
    occurred_at
  FROM tmp_gk_milestone_events_snapshot
  ORDER BY gk_id, occurred_at, event_type;

  GET DIAGNOSTICS v_upserted_rows = ROW_COUNT;

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
    'fn_snapshot_gk_milestone_events',
    'ok',
    jsonb_build_object(
      'deleted_rows', v_deleted_rows,
      'rows_affected', v_upserted_rows,
      'snapshot_rows', (SELECT COUNT(*)::BIGINT FROM tmp_gk_milestone_events_snapshot),
      'timing_ms', v_elapsed_ms
    ),
    v_started_at,
    v_completed_at
  );

  RETURN v_deleted_rows + v_upserted_rows;
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
    'fn_snapshot_gk_milestone_events',
    'error',
    jsonb_build_object('error', SQLERRM, 'timing_ms', v_elapsed_ms),
    v_started_at,
    v_completed_at
  );

  RAISE;
END;
$$;

COMMENT ON FUNCTION stats.fn_snapshot_gk_milestone_events() IS 'Rebuilds milestone events from qualifying move history and canonical first_finder_events rows.';
SQL);

        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION stats.fn_run_snapshot_phase(
  p_phase TEXT,
  p_period tstzrange DEFAULT NULL,
  p_batch_size INT DEFAULT 50000
)
RETURNS JSONB
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_rows BIGINT := 0;
  v_text TEXT;
  v_period_date daterange;
BEGIN
  IF p_batch_size IS NULL OR p_batch_size < 1 THEN
    RAISE EXCEPTION 'p_batch_size must be >= 1'
      USING ERRCODE = '22023';
  END IF;

  IF p_period IS NOT NULL
    AND p_phase IN (
      'fn_seed_daily_activity',
      'fn_snapshot_daily_country_stats',
      'fn_snapshot_user_country_stats',
      'fn_snapshot_gk_country_stats',
      'fn_snapshot_relationship_tables'
    )
    AND (
      lower_inc(p_period) IS DISTINCT FROM TRUE
      OR upper_inc(p_period) IS DISTINCT FROM FALSE
      OR lower(p_period) IS DISTINCT FROM date_trunc('day', lower(p_period))
      OR upper(p_period) IS DISTINCT FROM date_trunc('day', upper(p_period))
    ) THEN
    RAISE EXCEPTION 'p_period must use whole-day [) bounds for phase %', p_phase
      USING ERRCODE = '22023';
  END IF;

  v_period_date := CASE
    WHEN p_period IS NULL THEN NULL
    ELSE daterange(lower(p_period)::DATE, upper(p_period)::DATE, '[)')
  END;

  CASE p_phase
    WHEN 'fn_backfill_heavy_previous_move_id_all' THEN
      IF p_period IS NULL THEN
        v_text := stats.fn_backfill_heavy_previous_move_id_all(p_batch_size, NULL);
        RETURN jsonb_build_object('phase', p_phase, 'mode', 'full', 'summary', v_text);
      END IF;

      v_rows := stats.fn_backfill_previous_move_id(p_period, p_batch_size);
      RETURN jsonb_build_object('phase', p_phase, 'mode', 'scoped', 'requested_period', p_period, 'rows_updated', v_rows);
    WHEN 'fn_snapshot_entity_counters' THEN
      PERFORM stats.fn_snapshot_entity_counters();
      RETURN jsonb_build_object('phase', p_phase, 'mode', 'full', 'result', 'completed');
    WHEN 'fn_snapshot_daily_entity_counts' THEN
      IF p_period IS NOT NULL THEN
        RAISE EXCEPTION 'Phase % only supports full mode', p_phase
          USING ERRCODE = '22023';
      END IF;

      v_rows := stats.fn_snapshot_daily_entity_counts();
      RETURN jsonb_build_object('phase', p_phase, 'mode', 'full', 'rows_affected', v_rows);
    WHEN 'fn_seed_daily_activity' THEN
      v_rows := stats.fn_seed_daily_activity(p_period);
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_daily_country_stats' THEN
      v_rows := stats.fn_snapshot_daily_country_stats(v_period_date);
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_user_country_stats' THEN
      v_rows := stats.fn_snapshot_user_country_stats(v_period_date);
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'mode', CASE WHEN p_period IS NULL THEN 'full' ELSE 'scoped' END, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_gk_country_stats' THEN
      v_rows := stats.fn_snapshot_gk_country_stats(v_period_date);
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'mode', CASE WHEN p_period IS NULL THEN 'full' ELSE 'scoped' END, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_gk_country_history' THEN
      IF p_period IS NOT NULL THEN
        RAISE EXCEPTION 'Phase % only supports full mode', p_phase
          USING ERRCODE = '22023';
      END IF;

      v_rows := stats.fn_snapshot_gk_country_history();
      RETURN jsonb_build_object('phase', p_phase, 'mode', 'full', 'rows_affected', v_rows);
    WHEN 'fn_snapshot_relationship_tables' THEN
      v_rows := stats.fn_snapshot_relationship_tables(v_period_date);
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'mode', CASE WHEN p_period IS NULL THEN 'full' ELSE 'scoped' END, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_hourly_activity' THEN
      IF p_period IS NULL THEN
        v_rows := stats.fn_snapshot_hourly_activity();
      ELSE
        v_rows := stats.fn_snapshot_hourly_activity(p_period);
      END IF;

      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_country_pair_flows' THEN
      IF p_period IS NULL THEN
        v_rows := stats.fn_snapshot_country_pair_flows();
      ELSE
        v_rows := stats.fn_snapshot_country_pair_flows(p_period);
      END IF;

      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_first_finder_events' THEN
      IF p_period IS NOT NULL THEN
        RAISE EXCEPTION 'Phase % only supports full mode', p_phase
          USING ERRCODE = '22023';
      END IF;

      v_rows := stats.fn_snapshot_first_finder_events();
      RETURN jsonb_build_object('phase', p_phase, 'mode', 'full', 'rows_affected', v_rows);
    WHEN 'fn_snapshot_gk_milestone_events' THEN
      IF p_period IS NOT NULL THEN
        RAISE EXCEPTION 'Phase % only supports full mode', p_phase
          USING ERRCODE = '22023';
      END IF;

      v_rows := stats.fn_snapshot_gk_milestone_events();
      RETURN jsonb_build_object('phase', p_phase, 'mode', 'full', 'rows_affected', v_rows);
    ELSE
      RAISE EXCEPTION 'Unsupported snapshot phase: %', p_phase
        USING ERRCODE = '22023';
  END CASE;
END;
$$;
SQL);
    }

    public function down(): void {
        $this->execute('DROP FUNCTION IF EXISTS stats.fn_snapshot_gk_milestone_events();');
        $this->execute('DROP FUNCTION IF EXISTS stats.fn_snapshot_first_finder_events();');
        $this->execute('DROP FUNCTION IF EXISTS stats.fn_snapshot_gk_country_history();');
        $this->execute('DROP FUNCTION IF EXISTS stats.fn_snapshot_daily_entity_counts();');

        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION stats.fn_run_snapshot_phase(
  p_phase TEXT,
  p_period tstzrange DEFAULT NULL,
  p_batch_size INT DEFAULT 50000
)
RETURNS JSONB
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_rows BIGINT := 0;
  v_text TEXT;
  v_period_date daterange;
BEGIN
  IF p_batch_size IS NULL OR p_batch_size < 1 THEN
    RAISE EXCEPTION 'p_batch_size must be >= 1'
      USING ERRCODE = '22023';
  END IF;

  IF p_period IS NOT NULL
    AND p_phase IN (
      'fn_seed_daily_activity',
      'fn_snapshot_daily_country_stats',
      'fn_snapshot_user_country_stats',
      'fn_snapshot_gk_country_stats',
      'fn_snapshot_relationship_tables'
    )
    AND (
      lower_inc(p_period) IS DISTINCT FROM TRUE
      OR upper_inc(p_period) IS DISTINCT FROM FALSE
      OR lower(p_period) IS DISTINCT FROM date_trunc('day', lower(p_period))
      OR upper(p_period) IS DISTINCT FROM date_trunc('day', upper(p_period))
    ) THEN
    RAISE EXCEPTION 'p_period must use whole-day [) bounds for phase %', p_phase
      USING ERRCODE = '22023';
  END IF;

  v_period_date := CASE
    WHEN p_period IS NULL THEN NULL
    ELSE daterange(lower(p_period)::DATE, upper(p_period)::DATE, '[)')
  END;

  CASE p_phase
    WHEN 'fn_backfill_heavy_previous_move_id_all' THEN
      IF p_period IS NULL THEN
        v_text := stats.fn_backfill_heavy_previous_move_id_all(p_batch_size, NULL);
        RETURN jsonb_build_object('phase', p_phase, 'mode', 'full', 'summary', v_text);
      END IF;

      v_rows := stats.fn_backfill_previous_move_id(p_period, p_batch_size);
      RETURN jsonb_build_object('phase', p_phase, 'mode', 'scoped', 'requested_period', p_period, 'rows_updated', v_rows);
    WHEN 'fn_snapshot_entity_counters' THEN
      PERFORM stats.fn_snapshot_entity_counters();
      RETURN jsonb_build_object('phase', p_phase, 'mode', 'full', 'result', 'completed');
    WHEN 'fn_seed_daily_activity' THEN
      v_rows := stats.fn_seed_daily_activity(p_period);
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_daily_country_stats' THEN
      v_rows := stats.fn_snapshot_daily_country_stats(v_period_date);
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_user_country_stats' THEN
      v_rows := stats.fn_snapshot_user_country_stats(v_period_date);
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'mode', CASE WHEN p_period IS NULL THEN 'full' ELSE 'scoped' END, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_gk_country_stats' THEN
      v_rows := stats.fn_snapshot_gk_country_stats(v_period_date);
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'mode', CASE WHEN p_period IS NULL THEN 'full' ELSE 'scoped' END, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_relationship_tables' THEN
      v_rows := stats.fn_snapshot_relationship_tables(v_period_date);
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'mode', CASE WHEN p_period IS NULL THEN 'full' ELSE 'scoped' END, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_hourly_activity' THEN
      IF p_period IS NULL THEN
        v_rows := stats.fn_snapshot_hourly_activity();
      ELSE
        v_rows := stats.fn_snapshot_hourly_activity(p_period);
      END IF;

      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_country_pair_flows' THEN
      IF p_period IS NULL THEN
        v_rows := stats.fn_snapshot_country_pair_flows();
      ELSE
        v_rows := stats.fn_snapshot_country_pair_flows(p_period);
      END IF;

      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'rows_affected', v_rows);
    ELSE
      RAISE EXCEPTION 'Unsupported snapshot phase: %', p_phase
        USING ERRCODE = '22023';
  END CASE;
END;
$$;
SQL);
    }
}
