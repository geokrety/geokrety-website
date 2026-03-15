<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCountryStatsMaintenance extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
-- Normalizes country codes to 2-character uppercase format, mapping invalid codes to 'UK'
CREATE OR REPLACE FUNCTION geokrety.fn_normalize_country_code(
  p_country TEXT
)
RETURNS CHAR(2)
LANGUAGE plpgsql
IMMUTABLE STRICT
AS $$
DECLARE
  v_trimmed TEXT;
BEGIN
  IF p_country IS NULL THEN
    RETURN NULL;
  END IF;

  v_trimmed := BTRIM(p_country);

  -- If already exactly 2 chars after trim, use it (uppercase)
  IF LENGTH(v_trimmed) = 2 THEN
    RETURN UPPER(v_trimmed)::CHAR(2);
  END IF;

  -- Otherwise, map to 'UK' (unknown)
  RETURN 'UK'::CHAR(2);
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_country_daily_stats_bucket(
  p_stats_date DATE,
  p_country_code TEXT
)
RETURNS VOID
LANGUAGE plpgsql
AS $$
DECLARE
  v_country_code TEXT;
BEGIN
  IF p_stats_date IS NULL OR p_country_code IS NULL THEN
    RETURN;
  END IF;

  v_country_code := geokrety.fn_normalize_country_code(p_country_code);

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
    p_stats_date,
    v_country_code,
    COUNT(*)::BIGINT,
    COUNT(*) FILTER (WHERE move_type = 0)::BIGINT,
    COUNT(*) FILTER (WHERE move_type = 1)::BIGINT,
    COUNT(*) FILTER (WHERE move_type = 2)::BIGINT,
    COUNT(*) FILTER (WHERE move_type = 3)::BIGINT,
    COUNT(*) FILTER (WHERE move_type = 4)::BIGINT,
    COUNT(*) FILTER (WHERE move_type = 5)::BIGINT,
    COUNT(DISTINCT author) FILTER (WHERE author IS NOT NULL)::BIGINT,
    COUNT(DISTINCT geokret)::BIGINT,
    COALESCE(SUM(km_distance), 0)::NUMERIC(14,3)
  FROM geokrety.gk_moves
  WHERE country IS NOT NULL
    AND geokrety.fn_normalize_country_code(country) = v_country_code
    AND moved_on_datetime >= p_stats_date::timestamp with time zone
    AND moved_on_datetime < (p_stats_date + 1)::timestamp with time zone
  GROUP BY 1, 2
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

  IF FOUND THEN
    RETURN;
  END IF;

  UPDATE stats.country_daily_stats
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
   WHERE stats_date = p_stats_date
     AND country_code = v_country_code
     AND (
       points_contributed <> 0
       OR loves_count <> 0
       OR pictures_uploaded_total <> 0
       OR pictures_uploaded_avatar <> 0
       OR pictures_uploaded_move <> 0
       OR pictures_uploaded_user <> 0
     );

  IF FOUND THEN
    RETURN;
  END IF;

  DELETE FROM stats.country_daily_stats
   WHERE stats_date = p_stats_date
    AND country_code = v_country_code;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_gk_country_visit(
  p_geokrety_id BIGINT,
  p_country_code TEXT
)
RETURNS VOID
LANGUAGE plpgsql
AS $$
DECLARE
  v_country_code TEXT;
BEGIN
  IF p_geokrety_id IS NULL OR p_country_code IS NULL THEN
    RETURN;
  END IF;

  v_country_code := geokrety.fn_normalize_country_code(p_country_code);

  INSERT INTO stats.gk_countries_visited (
    geokrety_id,
    country_code,
    first_visited_at,
    first_move_id,
    move_count
  )
  SELECT
    p_geokrety_id,
    v_country_code,
    MIN(moved_on_datetime),
    (array_agg(id ORDER BY moved_on_datetime ASC, id ASC))[1],
    COUNT(*)::INT
  FROM geokrety.gk_moves
  WHERE geokret = p_geokrety_id
    AND country IS NOT NULL
    AND geokrety.fn_normalize_country_code(country) = v_country_code
  GROUP BY 1, 2
  ON CONFLICT (geokrety_id, country_code) DO UPDATE SET
    first_visited_at = EXCLUDED.first_visited_at,
    first_move_id = EXCLUDED.first_move_id,
    move_count = EXCLUDED.move_count;

  IF FOUND THEN
    RETURN;
  END IF;

  DELETE FROM stats.gk_countries_visited
   WHERE geokrety_id = p_geokrety_id
    AND country_code = v_country_code;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_user_country_visit(
  p_user_id BIGINT,
  p_country_code TEXT
)
RETURNS VOID
LANGUAGE plpgsql
AS $$
DECLARE
  v_country_code TEXT;
