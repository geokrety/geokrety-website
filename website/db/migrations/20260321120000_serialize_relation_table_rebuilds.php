<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SerializeRelationTableRebuilds extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
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

CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_relations()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_geokrety_ids INT[] := ARRAY[]::INT[];
  v_user_ids INT[] := ARRAY[]::INT[];
BEGIN
  IF TG_OP IN ('UPDATE', 'DELETE')
     AND OLD.author IS NOT NULL
     AND OLD.move_type IN (0, 1, 3, 5) THEN
    v_geokrety_ids := array_append(v_geokrety_ids, OLD.geokret);
    v_user_ids := array_append(v_user_ids, OLD.author);
  END IF;

  IF TG_OP IN ('INSERT', 'UPDATE')
     AND NEW.author IS NOT NULL
     AND NEW.move_type IN (0, 1, 3, 5) THEN
    v_geokrety_ids := array_append(v_geokrety_ids, NEW.geokret);
    v_user_ids := array_append(v_user_ids, NEW.author);
  END IF;

  SELECT array_agg(DISTINCT geokrety_id)
    INTO v_geokrety_ids
  FROM unnest(v_geokrety_ids) AS affected_geokrety(geokrety_id);

  IF v_geokrety_ids IS NULL OR cardinality(v_geokrety_ids) = 0 THEN
    RETURN COALESCE(NEW, OLD);
  END IF;

  -- Serialize live reconciliation with scoped/full snapshot relation rebuilds.
  PERFORM pg_advisory_xact_lock(20260321, 1);

  DELETE FROM stats.gk_related_users
  WHERE geokrety_id = ANY(v_geokrety_ids);

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
  WHERE m.geokret = ANY(v_geokrety_ids)
    AND m.author IS NOT NULL
    AND m.move_type IN (0, 1, 3, 5)
  GROUP BY m.geokret, m.author;

  SELECT array_agg(DISTINCT user_id)
    INTO v_user_ids
  FROM (
    SELECT unnest(v_user_ids) AS user_id
    UNION
    SELECT gru.user_id
    FROM stats.gk_related_users gru
    WHERE gru.geokrety_id = ANY(v_geokrety_ids)
  ) AS affected_users;

  IF v_user_ids IS NOT NULL AND cardinality(v_user_ids) > 0 THEN
    DELETE FROM stats.user_related_users
    WHERE user_id = ANY(v_user_ids)
       OR related_user_id = ANY(v_user_ids);

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
    WHERE left_side.user_id = ANY(v_user_ids)
       OR right_side.user_id = ANY(v_user_ids)
    GROUP BY left_side.user_id, right_side.user_id;
  END IF;

  RETURN COALESCE(NEW, OLD);
END;
$$;
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
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

CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_relations()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_geokrety_ids INT[] := ARRAY[]::INT[];
  v_user_ids INT[] := ARRAY[]::INT[];
BEGIN
  IF TG_OP IN ('UPDATE', 'DELETE')
     AND OLD.author IS NOT NULL
     AND OLD.move_type IN (0, 1, 3, 5) THEN
    v_geokrety_ids := array_append(v_geokrety_ids, OLD.geokret);
    v_user_ids := array_append(v_user_ids, OLD.author);
  END IF;

  IF TG_OP IN ('INSERT', 'UPDATE')
     AND NEW.author IS NOT NULL
     AND NEW.move_type IN (0, 1, 3, 5) THEN
    v_geokrety_ids := array_append(v_geokrety_ids, NEW.geokret);
    v_user_ids := array_append(v_user_ids, NEW.author);
  END IF;

  SELECT array_agg(DISTINCT geokrety_id)
    INTO v_geokrety_ids
  FROM unnest(v_geokrety_ids) AS affected_geokrety(geokrety_id);

  IF v_geokrety_ids IS NULL OR cardinality(v_geokrety_ids) = 0 THEN
    RETURN COALESCE(NEW, OLD);
  END IF;

  DELETE FROM stats.gk_related_users
  WHERE geokrety_id = ANY(v_geokrety_ids);

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
  WHERE m.geokret = ANY(v_geokrety_ids)
    AND m.author IS NOT NULL
    AND m.move_type IN (0, 1, 3, 5)
  GROUP BY m.geokret, m.author;

  SELECT array_agg(DISTINCT user_id)
    INTO v_user_ids
  FROM (
    SELECT unnest(v_user_ids) AS user_id
    UNION
    SELECT gru.user_id
    FROM stats.gk_related_users gru
    WHERE gru.geokrety_id = ANY(v_geokrety_ids)
  ) AS affected_users;

  IF v_user_ids IS NOT NULL AND cardinality(v_user_ids) > 0 THEN
    DELETE FROM stats.user_related_users
    WHERE user_id = ANY(v_user_ids)
       OR related_user_id = ANY(v_user_ids);

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
    WHERE left_side.user_id = ANY(v_user_ids)
       OR right_side.user_id = ANY(v_user_ids)
    GROUP BY left_side.user_id, right_side.user_id;
  END IF;

  RETURN COALESCE(NEW, OLD);
END;
$$;
SQL
        );
    }
}
