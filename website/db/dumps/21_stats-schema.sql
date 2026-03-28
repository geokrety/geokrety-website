--
-- PostgreSQL database dump
--

\restrict Y2LmTGw9BEeb3hnngn64Sfcqhimq9rAU1daIB2Gi3PSNBkhelWwcfbBpwOabrVU

-- Dumped from database version 16.13 (Ubuntu 16.13-1.pgdg24.04+1)
-- Dumped by pg_dump version 18.3 (Ubuntu 18.3-1.pgdg22.04+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: stats; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA stats;


--
-- Name: SCHEMA stats; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON SCHEMA stats IS 'GeoKrety statistics: counters, aggregates, relationships, geography/time buckets, operational helpers';


--
-- Name: fn_backfill_gk_moves_logged_at_author_home(tstzrange, integer); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_backfill_gk_moves_logged_at_author_home(p_period tstzrange DEFAULT NULL::tstzrange, p_batch_size integer DEFAULT 50000) RETURNS text
    LANGUAGE plpgsql
    AS $$
DECLARE
  v_batch_processed INTEGER := 0;
  v_batch_updated INTEGER := 0;
  v_batch_count INTEGER := 0;
  v_scope_description TEXT;
  v_scope_has_rows BOOLEAN := false;
BEGIN
  IF p_batch_size IS NULL OR p_batch_size <= 0 THEN
    RAISE EXCEPTION 'p_batch_size must be a positive integer (got %)', p_batch_size
      USING HINT = 'Use DEFAULT value for automatic batch sizing or provide p_batch_size > 0';
  END IF;

  IF p_period IS NOT NULL THEN
    SELECT EXISTS (
      SELECT 1
      FROM geokrety.gk_moves m
      WHERE p_period @> m.moved_on_datetime
    )
    INTO v_scope_has_rows;
  END IF;

  WITH derived_rows AS (
      SELECT
        m.id,
        m.logged_at_author_home,
        CASE
          WHEN m.author IS NULL OR m.position IS NULL OR u.home_position IS NULL THEN false
          ELSE public.ST_DWithin(m.position, u.home_position, 50)
        END AS derived_value
      FROM geokrety.gk_moves m
      LEFT JOIN geokrety.gk_users u
        ON u.id = m.author
      WHERE p_period IS NULL OR p_period @> m.moved_on_datetime
    ),
    candidate_rows AS (
      SELECT
        d.id,
        d.derived_value
      FROM derived_rows d
      WHERE d.logged_at_author_home IS DISTINCT FROM d.derived_value
      ORDER BY d.id
      LIMIT p_batch_size
    ),
    updated_rows AS (
      UPDATE geokrety.gk_moves m
      SET logged_at_author_home = c.derived_value
      FROM candidate_rows c
      WHERE m.id = c.id
        AND m.logged_at_author_home IS DISTINCT FROM c.derived_value
      RETURNING m.id
    )
    SELECT
      COUNT(c.id),
      COALESCE((SELECT COUNT(*) FROM updated_rows), 0)
    INTO v_batch_processed, v_batch_updated
    FROM candidate_rows c;

  IF v_batch_processed > 0 THEN
    v_batch_count := 1;
  END IF;

  IF p_period IS NULL THEN
    v_scope_description := 'full-history scope';
  ELSIF NOT v_scope_has_rows THEN
    v_scope_description := 'empty period scope (no rows in range)';
  ELSE
    v_scope_description := format(
      'period-scoped from %s to %s',
      COALESCE(to_char(lower(p_period) AT TIME ZONE 'UTC', 'YYYY-MM-DD'), 'unbounded'),
      COALESCE(to_char(upper(p_period) AT TIME ZONE 'UTC', 'YYYY-MM-DD'), 'unbounded')
    );
  END IF;

  RETURN format(
    'Processed %s rows; %s rows updated; %s batches completed; %s.',
    v_batch_processed,
    v_batch_updated,
    v_batch_count,
    v_scope_description
  );
END;
$$;


--
-- Name: fn_backfill_heavy_previous_move_id_all(integer, integer); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_backfill_heavy_previous_move_id_all(p_batch_size integer DEFAULT 5000, p_month_limit integer DEFAULT 16) RETURNS text
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_refreshed_at TIMESTAMPTZ;
  v_earliest TIMESTAMPTZ;
  v_latest TIMESTAMPTZ;
  v_effective_latest TIMESTAMPTZ;
  v_slice_start TIMESTAMPTZ;
  v_slice_end TIMESTAMPTZ;
  v_rows BIGINT := 0;
  v_total_rows BIGINT := 0;
  v_total_pending_rows BIGINT := 0;
  v_summary TEXT := '';
  v_month_count INT := 0;
  v_total_months INT := 0;
  v_month_started_at TIMESTAMPTZ;
  v_rows_per_sec NUMERIC;
  v_elapsed_sec NUMERIC;
  v_remaining_rows BIGINT := 0;
  v_estimated_remaining_sec NUMERIC;
  v_estimated_completion TIMESTAMPTZ;
  v_now TIMESTAMPTZ;
BEGIN
  IF p_batch_size IS NULL OR p_batch_size < 1 THEN
    RAISE EXCEPTION 'p_batch_size must be >= 1'
      USING ERRCODE = '22023';
  END IF;

  IF p_month_limit IS NOT NULL AND p_month_limit < 1 THEN
    RAISE EXCEPTION 'p_month_limit must be >= 1 or NULL'
      USING ERRCODE = '22023';
  END IF;

  IF NOT EXISTS (
    SELECT 1
    FROM pg_matviews
    WHERE schemaname = 'stats'
      AND matviewname = 'mv_backfill_working_set'
  ) THEN
    RAISE EXCEPTION 'stats.mv_backfill_working_set does not exist'
      USING ERRCODE = 'P0002';
  END IF;

  IF EXISTS (
    SELECT 1
    FROM pg_matviews
    WHERE schemaname = 'stats'
      AND matviewname = 'mv_backfill_working_set'
      AND NOT ispopulated
  ) THEN
    REFRESH MATERIALIZED VIEW stats.mv_backfill_working_set;
    v_refreshed_at := clock_timestamp();
  END IF;

  WITH pending_rows AS (
    SELECT mv.moved_on_datetime
    FROM stats.mv_backfill_working_set mv
    JOIN geokrety.gk_moves m ON m.id = mv.id
    WHERE mv.geokret IS NOT NULL
      AND (
        m.previous_move_id IS DISTINCT FROM mv.previous_move_id
        OR m.previous_position_id IS DISTINCT FROM mv.previous_position_id
        OR m.km_distance IS DISTINCT FROM mv.expected_km_distance
      )
  )
  SELECT MIN(moved_on_datetime), MAX(moved_on_datetime), COUNT(*)
    INTO v_earliest, v_latest, v_total_pending_rows
  FROM pending_rows;

  IF v_earliest IS NULL THEN
    INSERT INTO stats.job_log (job_name, status, metadata, started_at, completed_at)
    VALUES (
      'fn_backfill_heavy_previous_move_id_all',
      'ok',
      jsonb_build_object(
        'batch_size', p_batch_size,
        'month_limit', p_month_limit,
        'rows_updated', 0,
        'months_processed', 0,
        'strategy', 'monthly_mv_join_split_chain',
        'summary', 'nothing to backfill',
        'refreshed_at', v_refreshed_at
      ),
      v_started_at,
      clock_timestamp()
    );

    RETURN 'fn_backfill_heavy_previous_move_id_all: nothing to backfill';
  END IF;

  v_slice_start := date_trunc('month', v_earliest);

  IF p_month_limit IS NULL THEN
    v_effective_latest := v_latest;
  ELSE
    v_effective_latest := LEAST(
      v_latest,
      v_slice_start + (p_month_limit || ' months')::INTERVAL - INTERVAL '1 second'
    );
  END IF;

  WITH pending_months AS (
    SELECT DISTINCT date_trunc('month', mv.moved_on_datetime) AS month_start
    FROM stats.mv_backfill_working_set mv
    JOIN geokrety.gk_moves m ON m.id = mv.id
    WHERE mv.geokret IS NOT NULL
      AND mv.moved_on_datetime >= v_slice_start
      AND mv.moved_on_datetime <= v_effective_latest
      AND (
        m.previous_move_id IS DISTINCT FROM mv.previous_move_id
        OR m.previous_position_id IS DISTINCT FROM mv.previous_position_id
        OR m.km_distance IS DISTINCT FROM mv.expected_km_distance
      )
  )
  SELECT COUNT(*)::INT
    INTO v_total_months
  FROM pending_months;

  WHILE v_slice_start <= v_effective_latest LOOP
    v_month_count := v_month_count + 1;
    v_slice_end := v_slice_start + INTERVAL '1 month';
    v_month_started_at := clock_timestamp();

    v_rows := stats.fn_backfill_previous_move_id(
      tstzrange(v_slice_start, v_slice_end, '[)'),
      p_batch_size
    );
    v_total_rows := v_total_rows + v_rows;

    v_now := clock_timestamp();
    v_elapsed_sec := extract(epoch FROM v_now - v_month_started_at);
    v_rows_per_sec := CASE WHEN v_elapsed_sec > 0 THEN ROUND(v_rows::NUMERIC / v_elapsed_sec, 2) ELSE 0 END;
    v_remaining_rows := GREATEST(v_total_pending_rows - v_total_rows, 0);
    v_estimated_remaining_sec := CASE
      WHEN v_rows_per_sec > 0 THEN ROUND(v_remaining_rows::NUMERIC / v_rows_per_sec, 0)
      ELSE NULL
    END;
    v_estimated_completion := CASE
      WHEN v_estimated_remaining_sec IS NULL THEN NULL
      ELSE v_now + (v_estimated_remaining_sec || ' seconds')::INTERVAL
    END;

    v_summary := v_summary
      || CASE WHEN v_summary = '' THEN '' ELSE E'\n' END
      || format(
        '#%s month=%s rows=%s rows/sec=%s cumulative=%s eta=%s now=%s',
        v_month_count,
        v_slice_start::DATE,
        v_rows,
        COALESCE(v_rows_per_sec::TEXT, '0'),
        v_total_rows,
        COALESCE(v_estimated_completion::TEXT, 'n/a'),
        v_now
      );

    v_slice_start := v_slice_end;
  END LOOP;

  INSERT INTO stats.job_log (job_name, status, metadata, started_at, completed_at)
  VALUES (
    'fn_backfill_heavy_previous_move_id_all',
    'ok',
    jsonb_build_object(
      'batch_size', p_batch_size,
      'month_limit', p_month_limit,
      'months_planned', v_total_months,
      'months_processed', v_month_count,
      'pending_rows', v_total_pending_rows,
      'rows_updated', v_total_rows,
      'strategy', 'monthly_mv_join_split_chain',
      'summary', v_summary,
      'processed_through', v_effective_latest,
      'refreshed_at', v_refreshed_at
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN format(
    'fn_backfill_heavy_previous_move_id_all completed: %s rows in %s months (month_limit=%s)',
    v_total_rows,
    v_month_count,
    COALESCE(p_month_limit::TEXT, 'unlimited')
  );
EXCEPTION WHEN OTHERS THEN
  INSERT INTO stats.job_log (job_name, status, metadata, started_at, completed_at)
  VALUES (
    'fn_backfill_heavy_previous_move_id_all',
    'error',
    jsonb_build_object(
      'batch_size', p_batch_size,
      'month_limit', p_month_limit,
      'months_processed', v_month_count,
      'pending_rows', v_total_pending_rows,
      'rows_updated', v_total_rows,
      'refreshed_at', v_refreshed_at,
      'error', SQLERRM
    ),
    v_started_at,
    clock_timestamp()
  );

  RAISE;
END;
$$;


--
-- Name: fn_backfill_previous_move_id(tstzrange, integer); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_backfill_previous_move_id(p_period tstzrange DEFAULT tstzrange('-infinity'::timestamp with time zone, 'infinity'::timestamp with time zone, '[)'::text), p_batch_size integer DEFAULT 50000) RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_rows_updated BIGINT := 0;
  v_batch_rows BIGINT := 0;
BEGIN
  IF p_batch_size IS NULL OR p_batch_size < 1 THEN
    RAISE EXCEPTION 'p_batch_size must be >= 1'
      USING ERRCODE = '22023';
  END IF;

  IF NOT EXISTS (
    SELECT 1
    FROM pg_matviews
    WHERE schemaname = 'stats'
      AND matviewname = 'mv_backfill_working_set'
  ) THEN
    RAISE EXCEPTION 'stats.mv_backfill_working_set does not exist'
      USING ERRCODE = 'P0002';
  END IF;

  BEGIN
    ALTER TABLE geokrety.gk_moves DISABLE TRIGGER ALL;

    LOOP
      WITH batch AS (
        SELECT
          mv.id,
          mv.previous_move_id,
          mv.previous_position_id,
          mv.expected_km_distance
        FROM stats.mv_backfill_working_set mv
        JOIN geokrety.gk_moves m ON m.id = mv.id
        WHERE mv.moved_on_datetime <@ p_period
          AND (
            m.previous_move_id IS DISTINCT FROM mv.previous_move_id
            OR m.previous_position_id IS DISTINCT FROM mv.previous_position_id
            OR m.km_distance IS DISTINCT FROM mv.expected_km_distance
          )
        ORDER BY mv.moved_on_datetime, mv.id
        LIMIT p_batch_size
      ),
      updated_rows AS (
        UPDATE geokrety.gk_moves m
           SET previous_move_id = batch.previous_move_id,
               previous_position_id = batch.previous_position_id,
               km_distance = batch.expected_km_distance
          FROM batch
         WHERE m.id = batch.id
        RETURNING 1
      )
      SELECT COUNT(*)
        INTO v_batch_rows
      FROM updated_rows;

      EXIT WHEN v_batch_rows = 0;
      v_rows_updated := v_rows_updated + v_batch_rows;
    END LOOP;

    ALTER TABLE geokrety.gk_moves ENABLE TRIGGER ALL;
  EXCEPTION WHEN OTHERS THEN
    BEGIN
      ALTER TABLE geokrety.gk_moves ENABLE TRIGGER ALL;
    EXCEPTION WHEN OTHERS THEN
      NULL;
    END;

    RAISE;
  END;

  INSERT INTO stats.job_log (job_name, status, metadata, started_at, completed_at)
  VALUES (
    'fn_backfill_previous_move_id',
    'ok',
    jsonb_build_object(
      'period', p_period,
      'batch_size', p_batch_size,
      'rows_updated', v_rows_updated,
      'strategy', 'mv_join_batch_update_split_chain'
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_rows_updated;
EXCEPTION WHEN OTHERS THEN
  INSERT INTO stats.job_log (job_name, status, metadata, started_at, completed_at)
  VALUES (
    'fn_backfill_previous_move_id',
    'error',
    jsonb_build_object(
      'period', p_period,
      'batch_size', p_batch_size,
      'rows_updated', v_rows_updated,
      'error', SQLERRM
    ),
    v_started_at,
    clock_timestamp()
  );

  RAISE;
END;
$$;


--
-- Name: fn_detect_first_finder(bigint, bigint, bigint, smallint, timestamp with time zone); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_detect_first_finder(p_gk_id bigint, p_move_id bigint, p_finder_user_id bigint, p_move_type smallint, p_found_at timestamp with time zone) RETURNS boolean
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
  v_canonical_move_id BIGINT;
BEGIN
  IF p_finder_user_id IS NULL OR p_move_type NOT IN (0, 1, 3, 5) THEN
    RETURN FALSE;
  END IF;

  PERFORM stats.fn_reconcile_first_finder_event(p_gk_id);

  SELECT move_id
    INTO v_canonical_move_id
  FROM stats.first_finder_events
  WHERE gk_id = p_gk_id::INT;

  RETURN v_canonical_move_id = p_move_id;
END;
$$;


--
-- Name: fn_reconcile_first_finder_event(bigint); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_reconcile_first_finder_event(p_gk_id bigint) RETURNS boolean
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
  v_finder_user_id BIGINT;
  v_move_id BIGINT;
  v_move_type SMALLINT;
  v_found_at TIMESTAMPTZ;
  v_gk_created_at TIMESTAMPTZ;
  v_hours_since_creation SMALLINT;
BEGIN
  IF p_gk_id IS NULL THEN
    RETURN FALSE;
  END IF;

  PERFORM pg_advisory_xact_lock(20260316, p_gk_id::INT);

  SELECT
    m.author,
    m.id,
    m.move_type,
    m.moved_on_datetime,
    g.created_on_datetime,
    FLOOR(
      EXTRACT(EPOCH FROM (m.moved_on_datetime - g.created_on_datetime)) / 3600
    )::SMALLINT
  INTO
    v_finder_user_id,
    v_move_id,
    v_move_type,
    v_found_at,
    v_gk_created_at,
    v_hours_since_creation
  FROM geokrety.gk_geokrety g
  JOIN geokrety.gk_moves m
    ON m.geokret = g.id
  WHERE g.id = p_gk_id
    AND m.author IS NOT NULL
    AND m.move_type IN (0, 1, 3, 5)
    AND m.author IS DISTINCT FROM g.owner
    AND m.moved_on_datetime >= g.created_on_datetime
    AND m.moved_on_datetime <= g.created_on_datetime + INTERVAL '168 hours'
  ORDER BY m.moved_on_datetime, m.id
  LIMIT 1;

  IF NOT FOUND THEN
    DELETE FROM stats.first_finder_events
    WHERE gk_id = p_gk_id::INT;

    DELETE FROM stats.gk_milestone_events
    WHERE gk_id = p_gk_id::INT
      AND event_type = 'first_find';

    RETURN FALSE;
  END IF;

  INSERT INTO stats.first_finder_events (
    gk_id,
    finder_user_id,
    move_id,
    move_type,
    hours_since_creation,
    found_at,
    gk_created_at
  )
  VALUES (
    p_gk_id::INT,
    v_finder_user_id::INT,
    v_move_id,
    v_move_type,
    v_hours_since_creation,
    v_found_at,
    v_gk_created_at
  )
  ON CONFLICT (gk_id) DO UPDATE SET
    finder_user_id = EXCLUDED.finder_user_id,
    move_id = EXCLUDED.move_id,
    move_type = EXCLUDED.move_type,
    hours_since_creation = EXCLUDED.hours_since_creation,
    found_at = EXCLUDED.found_at,
    gk_created_at = EXCLUDED.gk_created_at,
    recorded_at = NOW()
  WHERE stats.first_finder_events.finder_user_id IS DISTINCT FROM EXCLUDED.finder_user_id
     OR stats.first_finder_events.move_id IS DISTINCT FROM EXCLUDED.move_id
     OR stats.first_finder_events.move_type IS DISTINCT FROM EXCLUDED.move_type
     OR stats.first_finder_events.hours_since_creation IS DISTINCT FROM EXCLUDED.hours_since_creation
     OR stats.first_finder_events.found_at IS DISTINCT FROM EXCLUDED.found_at
     OR stats.first_finder_events.gk_created_at IS DISTINCT FROM EXCLUDED.gk_created_at;

  INSERT INTO stats.gk_milestone_events (
    gk_id,
    event_type,
    event_value,
    additional_data,
    occurred_at
  )
  VALUES (
    p_gk_id::INT,
    'first_find',
    v_hours_since_creation,
    jsonb_strip_nulls(jsonb_build_object(
      'move_id', v_move_id,
      'actor_user_id', v_finder_user_id
    )),
    v_found_at
  )
  ON CONFLICT (gk_id, event_type) DO UPDATE SET
    event_value = EXCLUDED.event_value,
    additional_data = EXCLUDED.additional_data,
    occurred_at = EXCLUDED.occurred_at,
    recorded_at = NOW()
  WHERE stats.gk_milestone_events.event_value IS DISTINCT FROM EXCLUDED.event_value
     OR stats.gk_milestone_events.additional_data IS DISTINCT FROM EXCLUDED.additional_data
     OR stats.gk_milestone_events.occurred_at IS DISTINCT FROM EXCLUDED.occurred_at;

  RETURN TRUE;
END;
$$;


--
-- Name: FUNCTION fn_reconcile_first_finder_event(p_gk_id bigint); Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON FUNCTION stats.fn_reconcile_first_finder_event(p_gk_id bigint) IS 'Recomputes the canonical first_finder_events row and first_find milestone for one GeoKret after live source changes.';


--
-- Name: fn_reconcile_stats(); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_reconcile_stats() RETURNS TABLE(check_name text, source_count bigint, stats_count bigint, delta bigint, status text)
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
BEGIN
  RETURN QUERY
  SELECT *
  FROM stats.fn_reconcile_stats(NULL::TEXT[], NULL::tstzrange);
END;
$$;


--
-- Name: fn_reconcile_stats(text[], tstzrange); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_reconcile_stats(p_checks text[], p_period tstzrange) RETURNS TABLE(check_name text, source_count bigint, stats_count bigint, delta bigint, status text)
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_failures BIGINT := 0;
  v_run_all_checks BOOLEAN := p_checks IS NULL OR array_length(p_checks, 1) IS NULL;
  v_period_start TIMESTAMPTZ;
  v_period_end TIMESTAMPTZ;
BEGIN
  v_period_start := CASE WHEN p_period IS NULL THEN NULL ELSE lower(p_period) END;
  v_period_end := CASE WHEN p_period IS NULL THEN NULL ELSE upper(p_period) END;

  DROP TABLE IF EXISTS tmp_reconcile_results;

  CREATE TEMP TABLE tmp_reconcile_results (
    check_name TEXT,
    source_count BIGINT,
    stats_count BIGINT,
    delta BIGINT,
    status TEXT
  ) ON COMMIT DROP;

  IF v_run_all_checks OR 'stats.entity_counters_shard' = ANY(p_checks) THEN
    INSERT INTO tmp_reconcile_results
    WITH expected AS (
      SELECT
        entities.entity,
        shards.shard,
        COALESCE(entity_totals.cnt, 0) AS cnt
      FROM (
        VALUES
          ('gk_moves'), ('gk_moves_type_0'), ('gk_moves_type_1'), ('gk_moves_type_2'),
          ('gk_moves_type_3'), ('gk_moves_type_4'), ('gk_moves_type_5'),
          ('gk_geokrety'), ('gk_geokrety_type_0'), ('gk_geokrety_type_1'),
          ('gk_geokrety_type_2'), ('gk_geokrety_type_3'), ('gk_geokrety_type_4'),
          ('gk_geokrety_type_5'), ('gk_geokrety_type_6'), ('gk_geokrety_type_7'),
          ('gk_geokrety_type_8'), ('gk_geokrety_type_9'), ('gk_geokrety_type_10'),
          ('gk_pictures'), ('gk_pictures_type_0'), ('gk_pictures_type_1'),
          ('gk_pictures_type_2'), ('gk_users'), ('gk_loves')
      ) AS entities(entity)
      CROSS JOIN generate_series(0, 15) AS shards(shard)
      LEFT JOIN (
        SELECT 'gk_moves'::TEXT AS entity, (id % 16) AS shard, COUNT(*)::BIGINT AS cnt
        FROM geokrety.gk_moves
        GROUP BY (id % 16)
        UNION ALL
        SELECT format('gk_moves_type_%s', move_type), (id % 16), COUNT(*)::BIGINT
        FROM geokrety.gk_moves
        GROUP BY move_type, (id % 16)
        UNION ALL
        SELECT 'gk_geokrety'::TEXT, (id % 16), COUNT(*)::BIGINT
        FROM geokrety.gk_geokrety
        GROUP BY (id % 16)
        UNION ALL
        SELECT format('gk_geokrety_type_%s', type), (id % 16), COUNT(*)::BIGINT
        FROM geokrety.gk_geokrety
        GROUP BY type, (id % 16)
        UNION ALL
        SELECT 'gk_pictures'::TEXT, (id % 16), COUNT(*)::BIGINT
        FROM geokrety.gk_pictures
        WHERE uploaded_on_datetime IS NOT NULL
        GROUP BY (id % 16)
        UNION ALL
        SELECT format('gk_pictures_type_%s', type), (id % 16), COUNT(*)::BIGINT
        FROM geokrety.gk_pictures
        WHERE uploaded_on_datetime IS NOT NULL
        GROUP BY type, (id % 16)
        UNION ALL
        SELECT 'gk_users'::TEXT, (id % 16), COUNT(*)::BIGINT
        FROM geokrety.gk_users
        GROUP BY (id % 16)
        UNION ALL
        SELECT 'gk_loves'::TEXT, (id % 16), COUNT(*)::BIGINT
        FROM geokrety.gk_loves
        GROUP BY (id % 16)
      ) AS entity_totals USING (entity, shard)
    ), mismatches AS (
      SELECT COUNT(*)::BIGINT AS mismatch_count
      FROM expected e
      FULL JOIN stats.entity_counters_shard a
        ON a.entity = e.entity
       AND a.shard = e.shard
      WHERE a.entity IS NULL
         OR e.entity IS NULL
         OR a.cnt IS DISTINCT FROM e.cnt
    )
    SELECT
      'stats.entity_counters_shard',
      (SELECT COUNT(*)::BIGINT FROM expected),
      (SELECT COUNT(*)::BIGINT FROM stats.entity_counters_shard),
      mismatch_count,
      CASE WHEN mismatch_count = 0 THEN 'OK' ELSE 'FAIL' END
    FROM mismatches;
  END IF;

  IF v_run_all_checks OR 'stats.daily_activity' = ANY(p_checks) THEN
    INSERT INTO tmp_reconcile_results
    WITH activity_days AS (
      SELECT moved_on_datetime::date AS activity_date
      FROM geokrety.gk_moves
      WHERE p_period IS NULL OR (moved_on_datetime >= v_period_start AND moved_on_datetime < v_period_end)
      UNION
      SELECT created_on_datetime::date FROM geokrety.gk_geokrety
      WHERE p_period IS NULL OR (created_on_datetime >= v_period_start AND created_on_datetime < v_period_end)
      UNION
      SELECT uploaded_on_datetime::date
      FROM geokrety.gk_pictures
      WHERE uploaded_on_datetime IS NOT NULL
        AND (p_period IS NULL OR (uploaded_on_datetime >= v_period_start AND uploaded_on_datetime < v_period_end))
      UNION
      SELECT joined_on_datetime::date FROM geokrety.gk_users
      WHERE p_period IS NULL OR (joined_on_datetime >= v_period_start AND joined_on_datetime < v_period_end)
      UNION
      SELECT created_on_datetime::date FROM geokrety.gk_loves
      WHERE p_period IS NULL OR (created_on_datetime >= v_period_start AND created_on_datetime < v_period_end)
    ), expected AS (
      SELECT
        activity_days.activity_date,
        COALESCE(moves_daily.total_moves, 0) AS total_moves,
        COALESCE(moves_daily.drops, 0) AS drops,
        COALESCE(moves_daily.grabs, 0) AS grabs,
        COALESCE(moves_daily.comments, 0) AS comments,
        COALESCE(moves_daily.sees, 0) AS sees,
        COALESCE(moves_daily.archives, 0) AS archives,
        COALESCE(moves_daily.dips, 0) AS dips,
        COALESCE(moves_daily.km_contributed, 0)::NUMERIC(14,3) AS km_contributed,
        COALESCE(geokrety_daily.gk_created, 0) AS gk_created,
        COALESCE(pictures_daily.pictures_uploaded_total, 0) AS pictures_uploaded_total,
        COALESCE(pictures_daily.pictures_uploaded_avatar, 0) AS pictures_uploaded_avatar,
        COALESCE(pictures_daily.pictures_uploaded_move, 0) AS pictures_uploaded_move,
        COALESCE(pictures_daily.pictures_uploaded_user, 0) AS pictures_uploaded_user,
        COALESCE(loves_daily.loves_count, 0) AS loves_count,
        COALESCE(users_daily.users_registered, 0) AS users_registered
      FROM activity_days
      LEFT JOIN (
        SELECT
          moved_on_datetime::date AS activity_date,
          COUNT(*)::BIGINT AS total_moves,
          COUNT(*) FILTER (WHERE move_type = 0)::BIGINT AS drops,
          COUNT(*) FILTER (WHERE move_type = 1)::BIGINT AS grabs,
          COUNT(*) FILTER (WHERE move_type = 2)::BIGINT AS comments,
          COUNT(*) FILTER (WHERE move_type = 3)::BIGINT AS sees,
          COUNT(*) FILTER (WHERE move_type = 4)::BIGINT AS archives,
          COUNT(*) FILTER (WHERE move_type = 5)::BIGINT AS dips,
          COALESCE(SUM(km_distance), 0)::NUMERIC(14,3) AS km_contributed
        FROM geokrety.gk_moves
        WHERE p_period IS NULL OR (moved_on_datetime >= v_period_start AND moved_on_datetime < v_period_end)
        GROUP BY moved_on_datetime::date
      ) AS moves_daily USING (activity_date)
      LEFT JOIN (
        SELECT created_on_datetime::date AS activity_date, COUNT(*)::BIGINT AS gk_created
        FROM geokrety.gk_geokrety
        WHERE p_period IS NULL OR (created_on_datetime >= v_period_start AND created_on_datetime < v_period_end)
        GROUP BY created_on_datetime::date
      ) AS geokrety_daily USING (activity_date)
      LEFT JOIN (
        SELECT
          uploaded_on_datetime::date AS activity_date,
          COUNT(*)::BIGINT AS pictures_uploaded_total,
          COUNT(*) FILTER (WHERE type = 0)::BIGINT AS pictures_uploaded_avatar,
          COUNT(*) FILTER (WHERE type = 1)::BIGINT AS pictures_uploaded_move,
          COUNT(*) FILTER (WHERE type = 2)::BIGINT AS pictures_uploaded_user
        FROM geokrety.gk_pictures
        WHERE uploaded_on_datetime IS NOT NULL
          AND (p_period IS NULL OR (uploaded_on_datetime >= v_period_start AND uploaded_on_datetime < v_period_end))
        GROUP BY uploaded_on_datetime::date
      ) AS pictures_daily USING (activity_date)
      LEFT JOIN (
        SELECT created_on_datetime::date AS activity_date, COUNT(*)::BIGINT AS loves_count
        FROM geokrety.gk_loves
        WHERE p_period IS NULL OR (created_on_datetime >= v_period_start AND created_on_datetime < v_period_end)
        GROUP BY created_on_datetime::date
      ) AS loves_daily USING (activity_date)
      LEFT JOIN (
        SELECT joined_on_datetime::date AS activity_date, COUNT(*)::BIGINT AS users_registered
        FROM geokrety.gk_users
        WHERE p_period IS NULL OR (joined_on_datetime >= v_period_start AND joined_on_datetime < v_period_end)
        GROUP BY joined_on_datetime::date
      ) AS users_daily USING (activity_date)
    ), mismatches AS (
      SELECT COUNT(*)::BIGINT AS mismatch_count
      FROM expected e
      FULL JOIN stats.daily_activity a
        ON a.activity_date = e.activity_date
      WHERE (p_period IS NULL OR e.activity_date IS NOT NULL OR (a.activity_date IS NOT NULL AND tstzrange(a.activity_date::TIMESTAMPTZ, (a.activity_date + 1)::TIMESTAMPTZ, '[)') && p_period))
        AND (
          a.activity_date IS NULL
          OR e.activity_date IS NULL
          OR a.total_moves IS DISTINCT FROM e.total_moves
          OR a.drops IS DISTINCT FROM e.drops
          OR a.grabs IS DISTINCT FROM e.grabs
          OR a.comments IS DISTINCT FROM e.comments
          OR a.sees IS DISTINCT FROM e.sees
          OR a.archives IS DISTINCT FROM e.archives
          OR a.dips IS DISTINCT FROM e.dips
          OR a.km_contributed IS DISTINCT FROM e.km_contributed
          OR a.gk_created IS DISTINCT FROM e.gk_created
          OR a.pictures_uploaded_total IS DISTINCT FROM e.pictures_uploaded_total
          OR a.pictures_uploaded_avatar IS DISTINCT FROM e.pictures_uploaded_avatar
          OR a.pictures_uploaded_move IS DISTINCT FROM e.pictures_uploaded_move
          OR a.pictures_uploaded_user IS DISTINCT FROM e.pictures_uploaded_user
          OR a.loves_count IS DISTINCT FROM e.loves_count
          OR a.users_registered IS DISTINCT FROM e.users_registered
        )
    )
    SELECT
      'stats.daily_activity',
      (SELECT COUNT(*)::BIGINT FROM expected),
      (SELECT COUNT(*)::BIGINT FROM stats.daily_activity WHERE p_period IS NULL OR tstzrange(activity_date::TIMESTAMPTZ, (activity_date + 1)::TIMESTAMPTZ, '[)') && p_period),
      mismatch_count,
      CASE WHEN mismatch_count = 0 THEN 'OK' ELSE 'FAIL' END
    FROM mismatches;
  END IF;

  IF v_run_all_checks OR 'stats.country_daily_stats' = ANY(p_checks) THEN
    INSERT INTO tmp_reconcile_results
    WITH move_expected AS (
      SELECT
        m.moved_on_datetime::date AS stats_date,
        geokrety.fn_normalize_country_code(m.country)::TEXT AS country_code,
        COUNT(*)::BIGINT AS moves_count,
        COUNT(*) FILTER (WHERE m.move_type = 0)::BIGINT AS drops,
        COUNT(*) FILTER (WHERE m.move_type = 1)::BIGINT AS grabs,
        COUNT(*) FILTER (WHERE m.move_type = 2)::BIGINT AS comments,
        COUNT(*) FILTER (WHERE m.move_type = 3)::BIGINT AS sees,
        COUNT(*) FILTER (WHERE m.move_type = 4)::BIGINT AS archives,
        COUNT(*) FILTER (WHERE m.move_type = 5)::BIGINT AS dips,
        COUNT(DISTINCT m.author) FILTER (WHERE m.author IS NOT NULL)::BIGINT AS unique_users,
        COUNT(DISTINCT m.geokret)::BIGINT AS unique_gks,
        COALESCE(SUM(m.km_distance), 0)::NUMERIC(14,3) AS km_contributed
      FROM geokrety.gk_moves m
      WHERE m.country IS NOT NULL
        AND (p_period IS NULL OR (m.moved_on_datetime >= v_period_start AND m.moved_on_datetime < v_period_end))
      GROUP BY m.moved_on_datetime::date, geokrety.fn_normalize_country_code(m.country)
    ), expected AS (
      SELECT *
      FROM move_expected
      UNION ALL
      SELECT
        cds.stats_date,
        cds.country_code::TEXT,
        0::BIGINT AS moves_count,
        0::BIGINT AS drops,
        0::BIGINT AS grabs,
        0::BIGINT AS comments,
        0::BIGINT AS sees,
        0::BIGINT AS archives,
        0::BIGINT AS dips,
        0::BIGINT AS unique_users,
        0::BIGINT AS unique_gks,
        0::NUMERIC(14,3) AS km_contributed
      FROM stats.country_daily_stats cds
      WHERE (
        cds.points_contributed <> 0
        OR cds.loves_count <> 0
        OR cds.pictures_uploaded_total <> 0
        OR cds.pictures_uploaded_avatar <> 0
        OR cds.pictures_uploaded_move <> 0
        OR cds.pictures_uploaded_user <> 0
      )
        AND (p_period IS NULL OR tstzrange(cds.stats_date::TIMESTAMPTZ, (cds.stats_date + 1)::TIMESTAMPTZ, '[)') && p_period)
        AND NOT EXISTS (
          SELECT 1
          FROM move_expected me
          WHERE me.stats_date = cds.stats_date
            AND me.country_code = cds.country_code::TEXT
        )
    ), mismatches AS (
      SELECT COUNT(*)::BIGINT AS mismatch_count
      FROM expected e
      FULL JOIN stats.country_daily_stats a
        ON a.stats_date = e.stats_date
       AND a.country_code::TEXT = e.country_code
      WHERE (p_period IS NULL OR e.stats_date IS NOT NULL OR (a.stats_date IS NOT NULL AND tstzrange(a.stats_date::TIMESTAMPTZ, (a.stats_date + 1)::TIMESTAMPTZ, '[)') && p_period))
        AND (
          a.stats_date IS NULL
          OR e.stats_date IS NULL
          OR a.moves_count IS DISTINCT FROM e.moves_count
          OR a.drops IS DISTINCT FROM e.drops
          OR a.grabs IS DISTINCT FROM e.grabs
          OR a.comments IS DISTINCT FROM e.comments
          OR a.sees IS DISTINCT FROM e.sees
          OR a.archives IS DISTINCT FROM e.archives
          OR a.dips IS DISTINCT FROM e.dips
          OR a.unique_users IS DISTINCT FROM e.unique_users
          OR a.unique_gks IS DISTINCT FROM e.unique_gks
          OR a.km_contributed IS DISTINCT FROM e.km_contributed
        )
    )
    SELECT
      'stats.country_daily_stats',
      (SELECT COUNT(*)::BIGINT FROM expected),
      (SELECT COUNT(*)::BIGINT FROM stats.country_daily_stats WHERE p_period IS NULL OR tstzrange(stats_date::TIMESTAMPTZ, (stats_date + 1)::TIMESTAMPTZ, '[)') && p_period),
      mismatch_count,
      CASE WHEN mismatch_count = 0 THEN 'OK' ELSE 'FAIL' END
    FROM mismatches;
  END IF;

  IF v_run_all_checks OR 'stats.hourly_activity' = ANY(p_checks) THEN
    INSERT INTO tmp_reconcile_results
    WITH expected AS (
      SELECT
        (timezone('UTC', m.moved_on_datetime))::DATE AS activity_date,
        EXTRACT(HOUR FROM timezone('UTC', m.moved_on_datetime))::SMALLINT AS hour_utc,
        m.move_type::SMALLINT AS move_type,
        COUNT(*)::BIGINT AS move_count
      FROM geokrety.gk_moves m
      WHERE m.move_type BETWEEN 0 AND 5
        AND (p_period IS NULL OR (m.moved_on_datetime >= v_period_start AND m.moved_on_datetime < v_period_end))
      GROUP BY 1, 2, 3
    ), mismatches AS (
      SELECT COUNT(*)::BIGINT AS mismatch_count
      FROM expected e
      FULL JOIN stats.hourly_activity a
        ON a.activity_date = e.activity_date
       AND a.hour_utc = e.hour_utc
       AND a.move_type = e.move_type
      WHERE (p_period IS NULL OR e.activity_date IS NOT NULL OR (a.activity_date IS NOT NULL AND tstzrange(a.activity_date::TIMESTAMPTZ, (a.activity_date + 1)::TIMESTAMPTZ, '[)') && p_period))
        AND (
          a.activity_date IS NULL
          OR e.activity_date IS NULL
          OR a.move_count IS DISTINCT FROM e.move_count
        )
    )
    SELECT
      'stats.hourly_activity',
      (SELECT COUNT(*)::BIGINT FROM expected),
      (SELECT COUNT(*)::BIGINT FROM stats.hourly_activity WHERE p_period IS NULL OR tstzrange(activity_date::TIMESTAMPTZ, (activity_date + 1)::TIMESTAMPTZ, '[)') && p_period),
      mismatch_count,
      CASE WHEN mismatch_count = 0 THEN 'OK' ELSE 'FAIL' END
    FROM mismatches;
  END IF;

  IF v_run_all_checks OR 'stats.country_pair_flows' = ANY(p_checks) THEN
    INSERT INTO tmp_reconcile_results
    WITH expected AS (
      SELECT
        date_trunc('month', timezone('UTC', current_move.moved_on_datetime))::DATE AS year_month,
        UPPER(BTRIM(previous_move.country))::CHAR(2) AS from_country,
        UPPER(BTRIM(current_move.country))::CHAR(2) AS to_country,
        COUNT(*)::BIGINT AS move_count,
        COUNT(DISTINCT current_move.geokret)::BIGINT AS unique_gk_count
      FROM geokrety.gk_moves current_move
      JOIN geokrety.gk_moves previous_move
        ON previous_move.id = current_move.previous_move_id
      WHERE current_move.previous_move_id IS NOT NULL
        AND current_move.move_type IN (0, 1, 3, 5)
        AND previous_move.move_type IN (0, 1, 3, 5)
        AND previous_move.country IS NOT NULL
        AND current_move.country IS NOT NULL
        AND BTRIM(previous_move.country) <> ''
        AND BTRIM(current_move.country) <> ''
        AND UPPER(BTRIM(previous_move.country)) <> UPPER(BTRIM(current_move.country))
        AND (p_period IS NULL OR (current_move.moved_on_datetime >= v_period_start AND current_move.moved_on_datetime < v_period_end))
      GROUP BY 1, 2, 3
    ), mismatches AS (
      SELECT COUNT(*)::BIGINT AS mismatch_count
      FROM expected e
      FULL JOIN stats.country_pair_flows a
        ON a.year_month = e.year_month
       AND a.from_country = e.from_country
       AND a.to_country = e.to_country
      WHERE (p_period IS NULL OR e.year_month IS NOT NULL OR (a.year_month IS NOT NULL AND tstzrange(a.year_month::TIMESTAMPTZ, (a.year_month + INTERVAL '1 month')::TIMESTAMPTZ, '[)') && p_period))
        AND (
          a.year_month IS NULL
          OR e.year_month IS NULL
          OR a.move_count IS DISTINCT FROM e.move_count
          OR a.unique_gk_count IS DISTINCT FROM e.unique_gk_count
        )
    )
    SELECT
      'stats.country_pair_flows',
      (SELECT COUNT(*)::BIGINT FROM expected),
      (SELECT COUNT(*)::BIGINT FROM stats.country_pair_flows WHERE p_period IS NULL OR tstzrange(year_month::TIMESTAMPTZ, (year_month + INTERVAL '1 month')::TIMESTAMPTZ, '[)') && p_period),
      mismatch_count,
      CASE WHEN mismatch_count = 0 THEN 'OK' ELSE 'FAIL' END
    FROM mismatches;
  END IF;

  IF v_run_all_checks OR 'stats.uc_views' = ANY(p_checks) THEN
    INSERT INTO tmp_reconcile_results
    WITH view_counts AS (
      SELECT COUNT(*)::BIGINT AS stats_count
      FROM pg_views
      WHERE schemaname = 'stats'
        AND viewname = ANY (ARRAY[
          'v_uc1_country_activity',
          'v_uc2_user_network',
          'v_uc3_gk_circulation',
          'v_uc4_user_continent_coverage',
          'v_uc6_dormancy',
          'v_uc7_country_flow',
          'v_uc8_seasonal_heatmap',
          'v_uc9_multiplier_velocity',
          'v_uc10_cache_popularity',
          'v_uc13_gk_timeline',
          'v_uc14_first_finder_hof',
          'v_uc15_distance_records'
        ])
    )
    SELECT
      'stats.uc_views',
      CASE WHEN vc.stats_count = 0 THEN 0::BIGINT ELSE 12::BIGINT END,
      vc.stats_count,
      ABS(vc.stats_count - CASE WHEN vc.stats_count = 0 THEN 0::BIGINT ELSE 12::BIGINT END),
      CASE WHEN vc.stats_count IN (0, 12) THEN 'OK' ELSE 'FAIL' END
    FROM view_counts vc;
  END IF;

  IF v_run_all_checks OR 'stats.materialized_views' = ANY(p_checks) THEN
    INSERT INTO tmp_reconcile_results
    WITH mv_counts AS (
      SELECT COUNT(*)::BIGINT AS stats_count
      FROM pg_matviews
      WHERE schemaname = 'stats'
        AND matviewname = ANY (ARRAY[
          'mv_country_month_rollup',
          'mv_top_caches_global',
          'mv_global_kpi'
        ])
    )
    SELECT
      'stats.materialized_views',
      CASE WHEN mc.stats_count = 0 THEN 0::BIGINT ELSE 3::BIGINT END,
      mc.stats_count,
      ABS(mc.stats_count - CASE WHEN mc.stats_count = 0 THEN 0::BIGINT ELSE 3::BIGINT END),
      CASE WHEN mc.stats_count IN (0, 3) THEN 'OK' ELSE 'FAIL' END
    FROM mv_counts mc;
  END IF;

  SELECT COUNT(*)::BIGINT
    INTO v_failures
  FROM tmp_reconcile_results
  WHERE tmp_reconcile_results.delta <> 0;

  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_reconcile_stats',
    CASE WHEN v_failures = 0 THEN 'ok' ELSE 'error' END,
    jsonb_build_object(
      'policy', 'exact-zero-delta',
      'requested_period', p_period,
      'checks', (SELECT COUNT(*)::BIGINT FROM tmp_reconcile_results),
      'failures', v_failures
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN QUERY
  SELECT *
  FROM tmp_reconcile_results
  ORDER BY check_name;
EXCEPTION WHEN OTHERS THEN
  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_reconcile_stats',
    'error',
    jsonb_build_object(
      'policy', 'exact-zero-delta',
      'requested_period', p_period,
      'error', SQLERRM
    ),
    v_started_at,
    clock_timestamp()
  );

  RAISE;
END;
$$;


--
-- Name: fn_run_all_snapshots(); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_run_all_snapshots() RETURNS text
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
BEGIN
  RETURN stats.fn_run_all_snapshots(NULL::TEXT[], NULL::tstzrange, 50000);
END;
$$;


--
-- Name: fn_run_all_snapshots(text[], tstzrange, integer); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_run_all_snapshots(p_phases text[], p_period tstzrange DEFAULT NULL::tstzrange, p_batch_size integer DEFAULT 50000) RETURNS text
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_phase_started_at TIMESTAMPTZ;
  v_phase_completed_at TIMESTAMPTZ;
  v_total_ms BIGINT := 0;
  v_requested_phases TEXT[] := CASE
    WHEN p_phases IS NULL OR array_length(p_phases, 1) IS NULL THEN ARRAY[
      'fn_backfill_heavy_previous_move_id_all',
      'fn_snapshot_entity_counters',
      'fn_seed_daily_activity',
      'fn_snapshot_daily_country_stats',
      'fn_snapshot_user_country_stats',
      'fn_snapshot_gk_country_stats',
      'fn_snapshot_relationship_tables',
      'fn_snapshot_hourly_activity',
      'fn_snapshot_country_pair_flows'
    ]::TEXT[]
    ELSE p_phases
  END;
  v_phase TEXT;
  v_results JSONB := '{}'::JSONB;
  v_timing_ms BIGINT;
BEGIN
  IF p_batch_size IS NULL OR p_batch_size < 1 THEN
    RAISE EXCEPTION 'p_batch_size must be >= 1'
      USING ERRCODE = '22023';
  END IF;

  FOREACH v_phase IN ARRAY v_requested_phases LOOP
    v_phase_started_at := clock_timestamp();
    v_results := v_results || jsonb_build_object(
      v_phase,
      stats.fn_run_snapshot_phase(v_phase, p_period, p_batch_size)
    );
    v_phase_completed_at := clock_timestamp();
    v_timing_ms := (EXTRACT(EPOCH FROM (v_phase_completed_at - v_phase_started_at)) * 1000)::BIGINT;
    v_total_ms := v_total_ms + v_timing_ms;

    v_results := jsonb_set(
      v_results,
      ARRAY[v_phase],
      COALESCE(v_results -> v_phase, '{}'::JSONB) || jsonb_build_object('timing_ms', v_timing_ms),
      true
    );
    RAISE INFO 'Completed snapshot phase % in % ms', v_phase, v_timing_ms;
  END LOOP;

  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_run_all_snapshots',
    'ok',
    jsonb_build_object(
      'mode', CASE WHEN p_period IS NULL THEN 'phase-split-full' ELSE 'phase-split-scoped' END,
      'requested_period', p_period,
      'batch_size', p_batch_size,
      'phases', to_jsonb(v_requested_phases),
      'total_timing_ms', v_total_ms,
      'results', v_results
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_results::TEXT;
EXCEPTION WHEN OTHERS THEN
  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_run_all_snapshots',
    'error',
    jsonb_build_object(
      'mode', CASE WHEN p_period IS NULL THEN 'phase-split-full' ELSE 'phase-split-scoped' END,
      'requested_period', p_period,
      'batch_size', p_batch_size,
      'phases', to_jsonb(v_requested_phases),
      'error', SQLERRM
    ),
    v_started_at,
    clock_timestamp()
  );

  RAISE;
END;
$$;


--
-- Name: fn_run_snapshot_phase(text, tstzrange, integer); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_run_snapshot_phase(p_phase text, p_period tstzrange DEFAULT NULL::tstzrange, p_batch_size integer DEFAULT 50000) RETURNS jsonb
    LANGUAGE plpgsql SECURITY DEFINER
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


--
-- Name: fn_seed_daily_activity(tstzrange); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_seed_daily_activity(p_period tstzrange DEFAULT NULL::tstzrange) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
DECLARE
  v_rows BIGINT := 0;
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_completed_at TIMESTAMPTZ;
  v_elapsed_ms BIGINT := 0;
  v_period_start TIMESTAMPTZ;
  v_period_end TIMESTAMPTZ;
BEGIN
  IF p_period IS NOT NULL AND (
    lower_inc(p_period) IS DISTINCT FROM TRUE
    OR upper_inc(p_period) IS DISTINCT FROM FALSE
    OR lower(p_period) IS DISTINCT FROM date_trunc('day', lower(p_period))
    OR upper(p_period) IS DISTINCT FROM date_trunc('day', upper(p_period))
  ) THEN
    RAISE EXCEPTION 'p_period must use whole-day [) bounds'
      USING ERRCODE = '22023';
  END IF;

  v_period_start := CASE WHEN p_period IS NULL THEN NULL ELSE lower(p_period) END;
  v_period_end := CASE WHEN p_period IS NULL THEN NULL ELSE upper(p_period) END;

  IF p_period IS NULL THEN
    DELETE FROM stats.daily_active_users;
  ELSE
    DELETE FROM stats.daily_active_users
    WHERE tstzrange(
      activity_date::timestamp with time zone,
      (activity_date + 1)::timestamp with time zone,
      '[)'
    ) && p_period;
  END IF;

  INSERT INTO stats.daily_activity (
    activity_date,
    total_moves,
    drops,
    grabs,
    comments,
    sees,
    archives,
    dips,
    km_contributed,
    gk_created,
    pictures_uploaded_total,
    pictures_uploaded_avatar,
    pictures_uploaded_move,
    pictures_uploaded_user,
    users_registered
  )
  WITH activity_days AS (
    SELECT activity_date
    FROM stats.daily_activity
    WHERE p_period IS NULL
       OR tstzrange(
            activity_date::timestamp with time zone,
            (activity_date + 1)::timestamp with time zone,
            '[)'
          ) && p_period
    UNION
    SELECT moved_on_datetime::date AS activity_date
    FROM geokrety.gk_moves
    WHERE p_period IS NULL OR (moved_on_datetime >= v_period_start AND moved_on_datetime < v_period_end)
    UNION
    SELECT created_on_datetime::date
    FROM geokrety.gk_geokrety
    WHERE p_period IS NULL OR (created_on_datetime >= v_period_start AND created_on_datetime < v_period_end)
    UNION
    SELECT uploaded_on_datetime::date
    FROM geokrety.gk_pictures
    WHERE uploaded_on_datetime IS NOT NULL
      AND (p_period IS NULL OR (uploaded_on_datetime >= v_period_start AND uploaded_on_datetime < v_period_end))
    UNION
    SELECT joined_on_datetime::date
    FROM geokrety.gk_users
    WHERE p_period IS NULL OR (joined_on_datetime >= v_period_start AND joined_on_datetime < v_period_end)
  ),
  moves_daily AS (
    SELECT
      moved_on_datetime::date AS activity_date,
      COUNT(*)::BIGINT AS total_moves,
      COUNT(*) FILTER (WHERE move_type = 0)::BIGINT AS drops,
      COUNT(*) FILTER (WHERE move_type = 1)::BIGINT AS grabs,
      COUNT(*) FILTER (WHERE move_type = 2)::BIGINT AS comments,
      COUNT(*) FILTER (WHERE move_type = 3)::BIGINT AS sees,
      COUNT(*) FILTER (WHERE move_type = 4)::BIGINT AS archives,
      COUNT(*) FILTER (WHERE move_type = 5)::BIGINT AS dips,
      COALESCE(SUM(km_distance), 0)::NUMERIC(14,3) AS km_contributed
    FROM geokrety.gk_moves
    WHERE p_period IS NULL OR (moved_on_datetime >= v_period_start AND moved_on_datetime < v_period_end)
    GROUP BY moved_on_datetime::date
  ),
  geokrety_daily AS (
    SELECT
      created_on_datetime::date AS activity_date,
      COUNT(*)::BIGINT AS gk_created
    FROM geokrety.gk_geokrety
    WHERE p_period IS NULL OR (created_on_datetime >= v_period_start AND created_on_datetime < v_period_end)
    GROUP BY created_on_datetime::date
  ),
  pictures_daily AS (
    SELECT
      uploaded_on_datetime::date AS activity_date,
      COUNT(*)::BIGINT AS pictures_uploaded_total,
      COUNT(*) FILTER (WHERE type = 0)::BIGINT AS pictures_uploaded_avatar,
      COUNT(*) FILTER (WHERE type = 1)::BIGINT AS pictures_uploaded_move,
      COUNT(*) FILTER (WHERE type = 2)::BIGINT AS pictures_uploaded_user
    FROM geokrety.gk_pictures
    WHERE uploaded_on_datetime IS NOT NULL
      AND (p_period IS NULL OR (uploaded_on_datetime >= v_period_start AND uploaded_on_datetime < v_period_end))
    GROUP BY uploaded_on_datetime::date
  ),
  users_daily AS (
    SELECT
      joined_on_datetime::date AS activity_date,
      COUNT(*)::BIGINT AS users_registered
    FROM geokrety.gk_users
    WHERE p_period IS NULL OR (joined_on_datetime >= v_period_start AND joined_on_datetime < v_period_end)
    GROUP BY joined_on_datetime::date
  )
  SELECT
    activity_days.activity_date,
    COALESCE(moves_daily.total_moves, 0),
    COALESCE(moves_daily.drops, 0),
    COALESCE(moves_daily.grabs, 0),
    COALESCE(moves_daily.comments, 0),
    COALESCE(moves_daily.sees, 0),
    COALESCE(moves_daily.archives, 0),
    COALESCE(moves_daily.dips, 0),
    COALESCE(moves_daily.km_contributed, 0)::NUMERIC(14,3),
    COALESCE(geokrety_daily.gk_created, 0),
    COALESCE(pictures_daily.pictures_uploaded_total, 0),
    COALESCE(pictures_daily.pictures_uploaded_avatar, 0),
    COALESCE(pictures_daily.pictures_uploaded_move, 0),
    COALESCE(pictures_daily.pictures_uploaded_user, 0),
    COALESCE(users_daily.users_registered, 0)
  FROM activity_days
  LEFT JOIN moves_daily USING (activity_date)
  LEFT JOIN geokrety_daily USING (activity_date)
  LEFT JOIN pictures_daily USING (activity_date)
  LEFT JOIN users_daily USING (activity_date)
  ORDER BY activity_days.activity_date
  ON CONFLICT (activity_date) DO UPDATE SET
    total_moves = EXCLUDED.total_moves,
    drops = EXCLUDED.drops,
    grabs = EXCLUDED.grabs,
    comments = EXCLUDED.comments,
    sees = EXCLUDED.sees,
    archives = EXCLUDED.archives,
    dips = EXCLUDED.dips,
    km_contributed = EXCLUDED.km_contributed,
    gk_created = EXCLUDED.gk_created,
    pictures_uploaded_total = EXCLUDED.pictures_uploaded_total,
    pictures_uploaded_avatar = EXCLUDED.pictures_uploaded_avatar,
    pictures_uploaded_move = EXCLUDED.pictures_uploaded_move,
    pictures_uploaded_user = EXCLUDED.pictures_uploaded_user,
    users_registered = EXCLUDED.users_registered;

  GET DIAGNOSTICS v_rows = ROW_COUNT;

  INSERT INTO stats.daily_active_users (activity_date, user_id)
  SELECT DISTINCT
    moved_on_datetime::date AS activity_date,
    author
  FROM geokrety.gk_moves
  WHERE author IS NOT NULL
    AND (p_period IS NULL OR (moved_on_datetime >= v_period_start AND moved_on_datetime < v_period_end))
  ON CONFLICT (activity_date, user_id) DO NOTHING;

  DELETE FROM stats.daily_activity AS activity
  WHERE (p_period IS NULL OR tstzrange(
          activity.activity_date::timestamp with time zone,
          (activity.activity_date + 1)::timestamp with time zone,
          '[)'
        ) && p_period)
    AND activity.total_moves = 0
    AND activity.drops = 0
    AND activity.grabs = 0
    AND activity.comments = 0
    AND activity.sees = 0
    AND activity.archives = 0
    AND activity.dips = 0
    AND activity.km_contributed = 0
    AND activity.gk_created = 0
    AND activity.pictures_uploaded_total = 0
    AND activity.pictures_uploaded_avatar = 0
    AND activity.pictures_uploaded_move = 0
    AND activity.pictures_uploaded_user = 0
    AND activity.users_registered = 0
    AND COALESCE(activity.points_contributed, 0) = 0
    AND COALESCE(activity.loves_count, 0) = 0;

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
    'fn_seed_daily_activity',
    'ok',
    jsonb_build_object(
      'rows_affected', v_rows,
      'requested_period', p_period,
      'timing_ms', v_elapsed_ms
    ),
    v_started_at,
    v_completed_at
  );

  RAISE INFO 'fn_seed_daily_activity completed in % ms (requested_period=% rows=%)', v_elapsed_ms, p_period, v_rows;
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
    'fn_seed_daily_activity',
    'error',
    jsonb_build_object('error', SQLERRM, 'requested_period', p_period, 'timing_ms', v_elapsed_ms),
    v_started_at,
    v_completed_at
  );

  RAISE INFO 'fn_seed_daily_activity failed after % ms (requested_period=%): %', v_elapsed_ms, p_period, SQLERRM;

  RAISE;
END;
$$;


--
-- Name: FUNCTION fn_seed_daily_activity(p_period tstzrange); Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON FUNCTION stats.fn_seed_daily_activity(p_period tstzrange) IS 'Idempotent backfill of daily_activity and daily_active_users from source tables; p_period limits to a date range; pass NULL to seed all history';


--
-- Name: fn_seed_waypoints(); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_seed_waypoints() RETURNS bigint
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


--
-- Name: fn_snapshot_cache_visits(daterange); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_cache_visits(p_period daterange DEFAULT NULL::daterange) RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
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


--
-- Name: fn_snapshot_country_pair_flows(); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_country_pair_flows() RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_upserted_rows BIGINT := 0;
  v_deleted_rows BIGINT := 0;
  v_total_rows BIGINT := 0;
BEGIN
  DROP TABLE IF EXISTS tmp_country_pair_flow_snapshot;

  CREATE TEMP TABLE tmp_country_pair_flow_snapshot ON COMMIT DROP AS
  SELECT
    date_trunc('month', timezone('UTC', current_move.moved_on_datetime))::DATE AS year_month,
    UPPER(BTRIM(previous_move.country))::CHAR(2) AS from_country,
    UPPER(BTRIM(current_move.country))::CHAR(2) AS to_country,
    COUNT(*)::BIGINT AS move_count,
    COUNT(DISTINCT current_move.geokret)::BIGINT AS unique_gk_count
  FROM geokrety.gk_moves current_move
  JOIN geokrety.gk_moves previous_move
    ON previous_move.id = current_move.previous_move_id
  WHERE current_move.previous_move_id IS NOT NULL
    AND current_move.move_type IN (0, 1, 3, 5)
    AND previous_move.move_type IN (0, 1, 3, 5)
    AND previous_move.country IS NOT NULL
    AND current_move.country IS NOT NULL
    AND BTRIM(previous_move.country) <> ''
    AND BTRIM(current_move.country) <> ''
    AND UPPER(BTRIM(previous_move.country)) <> UPPER(BTRIM(current_move.country))
  GROUP BY 1, 2, 3;

  INSERT INTO stats.country_pair_flows (
    year_month,
    from_country,
    to_country,
    move_count,
    unique_gk_count
  )
  SELECT
    year_month,
    from_country,
    to_country,
    move_count,
    unique_gk_count
  FROM tmp_country_pair_flow_snapshot
  ON CONFLICT (year_month, from_country, to_country) DO UPDATE SET
    move_count = EXCLUDED.move_count,
    unique_gk_count = EXCLUDED.unique_gk_count;

  GET DIAGNOSTICS v_upserted_rows = ROW_COUNT;

  DELETE FROM stats.country_pair_flows target
  WHERE NOT EXISTS (
    SELECT 1
    FROM tmp_country_pair_flow_snapshot snapshot
    WHERE snapshot.year_month = target.year_month
      AND snapshot.from_country = target.from_country
      AND snapshot.to_country = target.to_country
  );

  GET DIAGNOSTICS v_deleted_rows = ROW_COUNT;
  v_total_rows := v_upserted_rows + v_deleted_rows;

  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_snapshot_country_pair_flows',
    'completed',
    jsonb_build_object(
      'upserted_rows', v_upserted_rows,
      'deleted_rows', v_deleted_rows,
      'snapshot_rows', (SELECT COUNT(*)::BIGINT FROM tmp_country_pair_flow_snapshot)
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_total_rows;
EXCEPTION WHEN OTHERS THEN
  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_snapshot_country_pair_flows',
    'failed',
    jsonb_build_object('error', SQLERRM),
    v_started_at,
    clock_timestamp()
  );

  RAISE;
END;
$$;


--
-- Name: fn_snapshot_country_pair_flows(tstzrange); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_country_pair_flows(p_period tstzrange) RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_completed_at TIMESTAMPTZ;
  v_upserted_rows BIGINT := 0;
  v_deleted_rows BIGINT := 0;
  v_total_rows BIGINT := 0;
  v_elapsed_ms BIGINT := 0;
  v_period_start TIMESTAMPTZ;
  v_period_end TIMESTAMPTZ;
BEGIN
  IF p_period IS NULL THEN
    RETURN stats.fn_snapshot_country_pair_flows();
  END IF;

  v_period_start := lower(p_period);
  v_period_end := upper(p_period);

  DROP TABLE IF EXISTS tmp_country_pair_flow_months;
  DROP TABLE IF EXISTS tmp_country_pair_flow_snapshot;

  CREATE TEMP TABLE tmp_country_pair_flow_months ON COMMIT DROP AS
  SELECT DISTINCT
    date_trunc('month', timezone('UTC', current_move.moved_on_datetime))::DATE AS year_month
  FROM geokrety.gk_moves current_move
  WHERE current_move.previous_move_id IS NOT NULL
    AND current_move.move_type IN (0, 1, 3, 5)
    AND current_move.moved_on_datetime >= v_period_start
    AND current_move.moved_on_datetime < v_period_end;

  CREATE TEMP TABLE tmp_country_pair_flow_snapshot ON COMMIT DROP AS
  SELECT
    date_trunc('month', timezone('UTC', current_move.moved_on_datetime))::DATE AS year_month,
    UPPER(BTRIM(previous_move.country))::CHAR(2) AS from_country,
    UPPER(BTRIM(current_move.country))::CHAR(2) AS to_country,
    COUNT(*)::BIGINT AS move_count,
    COUNT(DISTINCT current_move.geokret)::BIGINT AS unique_gk_count
  FROM geokrety.gk_moves current_move
  JOIN geokrety.gk_moves previous_move
    ON previous_move.id = current_move.previous_move_id
  JOIN tmp_country_pair_flow_months touched_months
    ON touched_months.year_month = date_trunc('month', timezone('UTC', current_move.moved_on_datetime))::DATE
  WHERE current_move.previous_move_id IS NOT NULL
    AND current_move.move_type IN (0, 1, 3, 5)
    AND previous_move.move_type IN (0, 1, 3, 5)
    AND previous_move.country IS NOT NULL
    AND current_move.country IS NOT NULL
    AND BTRIM(previous_move.country) <> ''
    AND BTRIM(current_move.country) <> ''
    AND UPPER(BTRIM(previous_move.country)) <> UPPER(BTRIM(current_move.country))
  GROUP BY 1, 2, 3;

  INSERT INTO stats.country_pair_flows (
    year_month,
    from_country,
    to_country,
    move_count,
    unique_gk_count
  )
  SELECT
    year_month,
    from_country,
    to_country,
    move_count,
    unique_gk_count
  FROM tmp_country_pair_flow_snapshot
  ON CONFLICT (year_month, from_country, to_country) DO UPDATE SET
    move_count = EXCLUDED.move_count,
    unique_gk_count = EXCLUDED.unique_gk_count;

  GET DIAGNOSTICS v_upserted_rows = ROW_COUNT;

  DELETE FROM stats.country_pair_flows target
  WHERE EXISTS (
      SELECT 1
      FROM tmp_country_pair_flow_months touched_months
      WHERE touched_months.year_month = target.year_month
    )
    AND NOT EXISTS (
      SELECT 1
      FROM tmp_country_pair_flow_snapshot snapshot
      WHERE snapshot.year_month = target.year_month
        AND snapshot.from_country = target.from_country
        AND snapshot.to_country = target.to_country
    );

  GET DIAGNOSTICS v_deleted_rows = ROW_COUNT;
  v_total_rows := v_upserted_rows + v_deleted_rows;
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
    'fn_snapshot_country_pair_flows',
    'completed',
    jsonb_build_object(
      'requested_period', p_period,
      'upserted_rows', v_upserted_rows,
      'deleted_rows', v_deleted_rows,
      'snapshot_rows', (SELECT COUNT(*)::BIGINT FROM tmp_country_pair_flow_snapshot),
      'timing_ms', v_elapsed_ms
    ),
    v_started_at,
    v_completed_at
  );

  RAISE INFO 'fn_snapshot_country_pair_flows completed in % ms (requested_period=% rows=%)', v_elapsed_ms, p_period, v_total_rows;

  RETURN v_total_rows;
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
    'fn_snapshot_country_pair_flows',
    'failed',
    jsonb_build_object('requested_period', p_period, 'error', SQLERRM, 'timing_ms', v_elapsed_ms),
    v_started_at,
    v_completed_at
  );

  RAISE INFO 'fn_snapshot_country_pair_flows failed after % ms (requested_period=%): %', v_elapsed_ms, p_period, SQLERRM;

  RAISE;
END;
$$;


--
-- Name: fn_snapshot_daily_country_stats(daterange); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_daily_country_stats(p_period daterange DEFAULT NULL::daterange) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
DECLARE
  v_count BIGINT := 0;
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_completed_at TIMESTAMPTZ;
  v_elapsed_ms BIGINT := 0;
  v_period_start TIMESTAMPTZ;
  v_period_end TIMESTAMPTZ;
BEGIN
  v_period_start := CASE WHEN p_period IS NULL THEN NULL ELSE lower(p_period)::TIMESTAMPTZ END;
  v_period_end := CASE WHEN p_period IS NULL THEN NULL ELSE upper(p_period)::TIMESTAMPTZ END;

  INSERT INTO stats.country_daily_stats (
    stats_date,
    country_code,
    moves_count,
    drops,
    grabs,
    comments,
    sees,
    archives,
    dips,
    unique_users,
    unique_gks,
    km_contributed
  )
  SELECT
    m.moved_on_datetime::date AS stats_date,
    geokrety.fn_normalize_country_code(m.country) AS country_code,
    COUNT(*)::BIGINT AS moves_count,
    COUNT(*) FILTER (WHERE m.move_type = 0)::BIGINT AS drops,
    COUNT(*) FILTER (WHERE m.move_type = 1)::BIGINT AS grabs,
    COUNT(*) FILTER (WHERE m.move_type = 2)::BIGINT AS comments,
    COUNT(*) FILTER (WHERE m.move_type = 3)::BIGINT AS sees,
    COUNT(*) FILTER (WHERE m.move_type = 4)::BIGINT AS archives,
    COUNT(*) FILTER (WHERE m.move_type = 5)::BIGINT AS dips,
    COUNT(DISTINCT m.author) FILTER (WHERE m.author IS NOT NULL)::BIGINT AS unique_users,
    COUNT(DISTINCT m.geokret)::BIGINT AS unique_gks,
    COALESCE(SUM(m.km_distance), 0)::NUMERIC(14,3) AS km_contributed
  FROM geokrety.gk_moves m
  WHERE m.country IS NOT NULL
    AND (p_period IS NULL OR (m.moved_on_datetime >= v_period_start AND m.moved_on_datetime < v_period_end))
  GROUP BY m.moved_on_datetime::date, geokrety.fn_normalize_country_code(m.country)
  ON CONFLICT (stats_date, country_code) DO UPDATE SET
    moves_count = EXCLUDED.moves_count,
    drops = EXCLUDED.drops,
    grabs = EXCLUDED.grabs,
    comments = EXCLUDED.comments,
    sees = EXCLUDED.sees,
    archives = EXCLUDED.archives,
    dips = EXCLUDED.dips,
    unique_users = EXCLUDED.unique_users,
    unique_gks = EXCLUDED.unique_gks,
    km_contributed = EXCLUDED.km_contributed;

  GET DIAGNOSTICS v_count = ROW_COUNT;

  IF p_period IS NULL THEN
    UPDATE stats.country_daily_stats cds
       SET moves_count = 0,
           drops = 0,
           grabs = 0,
           comments = 0,
           sees = 0,
           archives = 0,
           dips = 0,
           unique_users = 0,
           unique_gks = 0,
           km_contributed = 0
     WHERE NOT EXISTS (
       SELECT 1
       FROM geokrety.gk_moves m
       WHERE m.country IS NOT NULL
         AND m.moved_on_datetime::date = cds.stats_date
       AND geokrety.fn_normalize_country_code(m.country) = cds.country_code
     )
       AND (
         cds.points_contributed <> 0
         OR cds.loves_count <> 0
         OR cds.pictures_uploaded_total <> 0
         OR cds.pictures_uploaded_avatar <> 0
         OR cds.pictures_uploaded_move <> 0
         OR cds.pictures_uploaded_user <> 0
       );

    DELETE FROM stats.country_daily_stats cds
     WHERE NOT EXISTS (
       SELECT 1
       FROM geokrety.gk_moves m
       WHERE m.country IS NOT NULL
         AND m.moved_on_datetime::date = cds.stats_date
         AND geokrety.fn_normalize_country_code(m.country) = cds.country_code
     )
       AND cds.points_contributed = 0
       AND cds.loves_count = 0
       AND cds.pictures_uploaded_total = 0
       AND cds.pictures_uploaded_avatar = 0
       AND cds.pictures_uploaded_move = 0
       AND cds.pictures_uploaded_user = 0;
  ELSE
    UPDATE stats.country_daily_stats cds
       SET moves_count = 0,
           drops = 0,
           grabs = 0,
           comments = 0,
           sees = 0,
           archives = 0,
           dips = 0,
           unique_users = 0,
           unique_gks = 0,
           km_contributed = 0
     WHERE cds.stats_date <@ p_period
       AND NOT EXISTS (
         SELECT 1
         FROM geokrety.gk_moves m
         WHERE m.country IS NOT NULL
           AND m.moved_on_datetime::date = cds.stats_date
           AND geokrety.fn_normalize_country_code(m.country) = cds.country_code
       )
       AND (
         cds.points_contributed <> 0
         OR cds.loves_count <> 0
         OR cds.pictures_uploaded_total <> 0
         OR cds.pictures_uploaded_avatar <> 0
         OR cds.pictures_uploaded_move <> 0
         OR cds.pictures_uploaded_user <> 0
       );

    DELETE FROM stats.country_daily_stats cds
     WHERE cds.stats_date <@ p_period
       AND NOT EXISTS (
         SELECT 1
         FROM geokrety.gk_moves m
         WHERE m.country IS NOT NULL
           AND m.moved_on_datetime::date = cds.stats_date
           AND geokrety.fn_normalize_country_code(m.country) = cds.country_code
       )
       AND cds.points_contributed = 0
       AND cds.loves_count = 0
       AND cds.pictures_uploaded_total = 0
       AND cds.pictures_uploaded_avatar = 0
       AND cds.pictures_uploaded_move = 0
       AND cds.pictures_uploaded_user = 0;
  END IF;

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
    'fn_snapshot_daily_country_stats',
    'ok',
    jsonb_build_object('rows_affected', v_count, 'requested_period', p_period, 'timing_ms', v_elapsed_ms),
    v_started_at,
    v_completed_at
  );

  RAISE INFO 'fn_snapshot_daily_country_stats completed in % ms (requested_period=% rows=%)', v_elapsed_ms, p_period, v_count;

  RETURN v_count;
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
    'fn_snapshot_daily_country_stats',
    'error',
    jsonb_build_object('error', SQLERRM, 'requested_period', p_period, 'timing_ms', v_elapsed_ms),
    v_started_at,
    v_completed_at
  );

  RAISE INFO 'fn_snapshot_daily_country_stats failed after % ms (requested_period=%): %', v_elapsed_ms, p_period, SQLERRM;

  RAISE;
