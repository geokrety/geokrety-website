<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SquashPreviousMoveBackfillChain extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
DROP TRIGGER IF EXISTS tr_gk_moves_before_prev_move ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_moves_after_prev_move_insert ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_moves_after_prev_move_update ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_moves_after_prev_move_delete ON geokrety.gk_moves;

DROP FUNCTION IF EXISTS geokrety.fn_set_previous_move_id_and_distance();
DROP FUNCTION IF EXISTS geokrety.fn_refresh_previous_move_ids_after_insert();
DROP FUNCTION IF EXISTS geokrety.fn_refresh_previous_move_ids_after_update();
DROP FUNCTION IF EXISTS geokrety.fn_rewire_previous_move_ids_after_delete();
DROP FUNCTION IF EXISTS geokrety.fn_refresh_previous_move_chain(BIGINT);
DROP FUNCTION IF EXISTS geokrety.fn_refresh_previous_move_chains(BIGINT[]);

DROP FUNCTION IF EXISTS stats.fn_run_snapshot_phase(TEXT, tstzrange, INT);
DROP FUNCTION IF EXISTS stats.fn_run_all_snapshots();
DROP FUNCTION IF EXISTS stats.fn_run_all_snapshots(TEXT[], tstzrange, INT);
DROP FUNCTION IF EXISTS stats.fn_backfill_heavy_km_distance_all(INT);
DROP FUNCTION IF EXISTS stats.fn_backfill_heavy_km_distance_all(INT, INT);
DROP FUNCTION IF EXISTS stats.fn_backfill_heavy_previous_move_id_all(INT);
DROP FUNCTION IF EXISTS stats.fn_backfill_heavy_previous_move_id_all(INT, INT);
DROP FUNCTION IF EXISTS stats.fn_backfill_km_distance(tstzrange, INT);
DROP FUNCTION IF EXISTS stats.fn_backfill_previous_move_id(tstzrange, INT);

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_previous_move_chains(
  p_geokret_ids BIGINT[]
)
RETURNS BIGINT
LANGUAGE plpgsql
AS $BODY$
DECLARE
  v_rows_updated BIGINT := 0;
BEGIN
  IF p_geokret_ids IS NULL OR array_length(p_geokret_ids, 1) IS NULL THEN
    RETURN 0;
  END IF;

  WITH requested_geokrets AS (
    SELECT DISTINCT geokret
    FROM unnest(p_geokret_ids) AS geokret
    WHERE geokret IS NOT NULL
  ),
  base_moves AS (
    SELECT
      m.id,
      m.geokret,
      m.move_type,
      m.position,
      m.moved_on_datetime
    FROM geokrety.gk_moves m
    JOIN requested_geokrets rg ON rg.geokret = m.geokret
  ),
  qualifying_moves AS (
    SELECT
      m.id,
      m.geokret,
      row_number() OVER (
        PARTITION BY m.geokret
        ORDER BY m.moved_on_datetime, m.id
      ) AS qualifying_seq
    FROM base_moves m
    WHERE m.move_type IN (0, 1, 3, 5)
  ),
  positioned_moves AS (
    SELECT
      m.id,
      m.geokret,
      row_number() OVER (
        PARTITION BY m.geokret
        ORDER BY m.moved_on_datetime, m.id
      ) AS positioned_seq,
      m.position
    FROM base_moves m
    WHERE m.move_type IN (0, 1, 3, 5)
      AND m.position IS NOT NULL
  ),
  chain_state AS (
    SELECT
      m.id,
      m.geokret,
      m.move_type,
      m.position,
      count(*) FILTER (
        WHERE m.move_type IN (0, 1, 3, 5)
      ) OVER (
        PARTITION BY m.geokret
        ORDER BY m.moved_on_datetime, m.id
        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
      ) AS qualifying_seen,
      count(*) FILTER (
        WHERE m.move_type IN (0, 1, 3, 5)
          AND m.position IS NOT NULL
      ) OVER (
        PARTITION BY m.geokret
        ORDER BY m.moved_on_datetime, m.id
        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
      ) AS positioned_seen
    FROM base_moves m
  ),
  recomputed AS (
    SELECT
      s.id,
      qm.id AS previous_move_id,
      pm.id AS previous_position_id,
      CASE
        WHEN s.move_type IN (0, 1, 3, 5)
         AND s.position IS NOT NULL
         AND pm.position IS NOT NULL
          THEN (public.ST_Distance(pm.position, s.position) / 1000.0)::NUMERIC(8,3)
        ELSE NULL
      END AS km_distance
    FROM chain_state s
    LEFT JOIN qualifying_moves qm
      ON qm.geokret = s.geokret
     AND qm.qualifying_seq = s.qualifying_seen - CASE WHEN s.move_type IN (0, 1, 3, 5) THEN 1 ELSE 0 END
    LEFT JOIN positioned_moves pm
      ON pm.geokret = s.geokret
     AND pm.positioned_seq = s.positioned_seen - CASE WHEN s.move_type IN (0, 1, 3, 5) AND s.position IS NOT NULL THEN 1 ELSE 0 END
  )
  UPDATE geokrety.gk_moves g
     SET previous_move_id = r.previous_move_id,
         previous_position_id = r.previous_position_id,
         km_distance = r.km_distance
    FROM recomputed r
   WHERE g.id = r.id
     AND (
       g.previous_move_id IS DISTINCT FROM r.previous_move_id
       OR g.previous_position_id IS DISTINCT FROM r.previous_position_id
       OR g.km_distance IS DISTINCT FROM r.km_distance
     );

  GET DIAGNOSTICS v_rows_updated = ROW_COUNT;
  RETURN v_rows_updated;
