<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSnapshotOrchestrationAndViews extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION stats.fn_snapshot_hourly_activity(
  p_period tstzrange
)
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_upserted_rows BIGINT := 0;
  v_deleted_rows BIGINT := 0;
  v_total_rows BIGINT := 0;
BEGIN
  IF p_period IS NULL THEN
    RETURN stats.fn_snapshot_hourly_activity();
  END IF;

  DROP TABLE IF EXISTS tmp_hourly_activity_snapshot;

  CREATE TEMP TABLE tmp_hourly_activity_snapshot ON COMMIT DROP AS
  SELECT
    (timezone('UTC', m.moved_on_datetime))::DATE AS activity_date,
    EXTRACT(HOUR FROM timezone('UTC', m.moved_on_datetime))::SMALLINT AS hour_utc,
    m.move_type::SMALLINT AS move_type,
    COUNT(*)::BIGINT AS move_count
  FROM geokrety.gk_moves m
  WHERE m.move_type BETWEEN 0 AND 5
    AND m.moved_on_datetime <@ p_period
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
  WHERE tstzrange(target.activity_date::TIMESTAMPTZ, (target.activity_date + 1)::TIMESTAMPTZ, '[)') && p_period
    AND NOT EXISTS (
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
      'requested_period', p_period,
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
    jsonb_build_object('requested_period', p_period, 'error', SQLERRM),
    v_started_at,
    clock_timestamp()
  );

  RAISE;
END;
$$;

CREATE OR REPLACE FUNCTION stats.fn_snapshot_country_pair_flows(
  p_period tstzrange
)
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_upserted_rows BIGINT := 0;
  v_deleted_rows BIGINT := 0;
  v_total_rows BIGINT := 0;
BEGIN
  IF p_period IS NULL THEN
    RETURN stats.fn_snapshot_country_pair_flows();
  END IF;

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
    AND current_move.moved_on_datetime <@ p_period
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
  WHERE tstzrange(target.year_month::TIMESTAMPTZ, (target.year_month + INTERVAL '1 month')::TIMESTAMPTZ, '[)') && p_period
    AND NOT EXISTS (
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
      'requested_period', p_period,
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
    jsonb_build_object('requested_period', p_period, 'error', SQLERRM),
    v_started_at,
    clock_timestamp()
  );

  RAISE;
END;
$$;

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
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'mode', CASE WHEN p_period IS NULL THEN 'full' ELSE 'full-source' END, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_gk_country_stats' THEN
      v_rows := stats.fn_snapshot_gk_country_stats(v_period_date);
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'mode', CASE WHEN p_period IS NULL THEN 'full' ELSE 'full-source' END, 'rows_affected', v_rows);
    WHEN 'fn_snapshot_relationship_tables' THEN
      v_rows := stats.fn_snapshot_relationship_tables(v_period_date);
      RETURN jsonb_build_object('phase', p_phase, 'requested_period', p_period, 'mode', CASE WHEN p_period IS NULL THEN 'full' ELSE 'full-source' END, 'rows_affected', v_rows);
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

CREATE OR REPLACE FUNCTION stats.fn_run_all_snapshots(
  p_phases TEXT[],
  p_period tstzrange DEFAULT NULL,
  p_batch_size INT DEFAULT 50000
)
RETURNS TEXT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
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
BEGIN
  IF p_batch_size IS NULL OR p_batch_size < 1 THEN
    RAISE EXCEPTION 'p_batch_size must be >= 1'
      USING ERRCODE = '22023';
  END IF;

  FOREACH v_phase IN ARRAY v_requested_phases LOOP
    v_results := v_results || jsonb_build_object(
      v_phase,
      stats.fn_run_snapshot_phase(v_phase, p_period, p_batch_size)
    );
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

CREATE OR REPLACE FUNCTION stats.fn_run_all_snapshots()
RETURNS TEXT
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  RETURN stats.fn_run_all_snapshots(NULL::TEXT[], NULL::tstzrange, 50000);
END;
$$;

CREATE OR REPLACE FUNCTION stats.fn_reconcile_stats(
  p_checks TEXT[],
  p_period tstzrange
)
RETURNS TABLE (
  check_name TEXT,
  source_count BIGINT,
  stats_count BIGINT,
  delta BIGINT,
  status TEXT
)
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_failures BIGINT := 0;
  v_run_all_checks BOOLEAN := p_checks IS NULL OR array_length(p_checks, 1) IS NULL;