END;
$$;


--
-- Name: FUNCTION fn_snapshot_daily_country_stats(p_period daterange); Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON FUNCTION stats.fn_snapshot_daily_country_stats(p_period daterange) IS 'Seeds country_daily_stats from gk_moves. Idempotent via ON CONFLICT DO UPDATE. Optional p_period limits date range.';


--
-- Name: fn_snapshot_daily_entity_counts(); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_daily_entity_counts() RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
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


--
-- Name: FUNCTION fn_snapshot_daily_entity_counts(); Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON FUNCTION stats.fn_snapshot_daily_entity_counts() IS 'Rebuilds daily_entity_counts as cumulative end-of-day totals for the canonical 25 entity counters.';


--
-- Name: fn_snapshot_entity_counters(); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_entity_counters() RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_completed_at TIMESTAMPTZ;
  v_elapsed_ms BIGINT := 0;
BEGIN
  DELETE FROM stats.entity_counters_shard;

  INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
  SELECT
    entities.entity,
    shards.shard,
    COALESCE(entity_totals.cnt, 0) AS cnt
  FROM (
    VALUES
      ('gk_moves'), ('gk_moves_type_0'), ('gk_moves_type_1'), ('gk_moves_type_2'),
      ('gk_moves_type_3'), ('gk_moves_type_4'), ('gk_moves_type_5'),
      ('gk_geokrety'), ('gk_geokrety_type_0'), ('gk_geokrety_type_1'),
      ('gk_geokrety_type_2'), ('gk_geokrety_type_3'), ('gk_geokrety_type_4'),
      ('gk_geokrety_type_5'), ('gk_geokrety_type_6'), ('gk_geokrety_type_7'),
      ('gk_geokrety_type_8'), ('gk_geokrety_type_9'), ('gk_geokrety_type_10'),
      ('gk_pictures'), ('gk_pictures_type_0'), ('gk_pictures_type_1'),
      ('gk_pictures_type_2'), ('gk_users'), ('gk_loves')
  ) AS entities(entity)
  CROSS JOIN generate_series(0, 15) AS shards(shard)
  LEFT JOIN (
    SELECT 'gk_moves'::TEXT AS entity, (id % 16) AS shard, COUNT(*)::BIGINT AS cnt
    FROM geokrety.gk_moves
    GROUP BY (id % 16)
    UNION ALL
    SELECT format('gk_moves_type_%s', move_type), (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_moves
    GROUP BY move_type, (id % 16)
    UNION ALL
    SELECT 'gk_geokrety'::TEXT, (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_geokrety
    GROUP BY (id % 16)
    UNION ALL
    SELECT format('gk_geokrety_type_%s', type), (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_geokrety
    GROUP BY type, (id % 16)
    UNION ALL
    SELECT 'gk_pictures'::TEXT, (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_pictures
    WHERE uploaded_on_datetime IS NOT NULL
    GROUP BY (id % 16)
    UNION ALL
    SELECT format('gk_pictures_type_%s', type), (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_pictures
    WHERE uploaded_on_datetime IS NOT NULL
    GROUP BY type, (id % 16)
    UNION ALL
    SELECT 'gk_users'::TEXT, (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_users
    GROUP BY (id % 16)
    UNION ALL
    SELECT 'gk_loves'::TEXT, (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_loves
    GROUP BY (id % 16)
  ) AS entity_totals USING (entity, shard);

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
    'fn_snapshot_entity_counters',
    'ok',
    jsonb_build_object(
      'entities_refreshed', 25,
      'rows_affected', (SELECT COUNT(*)::BIGINT FROM stats.entity_counters_shard),
      'timing_ms', v_elapsed_ms
    ),
    v_started_at,
    v_completed_at
  );

  RAISE INFO 'fn_snapshot_entity_counters completed in % ms', v_elapsed_ms;
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
    'fn_snapshot_entity_counters',
    'error',
    jsonb_build_object('error', SQLERRM, 'timing_ms', v_elapsed_ms),
    v_started_at,
    v_completed_at
  );

  RAISE INFO 'fn_snapshot_entity_counters failed after % ms: %', v_elapsed_ms, SQLERRM;

  RAISE;
END;
$$;


--
-- Name: FUNCTION fn_snapshot_entity_counters(); Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON FUNCTION stats.fn_snapshot_entity_counters() IS 'Seeds entity_counters_shard from current source table counts; idempotent; run once during Sprint 6 backfill';


--
-- Name: fn_snapshot_first_finder_events(); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_first_finder_events() RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
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


--
-- Name: FUNCTION fn_snapshot_first_finder_events(); Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON FUNCTION stats.fn_snapshot_first_finder_events() IS 'Rebuilds first_finder_events from the earliest qualifying non-owner authenticated move within the 168-hour eligibility window.';


--
-- Name: fn_snapshot_gk_country_history(); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_gk_country_history() RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
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
  WITH qualifying_moves AS (
    SELECT
      normalized_moves.move_id,
      normalized_moves.geokrety_id,
      normalized_moves.country_code,
      normalized_moves.arrived_at
    FROM (
      SELECT
        m.id AS move_id,
        m.geokret AS geokrety_id,
        geokrety.fn_normalize_country_code(m.country) AS country_code,
        m.moved_on_datetime AS arrived_at
      FROM geokrety.gk_moves m
      WHERE m.country IS NOT NULL
        AND m.move_type IN (0, 1, 3, 5)
    ) AS normalized_moves
    WHERE normalized_moves.country_code IS NOT NULL
  ),
  country_transitions AS (
    SELECT
      ordered_moves.move_id,
      ordered_moves.geokrety_id,
      ordered_moves.country_code,
      ordered_moves.arrived_at
    FROM (
      SELECT
        qualifying_moves.move_id,
        qualifying_moves.geokrety_id,
        qualifying_moves.country_code,
        qualifying_moves.arrived_at,
        LAG(qualifying_moves.country_code) OVER (
          PARTITION BY qualifying_moves.geokrety_id
          ORDER BY qualifying_moves.arrived_at, qualifying_moves.move_id
        ) AS previous_country_code
      FROM qualifying_moves
    ) AS ordered_moves
    WHERE ordered_moves.previous_country_code IS DISTINCT FROM ordered_moves.country_code
  )
  SELECT
    country_transitions.geokrety_id,
    country_transitions.country_code,
    country_transitions.arrived_at,
    LEAD(country_transitions.arrived_at) OVER (
      PARTITION BY country_transitions.geokrety_id
      ORDER BY country_transitions.arrived_at, country_transitions.move_id
    ) AS departed_at,
    country_transitions.move_id
  FROM country_transitions;

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


--
-- Name: FUNCTION fn_snapshot_gk_country_history(); Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON FUNCTION stats.fn_snapshot_gk_country_history() IS 'Rebuilds gk_country_history from qualifying move chronology using the canonical country normalization rules.';


--
-- Name: fn_snapshot_gk_country_stats(daterange); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_gk_country_stats(p_period daterange DEFAULT NULL::daterange) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
DECLARE
  v_count BIGINT := 0;
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_completed_at TIMESTAMPTZ;
  v_elapsed_ms BIGINT := 0;
  v_period_start TIMESTAMPTZ;
  v_period_end TIMESTAMPTZ;
BEGIN
  v_period_start := CASE WHEN p_period IS NULL THEN NULL ELSE lower(p_period)::TIMESTAMPTZ END;
  v_period_end := CASE WHEN p_period IS NULL THEN NULL ELSE upper(p_period)::TIMESTAMPTZ END;

  IF p_period IS NULL THEN
    INSERT INTO stats.gk_countries_visited (
      geokrety_id,
      country_code,
      first_visited_at,
      first_move_id,
      move_count
    )
    SELECT
      sub.geokret AS geokrety_id,
      sub.country AS country_code,
      sub.first_visited_at,
      sub.first_move_id,
      sub.move_count
    FROM (
      SELECT
        m.geokret,
        geokrety.fn_normalize_country_code(m.country) AS country,
        MIN(m.moved_on_datetime) AS first_visited_at,
        (array_agg(m.id ORDER BY m.moved_on_datetime ASC, m.id ASC))[1] AS first_move_id,
        COUNT(*)::INT AS move_count
      FROM geokrety.gk_moves m
      WHERE m.country IS NOT NULL
      GROUP BY m.geokret, geokrety.fn_normalize_country_code(m.country)
    ) sub
    ON CONFLICT (geokrety_id, country_code) DO UPDATE SET
      move_count = EXCLUDED.move_count,
      first_visited_at = EXCLUDED.first_visited_at,
      first_move_id = EXCLUDED.first_move_id;
  ELSE
    WITH touched_keys AS (
      SELECT DISTINCT
        m.geokret,
        geokrety.fn_normalize_country_code(m.country) AS country
      FROM geokrety.gk_moves m
      WHERE m.country IS NOT NULL
        AND m.moved_on_datetime >= v_period_start
        AND m.moved_on_datetime < v_period_end
    )
    INSERT INTO stats.gk_countries_visited (
      geokrety_id,
      country_code,
      first_visited_at,
      first_move_id,
      move_count
    )
    SELECT
      sub.geokret AS geokrety_id,
      sub.country AS country_code,
      sub.first_visited_at,
      sub.first_move_id,
      sub.move_count
    FROM (
      SELECT
        m.geokret,
        geokrety.fn_normalize_country_code(m.country) AS country,
        MIN(m.moved_on_datetime) AS first_visited_at,
        (array_agg(m.id ORDER BY m.moved_on_datetime ASC, m.id ASC))[1] AS first_move_id,
        COUNT(*)::INT AS move_count
      FROM geokrety.gk_moves m
      JOIN touched_keys tk
        ON tk.geokret = m.geokret
       AND tk.country = geokrety.fn_normalize_country_code(m.country)
      WHERE m.country IS NOT NULL
      GROUP BY m.geokret, geokrety.fn_normalize_country_code(m.country)
    ) sub
    ON CONFLICT (geokrety_id, country_code) DO UPDATE SET
      move_count = EXCLUDED.move_count,
      first_visited_at = EXCLUDED.first_visited_at,
      first_move_id = EXCLUDED.first_move_id;
  END IF;

  GET DIAGNOSTICS v_count = ROW_COUNT;

  IF p_period IS NULL THEN
    DELETE FROM stats.gk_countries_visited gcv
     WHERE NOT EXISTS (
       SELECT 1
       FROM geokrety.gk_moves m
       WHERE m.country IS NOT NULL
         AND m.geokret = gcv.geokrety_id
         AND geokrety.fn_normalize_country_code(m.country) = gcv.country_code
     );
  ELSE
    DELETE FROM stats.gk_countries_visited gcv
     WHERE EXISTS (
       SELECT 1
       FROM geokrety.gk_moves scoped_moves
       WHERE scoped_moves.country IS NOT NULL
        AND scoped_moves.moved_on_datetime >= v_period_start
        AND scoped_moves.moved_on_datetime < v_period_end
         AND scoped_moves.geokret = gcv.geokrety_id
         AND geokrety.fn_normalize_country_code(scoped_moves.country) = gcv.country_code
     )
       AND NOT EXISTS (
         SELECT 1
         FROM geokrety.gk_moves m
         WHERE m.country IS NOT NULL
           AND m.geokret = gcv.geokrety_id
           AND geokrety.fn_normalize_country_code(m.country) = gcv.country_code
       );
  END IF;

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
    'fn_snapshot_gk_country_stats',
    'ok',
    jsonb_build_object('rows_affected', v_count, 'requested_period', p_period, 'timing_ms', v_elapsed_ms),
    v_started_at,
    clock_timestamp()
  );

  RAISE INFO 'fn_snapshot_gk_country_stats completed in % ms (requested_period=% rows=%)', v_elapsed_ms, p_period, v_count;

  RETURN v_count;
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
    'fn_snapshot_gk_country_stats',
    'error',
    jsonb_build_object('error', SQLERRM, 'requested_period', p_period, 'timing_ms', v_elapsed_ms),
    v_started_at,
    clock_timestamp()
  );

  RAISE INFO 'fn_snapshot_gk_country_stats failed after % ms (requested_period=%): %', v_elapsed_ms, p_period, SQLERRM;

  RAISE;
END;
$$;


--
-- Name: FUNCTION fn_snapshot_gk_country_stats(p_period daterange); Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON FUNCTION stats.fn_snapshot_gk_country_stats(p_period daterange) IS 'Seeds gk_countries_visited from gk_moves. Optional p_period recomputes only touched geokret-country keys using full source history for exact aggregates.';


--
-- Name: fn_snapshot_gk_milestone_events(); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_gk_milestone_events() RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
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
  WITH qualifying_moves AS MATERIALIZED (
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
  km_running_totals AS (
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
  km_crossings AS (
    SELECT
      km_running_totals.gk_id,
      km_running_totals.move_id,
      km_running_totals.actor_user_id,
      km_running_totals.occurred_at,
      km_running_totals.running_km,
      LAG(km_running_totals.running_km, 1, 0::NUMERIC) OVER (
        PARTITION BY km_running_totals.gk_id
        ORDER BY km_running_totals.occurred_at, km_running_totals.move_id
      ) AS previous_running_km
    FROM km_running_totals
  ),
  km_events AS (
    SELECT
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
      ON km_crossings.previous_running_km < km_thresholds.threshold_value
     AND km_crossings.running_km >= km_thresholds.threshold_value
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
      SELECT DISTINCT ON (qualifying_moves.gk_id, qualifying_moves.actor_user_id)
        qualifying_moves.gk_id,
        qualifying_moves.actor_user_id,
        qualifying_moves.move_id,
        qualifying_moves.occurred_at
      FROM qualifying_moves
      WHERE qualifying_moves.actor_user_id IS NOT NULL
      ORDER BY qualifying_moves.gk_id, qualifying_moves.actor_user_id, qualifying_moves.occurred_at, qualifying_moves.move_id
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


--
-- Name: FUNCTION fn_snapshot_gk_milestone_events(); Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON FUNCTION stats.fn_snapshot_gk_milestone_events() IS 'Rebuilds milestone events from qualifying move history and canonical first_finder_events rows.';


--
-- Name: fn_snapshot_hourly_activity(); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_hourly_activity() RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_upserted_rows BIGINT := 0;
  v_deleted_rows BIGINT := 0;
  v_total_rows BIGINT := 0;
BEGIN
  DROP TABLE IF EXISTS tmp_hourly_activity_snapshot;

  CREATE TEMP TABLE tmp_hourly_activity_snapshot ON COMMIT DROP AS
  SELECT
    (timezone('UTC', m.moved_on_datetime))::DATE AS activity_date,
    EXTRACT(HOUR FROM timezone('UTC', m.moved_on_datetime))::SMALLINT AS hour_utc,
    m.move_type::SMALLINT AS move_type,
    COUNT(*)::BIGINT AS move_count
  FROM geokrety.gk_moves m
  WHERE m.move_type BETWEEN 0 AND 5
  GROUP BY 1, 2, 3;

  INSERT INTO stats.hourly_activity (
    activity_date,
    hour_utc,
    move_type,
    move_count
  )
  SELECT
    activity_date,
    hour_utc,
    move_type,
    move_count
  FROM tmp_hourly_activity_snapshot
  ON CONFLICT (activity_date, hour_utc, move_type) DO UPDATE SET
    move_count = EXCLUDED.move_count;

  GET DIAGNOSTICS v_upserted_rows = ROW_COUNT;

  DELETE FROM stats.hourly_activity target
  WHERE NOT EXISTS (
    SELECT 1
    FROM tmp_hourly_activity_snapshot snapshot
    WHERE snapshot.activity_date = target.activity_date
      AND snapshot.hour_utc = target.hour_utc
      AND snapshot.move_type = target.move_type
  );

  GET DIAGNOSTICS v_deleted_rows = ROW_COUNT;
  v_total_rows := v_upserted_rows + v_deleted_rows;

  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_snapshot_hourly_activity',
    'completed',
    jsonb_build_object(
      'upserted_rows', v_upserted_rows,
      'deleted_rows', v_deleted_rows,
      'snapshot_rows', (SELECT COUNT(*)::BIGINT FROM tmp_hourly_activity_snapshot)
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_total_rows;
EXCEPTION WHEN OTHERS THEN
  INSERT INTO stats.job_log (
    job_name,
    status,
    metadata,
    started_at,
    completed_at
  )
  VALUES (
    'fn_snapshot_hourly_activity',
    'failed',
    jsonb_build_object('error', SQLERRM),
    v_started_at,
    clock_timestamp()
  );

  RAISE;
END;
$$;


--
-- Name: fn_snapshot_hourly_activity(tstzrange); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_hourly_activity(p_period tstzrange) RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_completed_at TIMESTAMPTZ;
  v_upserted_rows BIGINT := 0;
  v_deleted_rows BIGINT := 0;
  v_total_rows BIGINT := 0;
  v_elapsed_ms BIGINT := 0;
  v_period_start TIMESTAMPTZ;
  v_period_end TIMESTAMPTZ;
BEGIN
  IF p_period IS NULL THEN
    RETURN stats.fn_snapshot_hourly_activity();
  END IF;

  v_period_start := lower(p_period);
  v_period_end := upper(p_period);

  DROP TABLE IF EXISTS tmp_hourly_activity_days;
  DROP TABLE IF EXISTS tmp_hourly_activity_snapshot;

  CREATE TEMP TABLE tmp_hourly_activity_days ON COMMIT DROP AS
  SELECT DISTINCT
    (timezone('UTC', m.moved_on_datetime))::DATE AS activity_date
  FROM geokrety.gk_moves m
  WHERE m.move_type BETWEEN 0 AND 5
    AND m.moved_on_datetime >= v_period_start
    AND m.moved_on_datetime < v_period_end;

  CREATE TEMP TABLE tmp_hourly_activity_snapshot ON COMMIT DROP AS
  SELECT
    (timezone('UTC', m.moved_on_datetime))::DATE AS activity_date,
    EXTRACT(HOUR FROM timezone('UTC', m.moved_on_datetime))::SMALLINT AS hour_utc,
    m.move_type::SMALLINT AS move_type,
    COUNT(*)::BIGINT AS move_count
  FROM geokrety.gk_moves m
  JOIN tmp_hourly_activity_days touched_days
    ON touched_days.activity_date = (timezone('UTC', m.moved_on_datetime))::DATE
  WHERE m.move_type BETWEEN 0 AND 5
  GROUP BY 1, 2, 3;

  INSERT INTO stats.hourly_activity (
    activity_date,
    hour_utc,
    move_type,
    move_count
  )
  SELECT
    activity_date,
    hour_utc,
    move_type,
    move_count
  FROM tmp_hourly_activity_snapshot
  ON CONFLICT (activity_date, hour_utc, move_type) DO UPDATE SET
    move_count = EXCLUDED.move_count;

  GET DIAGNOSTICS v_upserted_rows = ROW_COUNT;

  DELETE FROM stats.hourly_activity target
  WHERE EXISTS (
      SELECT 1
      FROM tmp_hourly_activity_days touched_days
      WHERE touched_days.activity_date = target.activity_date
    )
    AND NOT EXISTS (
      SELECT 1
      FROM tmp_hourly_activity_snapshot snapshot
      WHERE snapshot.activity_date = target.activity_date
        AND snapshot.hour_utc = target.hour_utc
        AND snapshot.move_type = target.move_type
    );

  GET DIAGNOSTICS v_deleted_rows = ROW_COUNT;
  v_total_rows := v_upserted_rows + v_deleted_rows;
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
    'fn_snapshot_hourly_activity',
    'completed',
    jsonb_build_object(
      'requested_period', p_period,
      'upserted_rows', v_upserted_rows,
      'deleted_rows', v_deleted_rows,
      'snapshot_rows', (SELECT COUNT(*)::BIGINT FROM tmp_hourly_activity_snapshot),
      'timing_ms', v_elapsed_ms
    ),
    v_started_at,
    v_completed_at
  );

  RAISE INFO 'fn_snapshot_hourly_activity completed in % ms (requested_period=% rows=%)', v_elapsed_ms, p_period, v_total_rows;

  RETURN v_total_rows;
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
    'fn_snapshot_hourly_activity',
    'failed',
    jsonb_build_object('requested_period', p_period, 'error', SQLERRM, 'timing_ms', v_elapsed_ms),
    v_started_at,
    v_completed_at
  );

  RAISE INFO 'fn_snapshot_hourly_activity failed after % ms (requested_period=%): %', v_elapsed_ms, p_period, SQLERRM;

  RAISE;
END;
$$;


--
-- Name: fn_snapshot_relations(daterange); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_relations(p_period daterange DEFAULT NULL::daterange) RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
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

  -- Serialize snapshot rebuilds with the live gk_moves relations trigger.
  PERFORM pg_advisory_xact_lock(20260321, 1);

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
          OR iu.user_id = uru.related_user_id
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
       OR right_side.user_id IN (
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


--
-- Name: fn_snapshot_relationship_tables(daterange); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_relationship_tables(p_period daterange DEFAULT NULL::daterange) RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
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


--
-- Name: fn_snapshot_user_country_stats(daterange); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_user_country_stats(p_period daterange DEFAULT NULL::daterange) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
DECLARE
  v_count BIGINT := 0;
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_completed_at TIMESTAMPTZ;
  v_elapsed_ms BIGINT := 0;
  v_period_start TIMESTAMPTZ;
  v_period_end TIMESTAMPTZ;
BEGIN
  v_period_start := CASE WHEN p_period IS NULL THEN NULL ELSE lower(p_period)::TIMESTAMPTZ END;
  v_period_end := CASE WHEN p_period IS NULL THEN NULL ELSE upper(p_period)::TIMESTAMPTZ END;

  IF p_period IS NULL THEN
    INSERT INTO stats.user_countries (
      user_id,
      country_code,
      move_count,
      first_visit,
      last_visit
    )
    SELECT
      m.author AS user_id,
      geokrety.fn_normalize_country_code(m.country) AS country_code,
      COUNT(*)::BIGINT AS move_count,
      MIN(m.moved_on_datetime) AS first_visit,
      MAX(m.moved_on_datetime) AS last_visit
    FROM geokrety.gk_moves m
    WHERE m.country IS NOT NULL
      AND m.author IS NOT NULL
    GROUP BY m.author, geokrety.fn_normalize_country_code(m.country)
    ON CONFLICT (user_id, country_code) DO UPDATE SET
      move_count = EXCLUDED.move_count,
      first_visit = EXCLUDED.first_visit,
      last_visit = EXCLUDED.last_visit;
  ELSE
    WITH touched_keys AS (
      SELECT DISTINCT
        m.author AS user_id,
        geokrety.fn_normalize_country_code(m.country) AS country_code
      FROM geokrety.gk_moves m
      WHERE m.country IS NOT NULL
        AND m.author IS NOT NULL
        AND m.moved_on_datetime >= v_period_start
        AND m.moved_on_datetime < v_period_end
    )
    INSERT INTO stats.user_countries (
      user_id,
      country_code,
      move_count,
      first_visit,
      last_visit
    )
    SELECT
      m.author AS user_id,
      geokrety.fn_normalize_country_code(m.country) AS country_code,
      COUNT(*)::BIGINT AS move_count,
      MIN(m.moved_on_datetime) AS first_visit,
      MAX(m.moved_on_datetime) AS last_visit
    FROM geokrety.gk_moves m
    JOIN touched_keys tk
      ON tk.user_id = m.author
     AND tk.country_code = geokrety.fn_normalize_country_code(m.country)
    WHERE m.country IS NOT NULL
      AND m.author IS NOT NULL
    GROUP BY m.author, geokrety.fn_normalize_country_code(m.country)
    ON CONFLICT (user_id, country_code) DO UPDATE SET
      move_count = EXCLUDED.move_count,
      first_visit = EXCLUDED.first_visit,
      last_visit = EXCLUDED.last_visit;
  END IF;

  GET DIAGNOSTICS v_count = ROW_COUNT;

  IF p_period IS NULL THEN
    DELETE FROM stats.user_countries uc
     WHERE NOT EXISTS (
       SELECT 1
       FROM geokrety.gk_moves m
       WHERE m.country IS NOT NULL
         AND m.author IS NOT NULL
         AND m.author = uc.user_id
         AND geokrety.fn_normalize_country_code(m.country) = uc.country_code
     );
  ELSE
    DELETE FROM stats.user_countries uc
     WHERE EXISTS (
       SELECT 1
       FROM geokrety.gk_moves scoped_moves
       WHERE scoped_moves.country IS NOT NULL
         AND scoped_moves.author IS NOT NULL
        AND scoped_moves.moved_on_datetime >= v_period_start
        AND scoped_moves.moved_on_datetime < v_period_end
         AND scoped_moves.author = uc.user_id
         AND geokrety.fn_normalize_country_code(scoped_moves.country) = uc.country_code
     )
       AND NOT EXISTS (
         SELECT 1
         FROM geokrety.gk_moves m
         WHERE m.country IS NOT NULL
           AND m.author IS NOT NULL
           AND m.author = uc.user_id
           AND geokrety.fn_normalize_country_code(m.country) = uc.country_code
       );
  END IF;

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
    'fn_snapshot_user_country_stats',
    'ok',
    jsonb_build_object('rows_affected', v_count, 'requested_period', p_period, 'timing_ms', v_elapsed_ms),
    v_started_at,
    clock_timestamp()
  );

  RAISE INFO 'fn_snapshot_user_country_stats completed in % ms (requested_period=% rows=%)', v_elapsed_ms, p_period, v_count;

  RETURN v_count;
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
    'fn_snapshot_user_country_stats',
    'error',
    jsonb_build_object('error', SQLERRM, 'requested_period', p_period, 'timing_ms', v_elapsed_ms),
    v_started_at,
    clock_timestamp()
  );

  RAISE INFO 'fn_snapshot_user_country_stats failed after % ms (requested_period=%): %', v_elapsed_ms, p_period, SQLERRM;

  RAISE;
END;
$$;


--
-- Name: FUNCTION fn_snapshot_user_country_stats(p_period daterange); Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON FUNCTION stats.fn_snapshot_user_country_stats(p_period daterange) IS 'Seeds user_countries from gk_moves. Optional p_period recomputes only touched user-country keys using full source history for exact aggregates.';


--
-- Name: fn_snapshot_waypoints(daterange); Type: FUNCTION; Schema: stats; Owner: -
--

CREATE FUNCTION stats.fn_snapshot_waypoints(p_period daterange DEFAULT NULL::daterange) RETURNS bigint
    LANGUAGE plpgsql SECURITY DEFINER
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


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: backfill_progress; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.backfill_progress (
    job_name character varying(100) NOT NULL,
    target_table character varying(100) NOT NULL,
    min_id bigint DEFAULT 0 NOT NULL,
    max_id bigint NOT NULL,
    cursor_id bigint DEFAULT 0 NOT NULL,
    batch_size integer DEFAULT 10000 NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    rows_processed bigint DEFAULT 0 NOT NULL,
    error_count integer DEFAULT 0 NOT NULL,
    started_at timestamp with time zone,
    last_heartbeat_at timestamp with time zone,
    completed_at timestamp with time zone,
    notes text,
    last_error text,
    CONSTRAINT backfill_progress_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'running'::character varying, 'paused'::character varying, 'completed'::character varying, 'failed'::character varying])::text[])))
);