END;
$BODY$;

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_previous_move_chain(
  p_geokret_id BIGINT
)
RETURNS VOID
LANGUAGE plpgsql
AS $BODY$
BEGIN
  IF p_geokret_id IS NULL THEN
    RETURN;
  END IF;

  PERFORM geokrety.fn_refresh_previous_move_chains(ARRAY[p_geokret_id]);
END;
$BODY$;

CREATE OR REPLACE FUNCTION geokrety.fn_set_previous_move_id_and_distance()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $BODY$
DECLARE
  v_previous_move_id BIGINT;
  v_previous_position_id BIGINT;
  v_current_move_id BIGINT;
  v_move_qualifies BOOLEAN;
  v_has_position BOOLEAN;
BEGIN
  v_current_move_id := COALESCE(NEW.id, 9223372036854775807);
  v_move_qualifies := NEW.move_type IN (0, 1, 3, 5);
  v_has_position := NEW.position IS NOT NULL;

  IF TG_OP = 'UPDATE'
     AND OLD.geokret IS NOT DISTINCT FROM NEW.geokret
     AND OLD.moved_on_datetime IS NOT DISTINCT FROM NEW.moved_on_datetime
     AND OLD.move_type IS NOT DISTINCT FROM NEW.move_type
     AND OLD.position IS NOT DISTINCT FROM NEW.position THEN
    RETURN NEW;
  END IF;

  SELECT m.id
    INTO v_previous_move_id
  FROM geokrety.gk_moves m
  WHERE m.geokret = NEW.geokret
    AND m.id <> v_current_move_id
    AND m.move_type IN (0, 1, 3, 5)
    AND (
      m.moved_on_datetime < NEW.moved_on_datetime
      OR (m.moved_on_datetime = NEW.moved_on_datetime AND m.id < v_current_move_id)
    )
  ORDER BY m.moved_on_datetime DESC, m.id DESC
  LIMIT 1;

  NEW.previous_move_id := v_previous_move_id;

  SELECT pm.id
    INTO v_previous_position_id
  FROM geokrety.gk_geokrety g
  JOIN geokrety.gk_moves pm ON pm.id = g.last_position
  WHERE g.id = NEW.geokret
    AND pm.id <> v_current_move_id
    AND pm.position IS NOT NULL
    AND pm.move_type IN (0, 1, 3, 5)
    AND (
      pm.moved_on_datetime < NEW.moved_on_datetime
      OR (pm.moved_on_datetime = NEW.moved_on_datetime AND pm.id < v_current_move_id)
    );

  IF v_previous_position_id IS NULL THEN
    SELECT m.id
      INTO v_previous_position_id
    FROM geokrety.gk_moves m
    WHERE m.geokret = NEW.geokret
      AND m.id <> v_current_move_id
      AND m.position IS NOT NULL
      AND m.move_type IN (0, 1, 3, 5)
      AND (
        m.moved_on_datetime < NEW.moved_on_datetime
        OR (m.moved_on_datetime = NEW.moved_on_datetime AND m.id < v_current_move_id)
      )
    ORDER BY m.moved_on_datetime DESC, m.id DESC
    LIMIT 1;
  END IF;

  NEW.previous_position_id := v_previous_position_id;

  IF v_move_qualifies
     AND v_has_position
     AND NEW.previous_position_id IS NOT NULL THEN
    SELECT (public.ST_Distance(pm.position, NEW.position) / 1000.0)::NUMERIC(8,3)
      INTO NEW.km_distance
    FROM geokrety.gk_moves pm
    WHERE pm.id = NEW.previous_position_id
      AND pm.position IS NOT NULL;
  ELSE
    NEW.km_distance := NULL;
  END IF;

  RETURN NEW;
END;
$BODY$;

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_previous_move_ids_after_insert()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $BODY$
BEGIN
  IF pg_trigger_depth() > 1 THEN
    RETURN NULL;
  END IF;

  PERFORM geokrety.fn_refresh_previous_move_chains(
    ARRAY(
      SELECT DISTINCT geokret
      FROM new_moves
      WHERE geokret IS NOT NULL
    )
  );

  RETURN NULL;