BEGIN
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
      WHERE p_period IS NULL OR moved_on_datetime <@ p_period
      UNION
      SELECT created_on_datetime::date FROM geokrety.gk_geokrety
      WHERE p_period IS NULL OR created_on_datetime <@ p_period
      UNION
      SELECT uploaded_on_datetime::date
      FROM geokrety.gk_pictures
      WHERE uploaded_on_datetime IS NOT NULL
        AND (p_period IS NULL OR uploaded_on_datetime <@ p_period)
      UNION
      SELECT joined_on_datetime::date FROM geokrety.gk_users
      WHERE p_period IS NULL OR joined_on_datetime <@ p_period
      UNION
      SELECT created_on_datetime::date FROM geokrety.gk_loves
      WHERE p_period IS NULL OR created_on_datetime <@ p_period
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
        WHERE p_period IS NULL OR moved_on_datetime <@ p_period
        GROUP BY moved_on_datetime::date
      ) AS moves_daily USING (activity_date)
      LEFT JOIN (
        SELECT created_on_datetime::date AS activity_date, COUNT(*)::BIGINT AS gk_created
        FROM geokrety.gk_geokrety
        WHERE p_period IS NULL OR created_on_datetime <@ p_period
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
          AND (p_period IS NULL OR uploaded_on_datetime <@ p_period)
        GROUP BY uploaded_on_datetime::date
      ) AS pictures_daily USING (activity_date)
      LEFT JOIN (
        SELECT created_on_datetime::date AS activity_date, COUNT(*)::BIGINT AS loves_count
        FROM geokrety.gk_loves
        WHERE p_period IS NULL OR created_on_datetime <@ p_period
        GROUP BY created_on_datetime::date
      ) AS loves_daily USING (activity_date)
      LEFT JOIN (
        SELECT joined_on_datetime::date AS activity_date, COUNT(*)::BIGINT AS users_registered
        FROM geokrety.gk_users
        WHERE p_period IS NULL OR joined_on_datetime <@ p_period
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
        LOWER(m.country) AS country_code,
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
        AND (p_period IS NULL OR m.moved_on_datetime <@ p_period)
      GROUP BY m.moved_on_datetime::date, LOWER(m.country)
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
        AND (p_period IS NULL OR m.moved_on_datetime <@ p_period)
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
        AND (p_period IS NULL OR current_move.moved_on_datetime <@ p_period)
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

CREATE OR REPLACE FUNCTION stats.fn_reconcile_stats()
RETURNS TABLE (
  check_name TEXT,
  source_count BIGINT,
  stats_count BIGINT,
  delta BIGINT,
  status TEXT
)
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  RETURN QUERY
  SELECT *
  FROM stats.fn_reconcile_stats(NULL::TEXT[], NULL::tstzrange);
END;
$$;

CREATE OR REPLACE VIEW stats.v_uc1_country_activity AS
SELECT
  country_code,
  SUM(moves_count)::BIGINT AS moves,
  SUM(km_contributed)::NUMERIC(14,3) AS km
FROM stats.country_daily_stats
GROUP BY country_code;

CREATE OR REPLACE VIEW stats.v_uc2_user_network AS
SELECT
  user_id,
  related_user_id,
  shared_geokrety_count,
  first_seen_at,
  last_seen_at
FROM stats.user_related_users;

CREATE OR REPLACE VIEW stats.v_uc3_gk_circulation AS
SELECT
  geokrety_id,
  COUNT(*)::BIGINT AS users,
  SUM(interaction_count)::BIGINT AS interactions
FROM stats.gk_related_users
GROUP BY geokrety_id;

CREATE OR REPLACE VIEW stats.v_uc4_user_continent_coverage AS
SELECT
  uc.user_id,
  cr.continent_code,
  SUM(uc.move_count)::BIGINT AS moves
FROM stats.user_countries uc
JOIN stats.continent_reference cr
  ON cr.country_alpha2 = UPPER(uc.country_code)::CHAR(2)
GROUP BY uc.user_id, cr.continent_code;

CREATE OR REPLACE VIEW stats.v_uc6_dormancy AS
SELECT
  geokrety_id,
  MAX(last_interaction) AS last_touch,
  clock_timestamp() - MAX(last_interaction) AS dormancy_interval
FROM stats.gk_related_users
GROUP BY geokrety_id;

CREATE OR REPLACE VIEW stats.v_uc7_country_flow AS
SELECT
  year_month,
  from_country,
  to_country,
  move_count,
  unique_gk_count
FROM stats.country_pair_flows;

CREATE OR REPLACE VIEW stats.v_uc8_seasonal_heatmap AS
SELECT
  activity_date,
  hour_utc,
  move_type,
  move_count
FROM stats.hourly_activity;

DO $$
BEGIN
  IF to_regclass('points.gk_multiplier_audit') IS NOT NULL THEN
    EXECUTE $view$
      CREATE OR REPLACE VIEW stats.v_uc9_multiplier_velocity AS
      SELECT
        gk_id,
        MAX(calculated_at) AS last_change,
        AVG(multiplier_after - multiplier_before) AS avg_delta
      FROM points.gk_multiplier_audit
      GROUP BY gk_id
    $view$;
  ELSE
    EXECUTE $view$
      CREATE OR REPLACE VIEW stats.v_uc9_multiplier_velocity AS
      SELECT
        NULL::BIGINT AS gk_id,
        NULL::TIMESTAMPTZ AS last_change,
        NULL::NUMERIC AS avg_delta
      WHERE FALSE
    $view$;
  END IF;
END;
$$;

CREATE OR REPLACE VIEW stats.v_uc10_cache_popularity AS
SELECT
  w.waypoint_code,
  SUM(g.visit_count)::BIGINT AS total_gk_visits,
  COUNT(DISTINCT g.gk_id)::BIGINT AS distinct_gks
FROM stats.gk_cache_visits g
JOIN stats.waypoints w
  ON w.id = g.waypoint_id
GROUP BY w.waypoint_code;

CREATE OR REPLACE VIEW stats.v_uc13_gk_timeline AS
SELECT
  gk_id,
  event_type,
  occurred_at,
  CASE
    WHEN additional_data ? 'actor_user_id'
     AND COALESCE(additional_data->>'actor_user_id', '') ~ '^[0-9]+$'
      THEN (additional_data->>'actor_user_id')::INT
    ELSE NULL
  END AS actor_user_id
FROM stats.gk_milestone_events;

CREATE OR REPLACE VIEW stats.v_uc14_first_finder_hof AS
SELECT
  finder_user_id,
  COUNT(*)::BIGINT AS first_finds
FROM stats.first_finder_events
GROUP BY finder_user_id;

CREATE OR REPLACE VIEW stats.v_uc15_distance_records AS
SELECT
  geokret AS gk_id,
  SUM(km_distance)::NUMERIC(14,3) AS km_total
FROM geokrety.gk_moves
WHERE km_distance IS NOT NULL
GROUP BY geokret;
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP VIEW IF EXISTS stats.v_uc15_distance_records;
DROP VIEW IF EXISTS stats.v_uc14_first_finder_hof;
DROP VIEW IF EXISTS stats.v_uc13_gk_timeline;
DROP VIEW IF EXISTS stats.v_uc10_cache_popularity;
DROP VIEW IF EXISTS stats.v_uc9_multiplier_velocity;
DROP VIEW IF EXISTS stats.v_uc8_seasonal_heatmap;
DROP VIEW IF EXISTS stats.v_uc7_country_flow;
DROP VIEW IF EXISTS stats.v_uc6_dormancy;
DROP VIEW IF EXISTS stats.v_uc4_user_continent_coverage;
DROP VIEW IF EXISTS stats.v_uc3_gk_circulation;
DROP VIEW IF EXISTS stats.v_uc2_user_network;
DROP VIEW IF EXISTS stats.v_uc1_country_activity;

DROP FUNCTION IF EXISTS stats.fn_reconcile_stats();
DROP FUNCTION IF EXISTS stats.fn_reconcile_stats(TEXT[], tstzrange);
DROP FUNCTION IF EXISTS stats.fn_run_all_snapshots();
DROP FUNCTION IF EXISTS stats.fn_run_all_snapshots(TEXT[], tstzrange, INT);
DROP FUNCTION IF EXISTS stats.fn_run_snapshot_phase(TEXT, tstzrange, INT);
DROP FUNCTION IF EXISTS stats.fn_snapshot_country_pair_flows(tstzrange);
DROP FUNCTION IF EXISTS stats.fn_snapshot_hourly_activity(tstzrange);
SQL
        );
    }
}