--
-- Name: TABLE backfill_progress; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.backfill_progress IS 'Tracks resumable heavy batch operations with cursor checkpoints and status';


--
-- Name: COLUMN backfill_progress.cursor_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.backfill_progress.cursor_id IS 'Last successfully processed source row ID; resume from cursor_id + 1';


--
-- Name: COLUMN backfill_progress.last_heartbeat_at; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.backfill_progress.last_heartbeat_at IS 'Updated periodically during execution for liveness monitoring';


--
-- Name: continent_reference; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.continent_reference (
    country_alpha2 character(2) NOT NULL,
    continent_code character(2) NOT NULL,
    continent_name character varying(50) NOT NULL
);


--
-- Name: TABLE continent_reference; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.continent_reference IS 'Maps ISO 3166-1 alpha-2 country codes to continent codes and names; 249 entries';


--
-- Name: COLUMN continent_reference.continent_code; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.continent_reference.continent_code IS 'AF=Africa, AN=Antarctica, AS=Asia, EU=Europe, NA=North America, OC=Oceania, SA=South America';


--
-- Name: country_daily_stats; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.country_daily_stats (
    stats_date date NOT NULL,
    country_code character(2) NOT NULL,
    moves_count bigint DEFAULT 0 NOT NULL,
    drops bigint DEFAULT 0 NOT NULL,
    grabs bigint DEFAULT 0 NOT NULL,
    comments bigint DEFAULT 0 NOT NULL,
    sees bigint DEFAULT 0 NOT NULL,
    archives bigint DEFAULT 0 NOT NULL,
    dips bigint DEFAULT 0 NOT NULL,
    unique_users bigint DEFAULT 0 NOT NULL,
    unique_gks bigint DEFAULT 0 NOT NULL,
    km_contributed numeric(14,3) DEFAULT 0 NOT NULL,
    points_contributed numeric(16,4) DEFAULT 0 NOT NULL,
    loves_count bigint DEFAULT 0 NOT NULL,
    pictures_uploaded_total bigint DEFAULT 0 NOT NULL,
    pictures_uploaded_avatar bigint DEFAULT 0 NOT NULL,
    pictures_uploaded_move bigint DEFAULT 0 NOT NULL,
    pictures_uploaded_user bigint DEFAULT 0 NOT NULL
);