BEGIN
  IF p_user_id IS NULL OR p_country_code IS NULL THEN
    RETURN;
  END IF;

  v_country_code := geokrety.fn_normalize_country_code(p_country_code);

  INSERT INTO stats.user_countries (
    user_id,
    country_code,
    move_count,
    first_visit,
    last_visit
  )
  SELECT
    p_user_id,
    v_country_code,
    COUNT(*)::BIGINT,
    MIN(moved_on_datetime),
    MAX(moved_on_datetime)
  FROM geokrety.gk_moves
  WHERE author = p_user_id
    AND country IS NOT NULL
    AND geokrety.fn_normalize_country_code(country) = v_country_code
  GROUP BY 1, 2
  ON CONFLICT (user_id, country_code) DO UPDATE SET
    move_count = EXCLUDED.move_count,
    first_visit = EXCLUDED.first_visit,
    last_visit = EXCLUDED.last_visit;

  IF FOUND THEN
    RETURN;
  END IF;

  DELETE FROM stats.user_countries
   WHERE user_id = p_user_id
    AND country_code = v_country_code;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_country_rollups()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_old_date DATE;
  v_new_date DATE;
BEGIN
  v_old_date := CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN OLD.moved_on_datetime::date ELSE NULL END;
  v_new_date := CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN NEW.moved_on_datetime::date ELSE NULL END;

  IF TG_OP IN ('UPDATE', 'DELETE') AND OLD.country IS NOT NULL THEN
    PERFORM geokrety.fn_refresh_country_daily_stats_bucket(v_old_date, OLD.country);
    PERFORM geokrety.fn_refresh_gk_country_visit(OLD.geokret, OLD.country);

    IF OLD.author IS NOT NULL THEN
      PERFORM geokrety.fn_refresh_user_country_visit(OLD.author, OLD.country);
    END IF;
  END IF;

  IF TG_OP IN ('INSERT', 'UPDATE') AND NEW.country IS NOT NULL THEN
    IF TG_OP <> 'UPDATE'
       OR NEW.country IS DISTINCT FROM OLD.country
       OR v_new_date IS DISTINCT FROM v_old_date THEN
      PERFORM geokrety.fn_refresh_country_daily_stats_bucket(v_new_date, NEW.country);
    END IF;

    IF TG_OP <> 'UPDATE'
       OR NEW.country IS DISTINCT FROM OLD.country
       OR NEW.geokret IS DISTINCT FROM OLD.geokret THEN
      PERFORM geokrety.fn_refresh_gk_country_visit(NEW.geokret, NEW.country);
    END IF;

    IF NEW.author IS NOT NULL
       AND (
         TG_OP <> 'UPDATE'
         OR NEW.country IS DISTINCT FROM OLD.country
         OR NEW.author IS DISTINCT FROM OLD.author
       ) THEN
      PERFORM geokrety.fn_refresh_user_country_visit(NEW.author, NEW.country);
    END IF;
  END IF;

  RETURN NULL;
END;
$$;