END;
$BODY$;

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_previous_move_ids_after_update()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $BODY$
BEGIN
  IF pg_trigger_depth() > 1 THEN
    RETURN NULL;
  END IF;

  IF NOT EXISTS (
    SELECT 1
    FROM old_moves o
    JOIN new_moves n USING (id)
    WHERE o.geokret IS DISTINCT FROM n.geokret
       OR o.moved_on_datetime IS DISTINCT FROM n.moved_on_datetime
       OR o.move_type IS DISTINCT FROM n.move_type
       OR o.position IS DISTINCT FROM n.position
       OR o.previous_move_id IS DISTINCT FROM n.previous_move_id
       OR o.previous_position_id IS DISTINCT FROM n.previous_position_id
       OR o.km_distance IS DISTINCT FROM n.km_distance
  ) THEN
    RETURN NULL;
  END IF;

  PERFORM geokrety.fn_refresh_previous_move_chains(
    ARRAY(
      SELECT DISTINCT geokret
      FROM (
        SELECT geokret FROM old_moves
        UNION
        SELECT geokret FROM new_moves
      ) AS affected
      WHERE geokret IS NOT NULL
    )
  );

  RETURN NULL;
END;
$BODY$;

CREATE OR REPLACE FUNCTION geokrety.fn_rewire_previous_move_ids_after_delete()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $BODY$
BEGIN
  IF pg_trigger_depth() > 1 THEN
    RETURN NULL;
  END IF;

  PERFORM geokrety.fn_refresh_previous_move_chains(
    ARRAY(
      SELECT DISTINCT geokret
      FROM deleted_moves
      WHERE geokret IS NOT NULL
    )
  );

  RETURN NULL;
END;
$BODY$;

CREATE TRIGGER tr_gk_moves_before_prev_move
  BEFORE INSERT OR UPDATE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_set_previous_move_id_and_distance();

CREATE TRIGGER tr_gk_moves_after_prev_move_insert
  AFTER INSERT ON geokrety.gk_moves
  REFERENCING NEW TABLE AS new_moves
  FOR EACH STATEMENT EXECUTE FUNCTION geokrety.fn_refresh_previous_move_ids_after_insert();

CREATE TRIGGER tr_gk_moves_after_prev_move_update
  AFTER UPDATE ON geokrety.gk_moves
  REFERENCING OLD TABLE AS old_moves NEW TABLE AS new_moves
  FOR EACH STATEMENT EXECUTE FUNCTION geokrety.fn_refresh_previous_move_ids_after_update();

CREATE TRIGGER tr_gk_moves_after_prev_move_delete
  AFTER DELETE ON geokrety.gk_moves
  REFERENCING OLD TABLE AS deleted_moves
  FOR EACH STATEMENT EXECUTE FUNCTION geokrety.fn_rewire_previous_move_ids_after_delete();
SQL
        );

        $this->execute(<<<'SQL'
DROP MATERIALIZED VIEW IF EXISTS stats.mv_backfill_working_set CASCADE;

CREATE MATERIALIZED VIEW stats.mv_backfill_working_set AS
WITH base_moves AS (
  SELECT
    m.id,
    m.geokret,
    m.moved_on_datetime,
    m.position,
    m.km_distance,
    m.move_type
  FROM geokrety.gk_moves m
  WHERE m.geokret IS NOT NULL
),
qualifying_moves AS (
  SELECT
    m.id,
    m.geokret,
    row_number() OVER (
      PARTITION BY m.geokret
      ORDER BY m.moved_on_datetime, m.id
    ) AS qualifying_seq
  FROM base_moves m
  WHERE m.move_type IN (0, 1, 3, 5)
),
positioned_moves AS (
  SELECT
    m.id,
    m.geokret,
    row_number() OVER (
      PARTITION BY m.geokret
      ORDER BY m.moved_on_datetime, m.id
    ) AS positioned_seq,
    m.position
  FROM base_moves m
  WHERE m.move_type IN (0, 1, 3, 5)
    AND m.position IS NOT NULL
),
chain_state AS (
  SELECT
    m.id,
    m.geokret,
    m.move_type,
    m.position,
    count(*) FILTER (
      WHERE m.move_type IN (0, 1, 3, 5)
    ) OVER (
      PARTITION BY m.geokret
      ORDER BY m.moved_on_datetime, m.id
      ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
    ) AS qualifying_seen,
    count(*) FILTER (
      WHERE m.move_type IN (0, 1, 3, 5)
        AND m.position IS NOT NULL
    ) OVER (
      PARTITION BY m.geokret
      ORDER BY m.moved_on_datetime, m.id
      ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
    ) AS positioned_seen
  FROM base_moves m
)
SELECT
  s.id,
  s.geokret,
  b.moved_on_datetime,
  s.position,
  b.km_distance,
  s.move_type,
  qm.id AS previous_move_id,
  pm.id AS previous_position_id,
  CASE
    WHEN s.move_type IN (0, 1, 3, 5)
     AND s.position IS NOT NULL
     AND pm.position IS NOT NULL
      THEN (public.ST_Distance(pm.position, s.position) / 1000.0)::NUMERIC(8,3)
    ELSE NULL
  END AS expected_km_distance,
  pm.position AS previous_position
FROM chain_state s
JOIN base_moves b ON b.id = s.id
LEFT JOIN qualifying_moves qm
  ON qm.geokret = s.geokret
 AND qm.qualifying_seq = s.qualifying_seen - CASE WHEN s.move_type IN (0, 1, 3, 5) THEN 1 ELSE 0 END
LEFT JOIN positioned_moves pm
  ON pm.geokret = s.geokret
 AND pm.positioned_seq = s.positioned_seen - CASE WHEN s.move_type IN (0, 1, 3, 5) AND s.position IS NOT NULL THEN 1 ELSE 0 END
ORDER BY b.moved_on_datetime, b.id
WITH NO DATA;

CREATE INDEX idx_mv_backfill_period
  ON stats.mv_backfill_working_set (moved_on_datetime, geokret, id);
SQL
        );

        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION stats.fn_backfill_previous_move_id(
  p_period TSTZRANGE DEFAULT tstzrange('-infinity'::TIMESTAMPTZ, 'infinity'::TIMESTAMPTZ, '[)'),
  p_batch_size INT DEFAULT 50000
)
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $BODY$
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
$BODY$;