--
-- Name: TABLE country_daily_stats; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.country_daily_stats IS 'Daily per-country aggregate statistics for moves, distance, users, GKs, and content';


--
-- Name: COLUMN country_daily_stats.unique_users; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.country_daily_stats.unique_users IS 'Exact distinct user count maintained online for the date/country bucket';


--
-- Name: COLUMN country_daily_stats.unique_gks; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.country_daily_stats.unique_gks IS 'Exact distinct GK count maintained online for the date/country bucket';


--
-- Name: country_pair_flows; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.country_pair_flows (
    year_month date NOT NULL,
    from_country character(2) NOT NULL,
    to_country character(2) NOT NULL,
    move_count bigint DEFAULT 0 NOT NULL,
    unique_gk_count bigint DEFAULT 0 NOT NULL,
    CONSTRAINT chk_country_pair_flows_distinct_countries CHECK ((from_country <> to_country)),
    CONSTRAINT chk_country_pair_flows_from_upper CHECK (((from_country)::text = upper((from_country)::text))),
    CONSTRAINT chk_country_pair_flows_month_start CHECK ((year_month = (date_trunc('month'::text, (year_month)::timestamp with time zone))::date)),
    CONSTRAINT chk_country_pair_flows_to_upper CHECK (((to_country)::text = upper((to_country)::text)))
);