DROP TRIGGER IF EXISTS tr_gk_moves_after_country_rollups ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_country_rollups
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_country_rollups();

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_gk_country_history(
  p_geokrety_id BIGINT
)
RETURNS VOID
LANGUAGE plpgsql
AS $$
BEGIN
  IF p_geokrety_id IS NULL THEN
    RETURN;
  END IF;

  DELETE FROM stats.gk_country_history
   WHERE geokrety_id = p_geokrety_id;

  INSERT INTO stats.gk_country_history (
    geokrety_id,
    country_code,
    arrived_at,
    departed_at,
    move_id
  )
  WITH ordered_moves AS (
    SELECT
      m.id,
      m.geokret,
      geokrety.fn_normalize_country_code(m.country) AS country,
      m.moved_on_datetime,
      LAG(geokrety.fn_normalize_country_code(m.country)) OVER (
        ORDER BY m.moved_on_datetime, m.id
      ) AS previous_country
    FROM geokrety.gk_moves m
    WHERE m.geokret = p_geokrety_id
      AND m.country IS NOT NULL
      AND m.move_type IN (0, 1, 3, 5)
  ),
  transitions AS (
    SELECT
      id,
      geokret,
      country,
      moved_on_datetime,
      LEAD(moved_on_datetime) OVER (ORDER BY moved_on_datetime, id) AS departed_at
    FROM ordered_moves
    WHERE previous_country IS DISTINCT FROM country
  )
  SELECT
    geokret,
    country,
    moved_on_datetime,
    departed_at,
    id
  FROM transitions;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_country_history()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
  IF TG_OP IN ('UPDATE', 'DELETE') THEN
    PERFORM geokrety.fn_refresh_gk_country_history(OLD.geokret);
  END IF;

  IF TG_OP IN ('INSERT', 'UPDATE')
     AND (TG_OP <> 'UPDATE' OR NEW.geokret IS DISTINCT FROM OLD.geokret) THEN
    PERFORM geokrety.fn_refresh_gk_country_history(NEW.geokret);
  END IF;

  RETURN NULL;
END;
$$;

DROP TRIGGER IF EXISTS tr_gk_moves_after_country_history ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_country_history
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_country_history();

CREATE OR REPLACE FUNCTION stats.fn_snapshot_daily_country_stats(
  p_period daterange DEFAULT NULL
)
RETURNS BIGINT
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

COMMENT ON FUNCTION stats.fn_snapshot_daily_country_stats IS 'Seeds country_daily_stats from gk_moves. Idempotent via ON CONFLICT DO UPDATE. Optional p_period limits date range.';

CREATE OR REPLACE FUNCTION stats.fn_snapshot_user_country_stats(
  p_period daterange DEFAULT NULL
)
RETURNS BIGINT
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

COMMENT ON FUNCTION stats.fn_snapshot_user_country_stats IS 'Seeds user_countries from gk_moves. Optional p_period recomputes only touched user-country keys using full source history for exact aggregates.';

CREATE OR REPLACE FUNCTION stats.fn_snapshot_gk_country_stats(
  p_period daterange DEFAULT NULL
)
RETURNS BIGINT
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

COMMENT ON FUNCTION stats.fn_snapshot_gk_country_stats IS 'Seeds gk_countries_visited from gk_moves. Optional p_period recomputes only touched geokret-country keys using full source history for exact aggregates.';

CREATE INDEX IF NOT EXISTS idx_country_daily_stats_country_date
  ON stats.country_daily_stats (country_code, stats_date);

CREATE INDEX IF NOT EXISTS idx_gk_country_history_active_by_country
  ON stats.gk_country_history (country_code)
  WHERE departed_at IS NULL;

CREATE INDEX IF NOT EXISTS idx_gk_country_history_gk_arrived
  ON stats.gk_country_history (geokrety_id, arrived_at DESC);
SQL
        );
    }

    public function down(): void {
        $this->execute('DROP INDEX IF EXISTS stats.idx_gk_country_history_gk_arrived;');
        $this->execute('DROP INDEX IF EXISTS stats.idx_gk_country_history_active_by_country;');
        $this->execute('DROP INDEX IF EXISTS stats.idx_country_daily_stats_country_date;');
        $this->execute('DROP FUNCTION IF EXISTS stats.fn_snapshot_gk_country_stats(daterange);');
        $this->execute('DROP FUNCTION IF EXISTS stats.fn_snapshot_user_country_stats(daterange);');
        $this->execute('DROP FUNCTION IF EXISTS stats.fn_snapshot_daily_country_stats(daterange);');
        $this->execute('DROP TRIGGER IF EXISTS tr_gk_moves_after_country_history ON geokrety.gk_moves;');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_gk_moves_country_history();');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_refresh_gk_country_history(bigint);');
        $this->execute('DROP TRIGGER IF EXISTS tr_gk_moves_after_country_rollups ON geokrety.gk_moves;');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_gk_moves_country_rollups();');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_refresh_user_country_visit(bigint, text);');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_refresh_gk_country_visit(bigint, text);');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_refresh_country_daily_stats_bucket(date, text);');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_normalize_country_code(text);');
    }
}