CREATE OR REPLACE FUNCTION stats.fn_backfill_km_distance(
  p_period TSTZRANGE DEFAULT tstzrange('-infinity'::TIMESTAMPTZ, 'infinity'::TIMESTAMPTZ, '[)'),
  p_batch_size INT DEFAULT 50000
)
RETURNS BIGINT
LANGUAGE plpgsql
SECURITY DEFINER
AS $BODY$
DECLARE
  v_started_at TIMESTAMPTZ := clock_timestamp();
  v_rows_updated BIGINT := 0;
BEGIN
  v_rows_updated := stats.fn_backfill_previous_move_id(p_period, p_batch_size);

  INSERT INTO stats.job_log (job_name, status, metadata, started_at, completed_at)
  VALUES (
    'fn_backfill_km_distance',
    'ok',
    jsonb_build_object(
      'period', p_period,
      'batch_size', p_batch_size,
      'rows_updated', v_rows_updated,
      'strategy', 'delegated_previous_move_backfill'
    ),
    v_started_at,
    clock_timestamp()
  );

  RETURN v_rows_updated;
EXCEPTION WHEN OTHERS THEN
  INSERT INTO stats.job_log (job_name, status, metadata, started_at, completed_at)
  VALUES (
    'fn_backfill_km_distance',
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
$BODY$;

CREATE OR REPLACE FUNCTION stats.fn_backfill_heavy_previous_move_id_all(
  p_batch_size INT DEFAULT 5000,
  p_month_limit INT DEFAULT 16
)
RETURNS TEXT
LANGUAGE plpgsql
SECURITY DEFINER
AS $BODY$
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
$BODY$;
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP TRIGGER IF EXISTS tr_gk_moves_before_prev_move ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_moves_after_prev_move_insert ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_moves_after_prev_move_update ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_moves_after_prev_move_delete ON geokrety.gk_moves;

DROP FUNCTION IF EXISTS geokrety.fn_set_previous_move_id_and_distance();
DROP FUNCTION IF EXISTS geokrety.fn_refresh_previous_move_ids_after_insert();
DROP FUNCTION IF EXISTS geokrety.fn_refresh_previous_move_ids_after_update();
DROP FUNCTION IF EXISTS geokrety.fn_rewire_previous_move_ids_after_delete();
DROP FUNCTION IF EXISTS geokrety.fn_refresh_previous_move_chain(BIGINT);
DROP FUNCTION IF EXISTS geokrety.fn_refresh_previous_move_chains(BIGINT[]);

DROP FUNCTION IF EXISTS stats.fn_backfill_heavy_previous_move_id_all(INT, INT);
DROP FUNCTION IF EXISTS stats.fn_backfill_heavy_previous_move_id_all(INT);
DROP FUNCTION IF EXISTS stats.fn_backfill_km_distance(tstzrange, INT);
DROP FUNCTION IF EXISTS stats.fn_backfill_previous_move_id(tstzrange, INT);

DROP MATERIALIZED VIEW IF EXISTS stats.mv_backfill_working_set;
SQL
        );
    }
}