--
-- Name: TABLE country_pair_flows; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.country_pair_flows IS 'Monthly cross-country GeoKret flow counts; one row per month/from/to pair';


--
-- Name: COLUMN country_pair_flows.year_month; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.country_pair_flows.year_month IS 'First day of the UTC month bucket';


--
-- Name: COLUMN country_pair_flows.from_country; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.country_pair_flows.from_country IS 'Uppercase ISO 3166-1 alpha-2 origin country code';


--
-- Name: COLUMN country_pair_flows.to_country; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.country_pair_flows.to_country IS 'Uppercase ISO 3166-1 alpha-2 destination country code';


--
-- Name: COLUMN country_pair_flows.move_count; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.country_pair_flows.move_count IS 'Number of qualifying cross-country moves recorded in the month';


--
-- Name: COLUMN country_pair_flows.unique_gk_count; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.country_pair_flows.unique_gk_count IS 'Number of distinct GeoKrety that crossed from origin to destination in the month';


--
-- Name: daily_active_users; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.daily_active_users (
    activity_date date NOT NULL,
    user_id integer NOT NULL
);


--
-- Name: TABLE daily_active_users; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.daily_active_users IS 'Presence table for users active on a given day; one row per (activity_date, user_id)';


--
-- Name: daily_activity; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.daily_activity (
    activity_date date NOT NULL,
    total_moves bigint DEFAULT 0 NOT NULL,
    drops bigint DEFAULT 0 NOT NULL,
    grabs bigint DEFAULT 0 NOT NULL,
    comments bigint DEFAULT 0 NOT NULL,
    sees bigint DEFAULT 0 NOT NULL,
    archives bigint DEFAULT 0 NOT NULL,
    dips bigint DEFAULT 0 NOT NULL,
    km_contributed numeric(14,3) DEFAULT 0 NOT NULL,
    points_contributed numeric(16,4) DEFAULT 0 NOT NULL,
    gk_created bigint DEFAULT 0 NOT NULL,
    pictures_uploaded_total bigint DEFAULT 0 NOT NULL,
    pictures_uploaded_avatar bigint DEFAULT 0 NOT NULL,
    pictures_uploaded_move bigint DEFAULT 0 NOT NULL,
    pictures_uploaded_user bigint DEFAULT 0 NOT NULL,
    loves_count bigint DEFAULT 0 NOT NULL,
    users_registered bigint DEFAULT 0 NOT NULL
);


--
-- Name: TABLE daily_activity; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.daily_activity IS 'Per-calendar-day aggregate activity metrics; one row per day';


--
-- Name: COLUMN daily_activity.points_contributed; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.daily_activity.points_contributed IS 'Total gamification points awarded on this date; updated by points-awarder service (Sprint 4)';


--
-- Name: COLUMN daily_activity.gk_created; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.daily_activity.gk_created IS 'New GeoKrety created on this date; updated by gk_geokrety trigger (Step 2.8)';


--
-- Name: COLUMN daily_activity.pictures_uploaded_total; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.daily_activity.pictures_uploaded_total IS 'Total pictures uploaded; updated by gk_pictures trigger (Step 2.9)';


--
-- Name: COLUMN daily_activity.loves_count; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.daily_activity.loves_count IS 'Loves given on this date; updated by loves trigger (Sprint 5)';


--
-- Name: COLUMN daily_activity.users_registered; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.daily_activity.users_registered IS 'New user registrations; updated by gk_users trigger (Step 2.10)';


--
-- Name: daily_entity_counts; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.daily_entity_counts (
    count_date date NOT NULL,
    entity character varying(32) NOT NULL,
    cnt bigint DEFAULT 0 NOT NULL,
    CONSTRAINT daily_entity_counts_cnt_nonnegative_chk CHECK ((cnt >= 0))
);


--
-- Name: TABLE daily_entity_counts; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.daily_entity_counts IS 'Daily cumulative entity counts for trend charts; populated by nightly snapshot job';


--
-- Name: COLUMN daily_entity_counts.entity; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.daily_entity_counts.entity IS 'Entity name matching entity_counters_shard.entity';


--
-- Name: COLUMN daily_entity_counts.cnt; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.daily_entity_counts.cnt IS 'Entity count snapshot value for count_date';


--
-- Name: entity_counters_shard; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.entity_counters_shard (
    entity character varying(32) NOT NULL,
    shard integer NOT NULL,
    cnt bigint DEFAULT 0 NOT NULL,
    CONSTRAINT entity_counters_shard_cnt_nonnegative_chk CHECK ((cnt >= 0)),
    CONSTRAINT entity_counters_shard_shard_range_chk CHECK (((shard >= 0) AND (shard <= 15)))
);


--
-- Name: TABLE entity_counters_shard; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.entity_counters_shard IS 'Sharded counter table for exact entity counts; sum cnt across all 16 shards to read an entity total';


--
-- Name: COLUMN entity_counters_shard.entity; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.entity_counters_shard.entity IS 'Counter entity name, e.g. gk_moves, gk_moves_type_0, gk_geokrety_type_3';


--
-- Name: COLUMN entity_counters_shard.shard; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.entity_counters_shard.shard IS 'Shard index from 0 to 15 used to spread concurrent counter updates';


--
-- Name: COLUMN entity_counters_shard.cnt; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.entity_counters_shard.cnt IS 'Exact counter value stored for one entity shard';


--
-- Name: first_finder_events; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.first_finder_events (
    gk_id integer NOT NULL,
    finder_user_id integer NOT NULL,
    move_id bigint NOT NULL,
    move_type smallint NOT NULL,
    hours_since_creation smallint NOT NULL,
    found_at timestamp with time zone NOT NULL,
    gk_created_at timestamp with time zone NOT NULL,
    recorded_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_first_finder_events_hours_non_negative CHECK ((hours_since_creation >= 0)),
    CONSTRAINT chk_first_finder_events_move_type CHECK ((move_type = ANY (ARRAY[0, 1, 3, 5])))
);


--
-- Name: TABLE first_finder_events; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.first_finder_events IS 'First qualifying non-owner interaction per GeoKret; powers first-finder leaderboards';


--
-- Name: COLUMN first_finder_events.gk_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.first_finder_events.gk_id IS 'GeoKret internal identifier; one row per GeoKret';


--
-- Name: COLUMN first_finder_events.finder_user_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.first_finder_events.finder_user_id IS 'First non-owner authenticated user who interacted with the GeoKret';


--
-- Name: COLUMN first_finder_events.move_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.first_finder_events.move_id IS 'Move that established the first-finder event';


--
-- Name: COLUMN first_finder_events.move_type; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.first_finder_events.move_type IS 'Qualifying move type for the first-finder event';


--
-- Name: COLUMN first_finder_events.hours_since_creation; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.first_finder_events.hours_since_creation IS 'Whole hours between GeoKret creation and the first-finder move';


--
-- Name: COLUMN first_finder_events.found_at; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.first_finder_events.found_at IS 'Timestamp of the qualifying first-finder move';


--
-- Name: COLUMN first_finder_events.gk_created_at; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.first_finder_events.gk_created_at IS 'GeoKret creation timestamp used for the 168-hour eligibility cutoff';


--
-- Name: COLUMN first_finder_events.recorded_at; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.first_finder_events.recorded_at IS 'Timestamp when the first-finder event row was inserted';


--
-- Name: gk_cache_visits; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.gk_cache_visits (
    gk_id integer NOT NULL,
    waypoint_id bigint NOT NULL,
    visit_count bigint DEFAULT 0 NOT NULL,
    first_visited_at timestamp with time zone NOT NULL,
    last_visited_at timestamp with time zone NOT NULL
);


--
-- Name: TABLE gk_cache_visits; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.gk_cache_visits IS 'Per-GeoKret per-waypoint visit counter; enables cache analytics without gk_moves scans';


--
-- Name: COLUMN gk_cache_visits.gk_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_cache_visits.gk_id IS 'GeoKret internal ID (references geokrety.gk_geokrety.id, not FK to avoid cross-schema dep)';


--
-- Name: COLUMN gk_cache_visits.waypoint_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_cache_visits.waypoint_id IS 'FK to stats.waypoints(id); surrogated to allow rename/merge without cascading issues';


--
-- Name: COLUMN gk_cache_visits.visit_count; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_cache_visits.visit_count IS 'Count of moves referencing this waypoint for this GeoKret; incremented by trigger';


--
-- Name: gk_countries_visited; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.gk_countries_visited (
    geokrety_id integer NOT NULL,
    country_code character(2) NOT NULL,
    first_visited_at timestamp with time zone NOT NULL,
    first_move_id bigint NOT NULL,
    move_count integer DEFAULT 1 NOT NULL
);


--
-- Name: TABLE gk_countries_visited; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.gk_countries_visited IS 'Tracks which countries each GK has visited, with first-visit metadata and move counts';


--
-- Name: COLUMN gk_countries_visited.first_move_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_countries_visited.first_move_id IS 'ID of the first move that placed this GK in this country';


--
-- Name: COLUMN gk_countries_visited.move_count; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_countries_visited.move_count IS 'Total number of moves by this GK in this country';


--
-- Name: gk_country_history; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.gk_country_history (
    id bigint NOT NULL,
    geokrety_id integer NOT NULL,
    country_code character(2) NOT NULL,
    arrived_at timestamp with time zone NOT NULL,
    departed_at timestamp with time zone,
    move_id bigint NOT NULL
);


--
-- Name: TABLE gk_country_history; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.gk_country_history IS 'Temporal intervals of GK presence in countries; exclusion constraint prevents overlapping intervals per GK';


--
-- Name: COLUMN gk_country_history.departed_at; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_country_history.departed_at IS 'NULL means the GK is currently in this country (open interval)';


--
-- Name: COLUMN gk_country_history.move_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_country_history.move_id IS 'Move ID that caused the GK to arrive in this country';


--
-- Name: gk_country_history_id_seq; Type: SEQUENCE; Schema: stats; Owner: -
--

CREATE SEQUENCE stats.gk_country_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_country_history_id_seq; Type: SEQUENCE OWNED BY; Schema: stats; Owner: -
--

ALTER SEQUENCE stats.gk_country_history_id_seq OWNED BY stats.gk_country_history.id;


--
-- Name: gk_milestone_events; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.gk_milestone_events (
    id bigint NOT NULL,
    gk_id integer NOT NULL,
    event_type text NOT NULL,
    event_value numeric,
    additional_data jsonb,
    occurred_at timestamp with time zone NOT NULL,
    recorded_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_gk_milestone_events_event_type CHECK ((event_type = ANY (ARRAY['km_100'::text, 'km_1000'::text, 'km_10000'::text, 'users_10'::text, 'users_50'::text, 'users_100'::text, 'first_find'::text])))
);


--
-- Name: TABLE gk_milestone_events; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.gk_milestone_events IS 'Append-only per-GeoKret milestone event log for Sprint 5 analytics';


--
-- Name: COLUMN gk_milestone_events.gk_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_milestone_events.gk_id IS 'GeoKret internal identifier';


--
-- Name: COLUMN gk_milestone_events.event_type; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_milestone_events.event_type IS 'Milestone type key';


--
-- Name: COLUMN gk_milestone_events.event_value; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_milestone_events.event_value IS 'Threshold value reached when the milestone fired';


--
-- Name: COLUMN gk_milestone_events.additional_data; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_milestone_events.additional_data IS 'Optional milestone metadata such as country code or related actor context';


--
-- Name: COLUMN gk_milestone_events.occurred_at; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_milestone_events.occurred_at IS 'Timestamp when the milestone was reached';


--
-- Name: COLUMN gk_milestone_events.recorded_at; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_milestone_events.recorded_at IS 'Timestamp when the milestone row was appended';


--
-- Name: gk_milestone_events_id_seq; Type: SEQUENCE; Schema: stats; Owner: -
--

CREATE SEQUENCE stats.gk_milestone_events_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_milestone_events_id_seq; Type: SEQUENCE OWNED BY; Schema: stats; Owner: -
--

ALTER SEQUENCE stats.gk_milestone_events_id_seq OWNED BY stats.gk_milestone_events.id;


--
-- Name: gk_related_users; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.gk_related_users (
    geokrety_id integer NOT NULL,
    user_id integer NOT NULL,
    interaction_count bigint DEFAULT 0 NOT NULL,
    first_interaction timestamp with time zone NOT NULL,
    last_interaction timestamp with time zone NOT NULL
);


--
-- Name: TABLE gk_related_users; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.gk_related_users IS 'Per-GeoKret per-user interaction counter; powers UC3, reach bonus, and social graph';


--
-- Name: COLUMN gk_related_users.geokrety_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_related_users.geokrety_id IS 'GeoKret internal ID (no cross-schema FK)';


--
-- Name: COLUMN gk_related_users.user_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_related_users.user_id IS 'User internal ID; only authenticated users (author IS NOT NULL)';


--
-- Name: COLUMN gk_related_users.interaction_count; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.gk_related_users.interaction_count IS 'Count of qualifying moves (DROP/GRAB/SEEN/DIP, not COMMENT) by this user on this GK';


--
-- Name: hourly_activity; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.hourly_activity (
    activity_date date NOT NULL,
    hour_utc smallint NOT NULL,
    move_type smallint NOT NULL,
    move_count bigint DEFAULT 0 NOT NULL,
    CONSTRAINT chk_hourly_activity_hour CHECK (((hour_utc >= 0) AND (hour_utc <= 23))),
    CONSTRAINT chk_hourly_activity_move_type CHECK (((move_type >= 0) AND (move_type <= 5)))
);


--
-- Name: TABLE hourly_activity; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.hourly_activity IS 'Aggregate move count by UTC date, UTC hour, and move type; powers Sprint 5 hourly analytics';


--
-- Name: COLUMN hourly_activity.activity_date; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.hourly_activity.activity_date IS 'UTC calendar date bucket for the move';


--
-- Name: COLUMN hourly_activity.hour_utc; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.hourly_activity.hour_utc IS 'UTC hour bucket from 0 through 23';


--
-- Name: COLUMN hourly_activity.move_type; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.hourly_activity.move_type IS 'GeoKrety move type bucket (0=drop, 1=grab, 2=comment, 3=seen, 4=archive, 5=dip)';


--
-- Name: COLUMN hourly_activity.move_count; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.hourly_activity.move_count IS 'Exact number of moves aggregated into this UTC date/hour/type cell';


--
-- Name: job_log; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.job_log (
    id bigint NOT NULL,
    job_name character varying(100) NOT NULL,
    status character varying(20) NOT NULL,
    metadata jsonb,
    started_at timestamp with time zone DEFAULT now() NOT NULL,
    completed_at timestamp with time zone
);


--
-- Name: TABLE job_log; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.job_log IS 'Audit log for all backfill, replay, and snapshot operations';


--
-- Name: COLUMN job_log.metadata; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.job_log.metadata IS 'Arbitrary JSON metadata: batch counts, timing, error details';


--
-- Name: job_log_id_seq; Type: SEQUENCE; Schema: stats; Owner: -
--

CREATE SEQUENCE stats.job_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: job_log_id_seq; Type: SEQUENCE OWNED BY; Schema: stats; Owner: -
--

ALTER SEQUENCE stats.job_log_id_seq OWNED BY stats.job_log.id;


--
-- Name: mv_backfill_working_set; Type: MATERIALIZED VIEW; Schema: stats; Owner: -
--

CREATE MATERIALIZED VIEW stats.mv_backfill_working_set AS
 WITH base_moves AS (
         SELECT m.id,
            m.geokret,
            m.moved_on_datetime,
            m."position",
            m.km_distance,
            m.move_type
           FROM geokrety.gk_moves m
          WHERE (m.geokret IS NOT NULL)
        ), qualifying_moves AS (
         SELECT m.id,
            m.geokret,
            row_number() OVER (PARTITION BY m.geokret ORDER BY m.moved_on_datetime, m.id) AS qualifying_seq
           FROM base_moves m
          WHERE (m.move_type = ANY (ARRAY[0, 1, 3, 5]))
        ), positioned_moves AS (
         SELECT m.id,
            m.geokret,
            row_number() OVER (PARTITION BY m.geokret ORDER BY m.moved_on_datetime, m.id) AS positioned_seq,
            m."position"
           FROM base_moves m
          WHERE ((m.move_type = ANY (ARRAY[0, 1, 3, 5])) AND (m."position" IS NOT NULL))
        ), chain_state AS (
         SELECT m.id,
            m.geokret,
            m.move_type,
            m."position",
            count(*) FILTER (WHERE (m.move_type = ANY (ARRAY[0, 1, 3, 5]))) OVER (PARTITION BY m.geokret ORDER BY m.moved_on_datetime, m.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS qualifying_seen,
            count(*) FILTER (WHERE ((m.move_type = ANY (ARRAY[0, 1, 3, 5])) AND (m."position" IS NOT NULL))) OVER (PARTITION BY m.geokret ORDER BY m.moved_on_datetime, m.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS positioned_seen
           FROM base_moves m
        )
 SELECT s.id,
    s.geokret,
    b.moved_on_datetime,
    s."position",
    b.km_distance,
    s.move_type,
    qm.id AS previous_move_id,
    pm.id AS previous_position_id,
        CASE
            WHEN ((s.move_type = ANY (ARRAY[0, 1, 3, 5])) AND (s."position" IS NOT NULL) AND (pm."position" IS NOT NULL)) THEN ((public.st_distance(pm."position", s."position") / (1000.0)::double precision))::numeric(8,3)
            ELSE NULL::numeric
        END AS expected_km_distance,
    pm."position" AS previous_position
   FROM (((chain_state s
     JOIN base_moves b ON ((b.id = s.id)))
     LEFT JOIN qualifying_moves qm ON (((qm.geokret = s.geokret) AND (qm.qualifying_seq = (s.qualifying_seen -
        CASE
            WHEN (s.move_type = ANY (ARRAY[0, 1, 3, 5])) THEN 1
            ELSE 0
        END)))))
     LEFT JOIN positioned_moves pm ON (((pm.geokret = s.geokret) AND (pm.positioned_seq = (s.positioned_seen -
        CASE
            WHEN ((s.move_type = ANY (ARRAY[0, 1, 3, 5])) AND (s."position" IS NOT NULL)) THEN 1
            ELSE 0
        END)))))
  ORDER BY b.moved_on_datetime, b.id
  WITH NO DATA;


--
-- Name: mv_country_month_rollup; Type: MATERIALIZED VIEW; Schema: stats; Owner: -
--

CREATE MATERIALIZED VIEW stats.mv_country_month_rollup AS
 SELECT from_country,
    to_country,
    year_month,
    move_count,
    unique_gk_count
   FROM stats.country_pair_flows
  WITH NO DATA;


--
-- Name: mv_global_kpi; Type: MATERIALIZED VIEW; Schema: stats; Owner: -
--

CREATE MATERIALIZED VIEW stats.mv_global_kpi AS
 SELECT (1)::smallint AS singleton_key,
    ( SELECT count(*) AS count
           FROM geokrety.gk_geokrety) AS total_geokrety,
    ( SELECT count(*) AS count
           FROM geokrety.gk_moves) AS total_moves,
    ( SELECT (COALESCE(sum(gk_moves.km_distance), (0)::numeric))::numeric(14,3) AS "coalesce"
           FROM geokrety.gk_moves
          WHERE (gk_moves.km_distance IS NOT NULL)) AS total_km,
    ( SELECT count(*) AS count
           FROM geokrety.gk_users) AS total_users,
    clock_timestamp() AS computed_at
  WITH NO DATA;


--
-- Name: waypoints; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.waypoints (
    id bigint NOT NULL,
    waypoint_code character varying(20) NOT NULL,
    source character(2) DEFAULT 'UK'::bpchar NOT NULL,
    lat double precision,
    lon double precision,
    country character(2),
    first_seen_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_waypoints_source CHECK ((source = ANY (ARRAY['GC'::bpchar, 'OC'::bpchar, 'UK'::bpchar])))
);


--
-- Name: TABLE waypoints; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.waypoints IS 'Canonical deduplicated waypoint registry; each distinct waypoint code appears exactly once';


--
-- Name: COLUMN waypoints.waypoint_code; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.waypoints.waypoint_code IS 'Uppercase normalised waypoint identifier, e.g. GC1A2B3, OKXXXX';


--
-- Name: COLUMN waypoints.source; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.waypoints.source IS 'Provenance: GC=geocaching.com seed, OC=opencaching seed, UK=first seen in move stream';


--
-- Name: COLUMN waypoints.country; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.waypoints.country IS 'ISO 3166-1 alpha-2 country code derived from waypoint seed tables; may be NULL for UK-sourced';


--
-- Name: v_uc10_cache_popularity; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_uc10_cache_popularity AS
 SELECT w.waypoint_code,
    (sum(g.visit_count))::bigint AS total_gk_visits,
    count(DISTINCT g.gk_id) AS distinct_gks
   FROM (stats.gk_cache_visits g
     JOIN stats.waypoints w ON ((w.id = g.waypoint_id)))
  GROUP BY w.waypoint_code;


--
-- Name: mv_top_caches_global; Type: MATERIALIZED VIEW; Schema: stats; Owner: -
--

CREATE MATERIALIZED VIEW stats.mv_top_caches_global AS
 SELECT waypoint_code,
    total_gk_visits,
    distinct_gks
   FROM stats.v_uc10_cache_popularity
  WITH NO DATA;


--
-- Name: user_cache_visits; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.user_cache_visits (
    user_id integer NOT NULL,
    waypoint_id bigint NOT NULL,
    visit_count bigint DEFAULT 0 NOT NULL,
    first_visited_at timestamp with time zone NOT NULL,
    last_visited_at timestamp with time zone NOT NULL
);


--
-- Name: TABLE user_cache_visits; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.user_cache_visits IS 'Per-user per-waypoint visit counter; enables user cache analytics without gk_moves scans';


--
-- Name: COLUMN user_cache_visits.user_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.user_cache_visits.user_id IS 'User internal ID (references geokrety.gk_users.id; no cross-schema FK)';


--
-- Name: COLUMN user_cache_visits.visit_count; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.user_cache_visits.visit_count IS 'Number of moves by this user at this waypoint; incremented by trigger';


--
-- Name: user_countries; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.user_countries (
    user_id integer NOT NULL,
    country_code character(2) NOT NULL,
    move_count bigint DEFAULT 0 NOT NULL,
    first_visit timestamp with time zone NOT NULL,
    last_visit timestamp with time zone NOT NULL
);


--
-- Name: TABLE user_countries; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.user_countries IS 'Tracks which countries each user has interacted in, with move counts and visit timestamps';


--
-- Name: COLUMN user_countries.first_visit; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.user_countries.first_visit IS 'Timestamp of first move by this user in this country';


--
-- Name: COLUMN user_countries.last_visit; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.user_countries.last_visit IS 'Timestamp of most recent move by this user in this country';


--
-- Name: user_related_users; Type: TABLE; Schema: stats; Owner: -
--

CREATE TABLE stats.user_related_users (
    user_id integer NOT NULL,
    related_user_id integer NOT NULL,
    shared_geokrety_count bigint DEFAULT 0 NOT NULL,
    first_seen_at timestamp with time zone NOT NULL,
    last_seen_at timestamp with time zone NOT NULL,
    CONSTRAINT chk_user_related_users_no_self CHECK ((user_id <> related_user_id))
);


--
-- Name: TABLE user_related_users; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON TABLE stats.user_related_users IS 'Directional user-user relation via shared GeoKrety; both directions stored; powers UC2 social graph';


--
-- Name: COLUMN user_related_users.user_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.user_related_users.user_id IS 'Source user (authenticated only)';


--
-- Name: COLUMN user_related_users.related_user_id; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.user_related_users.related_user_id IS 'Target user (authenticated only); never equals user_id';


--
-- Name: COLUMN user_related_users.shared_geokrety_count; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON COLUMN stats.user_related_users.shared_geokrety_count IS 'Number of distinct GeoKrety that both users have interacted with';


--
-- Name: v_uc13_gk_timeline; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_uc13_gk_timeline AS
 SELECT gk_id,
    event_type,
    occurred_at,
        CASE
            WHEN ((additional_data ? 'actor_user_id'::text) AND (COALESCE((additional_data ->> 'actor_user_id'::text), ''::text) ~ '^[0-9]+$'::text)) THEN ((additional_data ->> 'actor_user_id'::text))::integer
            ELSE NULL::integer
        END AS actor_user_id
   FROM stats.gk_milestone_events;


--
-- Name: v_uc14_first_finder_hof; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_uc14_first_finder_hof AS
 SELECT finder_user_id,
    count(*) AS first_finds
   FROM stats.first_finder_events
  GROUP BY finder_user_id;


--
-- Name: v_uc15_distance_records; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_uc15_distance_records AS
 SELECT geokret AS gk_id,
    (sum(km_distance))::numeric(14,3) AS km_total
   FROM geokrety.gk_moves
  WHERE (km_distance IS NOT NULL)
  GROUP BY geokret;


--
-- Name: v_uc1_country_activity; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_uc1_country_activity AS
 SELECT country_code,
    (sum(moves_count))::bigint AS moves,
    (sum(km_contributed))::numeric(14,3) AS km
   FROM stats.country_daily_stats
  GROUP BY country_code;


--
-- Name: v_uc2_user_network; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_uc2_user_network AS
 SELECT user_id,
    related_user_id,
    shared_geokrety_count,
    first_seen_at,
    last_seen_at
   FROM stats.user_related_users;


--
-- Name: v_uc3_gk_circulation; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_uc3_gk_circulation AS
 SELECT geokrety_id,
    count(*) AS users,
    (sum(interaction_count))::bigint AS interactions
   FROM stats.gk_related_users
  GROUP BY geokrety_id;


--
-- Name: v_uc4_user_continent_coverage; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_uc4_user_continent_coverage AS
 SELECT uc.user_id,
    cr.continent_code,
    (sum(uc.move_count))::bigint AS moves
   FROM (stats.user_countries uc
     JOIN stats.continent_reference cr ON ((cr.country_alpha2 = (upper((uc.country_code)::text))::character(2))))
  GROUP BY uc.user_id, cr.continent_code;


--
-- Name: v_uc6_dormancy; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_uc6_dormancy AS
 SELECT geokrety_id,
    max(last_interaction) AS last_touch,
    (clock_timestamp() - max(last_interaction)) AS dormancy_interval
   FROM stats.gk_related_users
  GROUP BY geokrety_id;


--
-- Name: v_uc7_country_flow; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_uc7_country_flow AS
 SELECT year_month,
    from_country,
    to_country,
    move_count,
    unique_gk_count
   FROM stats.country_pair_flows;


--
-- Name: v_uc8_seasonal_heatmap; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_uc8_seasonal_heatmap AS
 SELECT activity_date,
    hour_utc,
    move_type,
    move_count
   FROM stats.hourly_activity;


--
-- Name: v_uc9_multiplier_velocity; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_uc9_multiplier_velocity AS
 SELECT NULL::bigint AS gk_id,
    NULL::timestamp with time zone AS last_change,
    NULL::numeric AS avg_delta
  WHERE false;


--
-- Name: v_waypoints_source_union; Type: VIEW; Schema: stats; Owner: -
--

CREATE VIEW stats.v_waypoints_source_union AS
 SELECT upper((gk_waypoints_gc.waypoint)::text) AS waypoint_code,
    'GC'::character(2) AS source,
    gk_waypoints_gc.lat,
    gk_waypoints_gc.lon,
    (upper((gk_waypoints_gc.country)::text))::character(2) AS country
   FROM geokrety.gk_waypoints_gc
  WHERE ((gk_waypoints_gc.waypoint IS NOT NULL) AND (btrim((gk_waypoints_gc.waypoint)::text) <> ''::text))
UNION ALL
 SELECT upper((gk_waypoints_oc.waypoint)::text) AS waypoint_code,
    'OC'::character(2) AS source,
    gk_waypoints_oc.lat,
    gk_waypoints_oc.lon,
    (upper((gk_waypoints_oc.country)::text))::character(2) AS country
   FROM geokrety.gk_waypoints_oc
  WHERE ((gk_waypoints_oc.waypoint IS NOT NULL) AND (btrim((gk_waypoints_oc.waypoint)::text) <> ''::text));


--
-- Name: VIEW v_waypoints_source_union; Type: COMMENT; Schema: stats; Owner: -
--

COMMENT ON VIEW stats.v_waypoints_source_union IS 'Union of GC and OC waypoint tables for seeding and diagnostics; does not deduplicate';


--
-- Name: waypoints_id_seq; Type: SEQUENCE; Schema: stats; Owner: -
--

CREATE SEQUENCE stats.waypoints_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: waypoints_id_seq; Type: SEQUENCE OWNED BY; Schema: stats; Owner: -
--

ALTER SEQUENCE stats.waypoints_id_seq OWNED BY stats.waypoints.id;


--
-- Name: gk_country_history id; Type: DEFAULT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.gk_country_history ALTER COLUMN id SET DEFAULT nextval('stats.gk_country_history_id_seq'::regclass);


--
-- Name: gk_milestone_events id; Type: DEFAULT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.gk_milestone_events ALTER COLUMN id SET DEFAULT nextval('stats.gk_milestone_events_id_seq'::regclass);


--
-- Name: job_log id; Type: DEFAULT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.job_log ALTER COLUMN id SET DEFAULT nextval('stats.job_log_id_seq'::regclass);


--
-- Name: waypoints id; Type: DEFAULT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.waypoints ALTER COLUMN id SET DEFAULT nextval('stats.waypoints_id_seq'::regclass);


--
-- Name: backfill_progress backfill_progress_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.backfill_progress
    ADD CONSTRAINT backfill_progress_pkey PRIMARY KEY (job_name);


--
-- Name: continent_reference continent_reference_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.continent_reference
    ADD CONSTRAINT continent_reference_pkey PRIMARY KEY (country_alpha2);


--
-- Name: country_daily_stats country_daily_stats_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.country_daily_stats
    ADD CONSTRAINT country_daily_stats_pkey PRIMARY KEY (stats_date, country_code);


--
-- Name: country_pair_flows country_pair_flows_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.country_pair_flows
    ADD CONSTRAINT country_pair_flows_pkey PRIMARY KEY (year_month, from_country, to_country);


--
-- Name: daily_active_users daily_active_users_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.daily_active_users
    ADD CONSTRAINT daily_active_users_pkey PRIMARY KEY (activity_date, user_id);


--
-- Name: daily_activity daily_activity_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.daily_activity
    ADD CONSTRAINT daily_activity_pkey PRIMARY KEY (activity_date);


--
-- Name: daily_entity_counts daily_entity_counts_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.daily_entity_counts
    ADD CONSTRAINT daily_entity_counts_pkey PRIMARY KEY (count_date, entity);


--
-- Name: entity_counters_shard entity_counters_shard_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.entity_counters_shard
    ADD CONSTRAINT entity_counters_shard_pkey PRIMARY KEY (entity, shard);


--
-- Name: first_finder_events first_finder_events_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.first_finder_events
    ADD CONSTRAINT first_finder_events_pkey PRIMARY KEY (gk_id);


--
-- Name: gk_cache_visits gk_cache_visits_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.gk_cache_visits
    ADD CONSTRAINT gk_cache_visits_pkey PRIMARY KEY (gk_id, waypoint_id);


--
-- Name: gk_countries_visited gk_countries_visited_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.gk_countries_visited
    ADD CONSTRAINT gk_countries_visited_pkey PRIMARY KEY (geokrety_id, country_code);


--
-- Name: gk_country_history gk_country_history_excl; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.gk_country_history
    ADD CONSTRAINT gk_country_history_excl EXCLUDE USING gist (geokrety_id WITH =, tstzrange(arrived_at, COALESCE(departed_at, 'infinity'::timestamp with time zone)) WITH &&);


--
-- Name: gk_country_history gk_country_history_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.gk_country_history
    ADD CONSTRAINT gk_country_history_pkey PRIMARY KEY (id);


--
-- Name: gk_milestone_events gk_milestone_events_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.gk_milestone_events
    ADD CONSTRAINT gk_milestone_events_pkey PRIMARY KEY (id);


--
-- Name: gk_related_users gk_related_users_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.gk_related_users
    ADD CONSTRAINT gk_related_users_pkey PRIMARY KEY (geokrety_id, user_id);


--
-- Name: hourly_activity hourly_activity_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.hourly_activity
    ADD CONSTRAINT hourly_activity_pkey PRIMARY KEY (activity_date, hour_utc, move_type);


--
-- Name: job_log job_log_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.job_log
    ADD CONSTRAINT job_log_pkey PRIMARY KEY (id);


--
-- Name: gk_milestone_events uq_gk_milestone_events_gk_event_type; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.gk_milestone_events
    ADD CONSTRAINT uq_gk_milestone_events_gk_event_type UNIQUE (gk_id, event_type);


--
-- Name: waypoints uq_waypoints_code; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.waypoints
    ADD CONSTRAINT uq_waypoints_code UNIQUE (waypoint_code);


--
-- Name: user_cache_visits user_cache_visits_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.user_cache_visits
    ADD CONSTRAINT user_cache_visits_pkey PRIMARY KEY (user_id, waypoint_id);


--
-- Name: user_countries user_countries_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.user_countries
    ADD CONSTRAINT user_countries_pkey PRIMARY KEY (user_id, country_code);


--
-- Name: user_related_users user_related_users_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.user_related_users
    ADD CONSTRAINT user_related_users_pkey PRIMARY KEY (user_id, related_user_id);


--
-- Name: waypoints waypoints_pkey; Type: CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.waypoints
    ADD CONSTRAINT waypoints_pkey PRIMARY KEY (id);


--
-- Name: idx_country_daily_stats_country_date; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_country_daily_stats_country_date ON stats.country_daily_stats USING btree (country_code, stats_date);


--
-- Name: idx_country_pair_flows_from; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_country_pair_flows_from ON stats.country_pair_flows USING btree (from_country, year_month DESC);


--
-- Name: idx_country_pair_flows_month_desc; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_country_pair_flows_month_desc ON stats.country_pair_flows USING btree (year_month DESC);


--
-- Name: idx_country_pair_flows_to; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_country_pair_flows_to ON stats.country_pair_flows USING btree (to_country, year_month DESC);


--
-- Name: idx_first_finder_events_hours; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_first_finder_events_hours ON stats.first_finder_events USING btree (hours_since_creation) WHERE (hours_since_creation <= 168);


--
-- Name: idx_first_finder_events_user; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_first_finder_events_user ON stats.first_finder_events USING btree (finder_user_id, found_at DESC);


--
-- Name: idx_gk_cache_visits_waypoint; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_gk_cache_visits_waypoint ON stats.gk_cache_visits USING btree (waypoint_id, gk_id);


--
-- Name: idx_gk_country_history_active_by_country; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_gk_country_history_active_by_country ON stats.gk_country_history USING btree (country_code) WHERE (departed_at IS NULL);


--
-- Name: idx_gk_country_history_gk_arrived; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_gk_country_history_gk_arrived ON stats.gk_country_history USING btree (geokrety_id, arrived_at DESC);


--
-- Name: idx_gk_milestone_events_gk; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_gk_milestone_events_gk ON stats.gk_milestone_events USING btree (gk_id, occurred_at DESC);


--
-- Name: idx_gk_milestone_events_type; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_gk_milestone_events_type ON stats.gk_milestone_events USING btree (event_type, occurred_at DESC);


--
-- Name: idx_gk_related_users_user; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_gk_related_users_user ON stats.gk_related_users USING btree (user_id);


--
-- Name: idx_hourly_activity_date_desc; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_hourly_activity_date_desc ON stats.hourly_activity USING btree (activity_date DESC);


--
-- Name: idx_mv_backfill_period; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_mv_backfill_period ON stats.mv_backfill_working_set USING btree (moved_on_datetime, geokret, id);


--
-- Name: idx_mv_country_month_rollup_pk; Type: INDEX; Schema: stats; Owner: -
--

CREATE UNIQUE INDEX idx_mv_country_month_rollup_pk ON stats.mv_country_month_rollup USING btree (from_country, to_country, year_month);


--
-- Name: idx_mv_global_kpi_pk; Type: INDEX; Schema: stats; Owner: -
--

CREATE UNIQUE INDEX idx_mv_global_kpi_pk ON stats.mv_global_kpi USING btree (singleton_key);


--
-- Name: idx_mv_top_caches_global_pk; Type: INDEX; Schema: stats; Owner: -
--

CREATE UNIQUE INDEX idx_mv_top_caches_global_pk ON stats.mv_top_caches_global USING btree (waypoint_code);


--
-- Name: idx_user_cache_visits_waypoint; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_user_cache_visits_waypoint ON stats.user_cache_visits USING btree (waypoint_id, user_id);


--
-- Name: idx_waypoints_country; Type: INDEX; Schema: stats; Owner: -
--

CREATE INDEX idx_waypoints_country ON stats.waypoints USING btree (country) WHERE (country IS NOT NULL);


--
-- Name: gk_cache_visits fk_gk_cache_visits_waypoint; Type: FK CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.gk_cache_visits
    ADD CONSTRAINT fk_gk_cache_visits_waypoint FOREIGN KEY (waypoint_id) REFERENCES stats.waypoints(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: user_cache_visits fk_user_cache_visits_waypoint; Type: FK CONSTRAINT; Schema: stats; Owner: -
--

ALTER TABLE ONLY stats.user_cache_visits
    ADD CONSTRAINT fk_user_cache_visits_waypoint FOREIGN KEY (waypoint_id) REFERENCES stats.waypoints(id) DEFERRABLE INITIALLY DEFERRED;


--
-- PostgreSQL database dump complete
--

\unrestrict Y2LmTGw9BEeb3hnngn64Sfcqhimq9rAU1daIB2Gi3PSNBkhelWwcfbBpwOabrVU

