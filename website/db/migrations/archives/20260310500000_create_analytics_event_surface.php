<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAnalyticsEventSurface extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE TABLE stats.hourly_activity (
  activity_date DATE NOT NULL,
  hour_utc SMALLINT NOT NULL,
  move_type SMALLINT NOT NULL,
  move_count BIGINT NOT NULL DEFAULT 0,
  PRIMARY KEY (activity_date, hour_utc, move_type),
  CONSTRAINT chk_hourly_activity_hour CHECK (hour_utc BETWEEN 0 AND 23),
  CONSTRAINT chk_hourly_activity_move_type CHECK (move_type BETWEEN 0 AND 5)
);

COMMENT ON TABLE stats.hourly_activity IS 'Aggregate move count by UTC date, UTC hour, and move type; powers Sprint 5 hourly analytics';
COMMENT ON COLUMN stats.hourly_activity.activity_date IS 'UTC calendar date bucket for the move';
COMMENT ON COLUMN stats.hourly_activity.hour_utc IS 'UTC hour bucket from 0 through 23';
COMMENT ON COLUMN stats.hourly_activity.move_type IS 'GeoKrety move type bucket (0=drop, 1=grab, 2=comment, 3=seen, 4=archive, 5=dip)';
COMMENT ON COLUMN stats.hourly_activity.move_count IS 'Exact number of moves aggregated into this UTC date/hour/type cell';

CREATE TABLE stats.country_pair_flows (
  year_month DATE NOT NULL,
  from_country CHAR(2) NOT NULL,
  to_country CHAR(2) NOT NULL,
  move_count BIGINT NOT NULL DEFAULT 0,
  unique_gk_count BIGINT NOT NULL DEFAULT 0,
  PRIMARY KEY (year_month, from_country, to_country),
  CONSTRAINT chk_country_pair_flows_month_start CHECK (
    year_month = date_trunc('month', year_month::timestamp with time zone)::date
  ),
  CONSTRAINT chk_country_pair_flows_distinct_countries CHECK (from_country <> to_country),
  CONSTRAINT chk_country_pair_flows_from_upper CHECK (from_country = UPPER(from_country)),
  CONSTRAINT chk_country_pair_flows_to_upper CHECK (to_country = UPPER(to_country))
);

COMMENT ON TABLE stats.country_pair_flows IS 'Monthly cross-country GeoKret flow counts; one row per month/from/to pair';
COMMENT ON COLUMN stats.country_pair_flows.year_month IS 'First day of the UTC month bucket';
COMMENT ON COLUMN stats.country_pair_flows.from_country IS 'Uppercase ISO 3166-1 alpha-2 origin country code';
COMMENT ON COLUMN stats.country_pair_flows.to_country IS 'Uppercase ISO 3166-1 alpha-2 destination country code';
COMMENT ON COLUMN stats.country_pair_flows.move_count IS 'Number of qualifying cross-country moves recorded in the month';
COMMENT ON COLUMN stats.country_pair_flows.unique_gk_count IS 'Number of distinct GeoKrety that crossed from origin to destination in the month';

CREATE TABLE stats.gk_milestone_events (
  id BIGSERIAL NOT NULL,
  gk_id INT NOT NULL,
  event_type TEXT NOT NULL,
  event_value NUMERIC NULL,
  additional_data JSONB NULL,
  occurred_at TIMESTAMPTZ NOT NULL,
  recorded_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  PRIMARY KEY (id),
  CONSTRAINT uq_gk_milestone_events_gk_event_type UNIQUE (gk_id, event_type),
  CONSTRAINT chk_gk_milestone_events_event_type CHECK (
    event_type IN (
      'km_100',
      'km_1000',
      'km_10000',
      'users_10',
      'users_50',
      'users_100',
      'first_find'
    )
  )
);

CREATE INDEX idx_gk_milestone_events_gk
  ON stats.gk_milestone_events (gk_id, occurred_at DESC);

CREATE INDEX idx_gk_milestone_events_type
  ON stats.gk_milestone_events (event_type, occurred_at DESC);

COMMENT ON TABLE stats.gk_milestone_events IS 'Append-only per-GeoKret milestone event log for Sprint 5 analytics';
COMMENT ON COLUMN stats.gk_milestone_events.gk_id IS 'GeoKret internal identifier';
COMMENT ON COLUMN stats.gk_milestone_events.event_type IS 'Milestone type key';
COMMENT ON COLUMN stats.gk_milestone_events.event_value IS 'Threshold value reached when the milestone fired';
COMMENT ON COLUMN stats.gk_milestone_events.additional_data IS 'Optional milestone metadata such as country code or related actor context';
COMMENT ON COLUMN stats.gk_milestone_events.occurred_at IS 'Timestamp when the milestone was reached';
COMMENT ON COLUMN stats.gk_milestone_events.recorded_at IS 'Timestamp when the milestone row was appended';

CREATE TABLE stats.first_finder_events (
  gk_id INT NOT NULL,
  finder_user_id INT NOT NULL,
  move_id BIGINT NOT NULL,
  move_type SMALLINT NOT NULL,
  hours_since_creation SMALLINT NOT NULL,
  found_at TIMESTAMPTZ NOT NULL,
  gk_created_at TIMESTAMPTZ NOT NULL,
  recorded_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  PRIMARY KEY (gk_id),
  CONSTRAINT chk_first_finder_events_move_type CHECK (move_type IN (0, 1, 3, 5)),
  CONSTRAINT chk_first_finder_events_hours_non_negative CHECK (hours_since_creation >= 0)
);

CREATE INDEX idx_first_finder_events_user
  ON stats.first_finder_events (finder_user_id, found_at DESC);

CREATE INDEX idx_first_finder_events_hours
  ON stats.first_finder_events (hours_since_creation)
  WHERE hours_since_creation <= 168;

COMMENT ON TABLE stats.first_finder_events IS 'First qualifying non-owner interaction per GeoKret; powers first-finder leaderboards';
COMMENT ON COLUMN stats.first_finder_events.gk_id IS 'GeoKret internal identifier; one row per GeoKret';
COMMENT ON COLUMN stats.first_finder_events.finder_user_id IS 'First non-owner authenticated user who interacted with the GeoKret';
COMMENT ON COLUMN stats.first_finder_events.move_id IS 'Move that established the first-finder event';
COMMENT ON COLUMN stats.first_finder_events.move_type IS 'Qualifying move type for the first-finder event';
COMMENT ON COLUMN stats.first_finder_events.hours_since_creation IS 'Whole hours between GeoKret creation and the first-finder move';
COMMENT ON COLUMN stats.first_finder_events.found_at IS 'Timestamp of the qualifying first-finder move';
COMMENT ON COLUMN stats.first_finder_events.gk_created_at IS 'GeoKret creation timestamp used for the 168-hour eligibility cutoff';
COMMENT ON COLUMN stats.first_finder_events.recorded_at IS 'Timestamp when the first-finder event row was inserted';

CREATE OR REPLACE FUNCTION geokrety.fn_current_geokret_country(
  p_geokrety_id BIGINT
)
RETURNS TEXT
LANGUAGE sql
STABLE
AS $$
  SELECT CASE
           WHEN m.country IS NULL OR BTRIM(m.country) = '' THEN NULL
           ELSE LOWER(BTRIM(m.country))
         END
  FROM geokrety.gk_geokrety g
  LEFT JOIN geokrety.gk_moves m
    ON m.id = g.last_position
  WHERE g.id = p_geokrety_id
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_love_country_at(
  p_geokrety_id BIGINT,
  p_loved_at TIMESTAMPTZ
)
RETURNS TEXT
LANGUAGE plpgsql
STABLE
AS $$
DECLARE
  v_country_code TEXT;
BEGIN
  IF p_geokrety_id IS NULL OR p_loved_at IS NULL THEN
    RETURN NULL;
  END IF;

  SELECT LOWER(h.country_code::TEXT)
    INTO v_country_code
  FROM stats.gk_country_history h
  WHERE h.geokrety_id = p_geokrety_id
    AND p_loved_at >= h.arrived_at
    AND (h.departed_at IS NULL OR p_loved_at < h.departed_at)
  ORDER BY h.arrived_at DESC
  LIMIT 1;

  IF v_country_code IS NOT NULL THEN
    RETURN v_country_code;
  END IF;

  RETURN geokrety.fn_current_geokret_country(p_geokrety_id);
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_gk_loves_daily_activity_date(
  p_activity_date DATE
)
RETURNS VOID
LANGUAGE plpgsql
AS $$
BEGIN
  IF p_activity_date IS NULL THEN
    RETURN;
  END IF;

  INSERT INTO stats.daily_activity (
    activity_date,
    loves_count
  )
  SELECT
    p_activity_date,
    COUNT(*)::BIGINT
  FROM geokrety.gk_loves l
  WHERE l.created_on_datetime >= p_activity_date::timestamp with time zone
    AND l.created_on_datetime < (p_activity_date + 1)::timestamp with time zone
  ON CONFLICT (activity_date) DO UPDATE SET
    loves_count = EXCLUDED.loves_count;

  IF FOUND THEN
    RETURN;
  END IF;

  UPDATE stats.daily_activity
     SET loves_count = 0
   WHERE activity_date = p_activity_date;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_gk_loves_country_daily_stats_bucket(
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

  v_country_code := LOWER(BTRIM(p_country_code));

  INSERT INTO stats.country_daily_stats (
    stats_date,
    country_code,
    loves_count
  )
  SELECT
    p_stats_date,
    v_country_code,
    COUNT(*)::BIGINT
  FROM geokrety.gk_loves l
  WHERE l.created_on_datetime >= p_stats_date::timestamp with time zone
    AND l.created_on_datetime < (p_stats_date + 1)::timestamp with time zone
    AND geokrety.fn_gk_love_country_at(l.geokret, l.created_on_datetime) = v_country_code
  GROUP BY 1, 2
  ON CONFLICT (stats_date, country_code) DO UPDATE SET
    loves_count = EXCLUDED.loves_count;

  IF FOUND THEN
    RETURN;
  END IF;

  UPDATE stats.country_daily_stats
     SET loves_count = 0
   WHERE stats_date = p_stats_date
     AND country_code = v_country_code
     AND (
       moves_count <> 0
       OR drops <> 0
       OR grabs <> 0
       OR comments <> 0
       OR sees <> 0
       OR archives <> 0
       OR dips <> 0
       OR unique_users <> 0
       OR unique_gks <> 0
       OR km_contributed <> 0
       OR points_contributed <> 0
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

CREATE OR REPLACE FUNCTION geokrety.fn_record_gk_milestone_event(
  p_gk_id INT,
  p_event_type TEXT,
  p_event_value NUMERIC,
  p_occurred_at TIMESTAMPTZ,
  p_move_id BIGINT,
  p_actor_user_id BIGINT
)
RETURNS VOID
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  INSERT INTO stats.gk_milestone_events (
    gk_id,
    event_type,
    event_value,
    additional_data,
    occurred_at
  )
  VALUES (
    p_gk_id,
    p_event_type,
    p_event_value,
    jsonb_strip_nulls(jsonb_build_object(
      'move_id', p_move_id,
      'actor_user_id', p_actor_user_id
    )),
    p_occurred_at
  )
  ON CONFLICT (gk_id, event_type) DO NOTHING;
END;
$$;

CREATE OR REPLACE FUNCTION stats.fn_detect_first_finder(
  p_gk_id BIGINT,
  p_move_id BIGINT,
  p_finder_user_id BIGINT,
  p_move_type SMALLINT,
  p_found_at TIMESTAMPTZ
)
RETURNS BOOLEAN
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_owner_user_id BIGINT;
  v_gk_created_at TIMESTAMPTZ;
  v_hours_since_creation SMALLINT;
  v_inserted_rows BIGINT := 0;
BEGIN
  IF p_finder_user_id IS NULL OR p_move_type NOT IN (0, 1, 3, 5) THEN
    RETURN FALSE;
  END IF;

  SELECT owner, created_on_datetime
    INTO v_owner_user_id, v_gk_created_at
  FROM geokrety.gk_geokrety
  WHERE id = p_gk_id;

  IF NOT FOUND THEN
    RETURN FALSE;
  END IF;

  IF v_owner_user_id IS NOT NULL AND p_finder_user_id = v_owner_user_id THEN
    RETURN FALSE;
  END IF;

  IF p_found_at < v_gk_created_at OR p_found_at > v_gk_created_at + INTERVAL '168 hours' THEN
    RETURN FALSE;
  END IF;

  v_hours_since_creation := FLOOR(EXTRACT(EPOCH FROM (p_found_at - v_gk_created_at)) / 3600)::SMALLINT;

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
    p_gk_id,
    p_finder_user_id,
    p_move_id,
    p_move_type,
    v_hours_since_creation,
    p_found_at,
    v_gk_created_at
  )
  ON CONFLICT (gk_id) DO NOTHING;

  GET DIAGNOSTICS v_inserted_rows = ROW_COUNT;

  IF v_inserted_rows > 0 THEN
    PERFORM geokrety.fn_record_gk_milestone_event(
      p_gk_id::INT,
      'first_find',
      v_hours_since_creation,
      p_found_at,
      p_move_id,
      p_finder_user_id
    );
  END IF;

  RETURN v_inserted_rows > 0;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_loves_activity()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_old_shard INT;
  v_new_shard INT;
  v_old_date DATE;
  v_new_date DATE;
  v_old_country TEXT;
  v_new_country TEXT;
BEGIN
  v_old_shard := CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN (OLD.id % 16) ELSE NULL END;
  v_new_shard := CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN (NEW.id % 16) ELSE NULL END;
  v_old_date := CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN OLD.created_on_datetime::date ELSE NULL END;
  v_new_date := CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN NEW.created_on_datetime::date ELSE NULL END;
  v_old_country := CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN geokrety.fn_gk_love_country_at(OLD.geokret, OLD.created_on_datetime) ELSE NULL END;
  v_new_country := CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN geokrety.fn_gk_love_country_at(NEW.geokret, NEW.created_on_datetime) ELSE NULL END;

  IF TG_OP = 'INSERT' THEN
    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_loves', v_new_shard, 1)
    ON CONFLICT (entity, shard) DO UPDATE SET
      cnt = stats.entity_counters_shard.cnt + 1;
  ELSIF TG_OP = 'DELETE' THEN
    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_loves', v_old_shard, 0)
    ON CONFLICT (entity, shard) DO UPDATE SET
      cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);
  ELSIF OLD.id IS DISTINCT FROM NEW.id THEN
    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_loves', v_old_shard, 0)
    ON CONFLICT (entity, shard) DO UPDATE SET
      cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);

    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_loves', v_new_shard, 1)
    ON CONFLICT (entity, shard) DO UPDATE SET
      cnt = stats.entity_counters_shard.cnt + 1;
  END IF;

  IF TG_OP IN ('UPDATE', 'DELETE') THEN
    PERFORM geokrety.fn_refresh_gk_loves_daily_activity_date(v_old_date);

    IF v_old_country IS NOT NULL THEN
      PERFORM geokrety.fn_refresh_gk_loves_country_daily_stats_bucket(v_old_date, v_old_country);
    END IF;
  END IF;

  IF TG_OP IN ('INSERT', 'UPDATE') THEN
    PERFORM geokrety.fn_refresh_gk_loves_daily_activity_date(v_new_date);

    IF v_new_country IS NOT NULL THEN
      PERFORM geokrety.fn_refresh_gk_loves_country_daily_stats_bucket(v_new_date, v_new_country);
    END IF;
  END IF;

  RETURN COALESCE(NEW, OLD);
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_emit_points_event()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  INSERT INTO notify_queues.geokrety_changes (
    channel,
    action,
    payload
  )
  VALUES (
    'points-awarder',
    'gk_move_created',
    NEW.id
  );

  RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_milestones()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_total_km NUMERIC := 0;
  v_previous_km NUMERIC := 0;
  v_related_user_count BIGINT := 0;
  v_previous_related_user_count BIGINT := 0;
BEGIN
  IF NEW.move_type IN (0, 1, 3, 5) THEN
    SELECT COALESCE(SUM(COALESCE(km_distance, 0)), 0)::NUMERIC
      INTO v_total_km
    FROM geokrety.gk_moves
    WHERE geokret = NEW.geokret
      AND move_type IN (0, 1, 3, 5);

    v_previous_km := v_total_km - COALESCE(NEW.km_distance, 0);

    IF v_previous_km < 100 AND v_total_km >= 100 THEN
      PERFORM geokrety.fn_record_gk_milestone_event(NEW.geokret::INT, 'km_100', 100, NEW.moved_on_datetime, NEW.id, NEW.author);
    END IF;

    IF v_previous_km < 1000 AND v_total_km >= 1000 THEN
      PERFORM geokrety.fn_record_gk_milestone_event(NEW.geokret::INT, 'km_1000', 1000, NEW.moved_on_datetime, NEW.id, NEW.author);
    END IF;

    IF v_previous_km < 10000 AND v_total_km >= 10000 THEN
      PERFORM geokrety.fn_record_gk_milestone_event(NEW.geokret::INT, 'km_10000', 10000, NEW.moved_on_datetime, NEW.id, NEW.author);
    END IF;

    IF NEW.author IS NOT NULL THEN
      SELECT COUNT(DISTINCT author)::BIGINT
        INTO v_related_user_count
      FROM geokrety.gk_moves
      WHERE geokret = NEW.geokret
        AND author IS NOT NULL
        AND move_type IN (0, 1, 3, 5);

      SELECT COUNT(DISTINCT author)::BIGINT
        INTO v_previous_related_user_count
      FROM geokrety.gk_moves
      WHERE geokret = NEW.geokret
        AND author IS NOT NULL
        AND move_type IN (0, 1, 3, 5)
        AND id <> NEW.id;

      IF v_previous_related_user_count < 10 AND v_related_user_count >= 10 THEN
        PERFORM geokrety.fn_record_gk_milestone_event(NEW.geokret::INT, 'users_10', 10, NEW.moved_on_datetime, NEW.id, NEW.author);
      END IF;

      IF v_previous_related_user_count < 50 AND v_related_user_count >= 50 THEN
        PERFORM geokrety.fn_record_gk_milestone_event(NEW.geokret::INT, 'users_50', 50, NEW.moved_on_datetime, NEW.id, NEW.author);
      END IF;

      IF v_previous_related_user_count < 100 AND v_related_user_count >= 100 THEN
        PERFORM geokrety.fn_record_gk_milestone_event(NEW.geokret::INT, 'users_100', 100, NEW.moved_on_datetime, NEW.id, NEW.author);
      END IF;
    END IF;
  END IF;

  RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_first_finder()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  PERFORM stats.fn_detect_first_finder(
    NEW.geokret,
    NEW.id,
    NEW.author,
    NEW.move_type,
    NEW.moved_on_datetime
  );

  RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION stats.fn_snapshot_hourly_activity()
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

CREATE OR REPLACE FUNCTION stats.fn_snapshot_country_pair_flows()
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

DROP TRIGGER IF EXISTS tr_gk_loves_activity ON geokrety.gk_loves;
CREATE TRIGGER tr_gk_loves_activity
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_loves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_loves_activity();

DROP TRIGGER IF EXISTS tr_gk_moves_emit_points_event ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_emit_points_event
  AFTER INSERT ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_emit_points_event();

DROP TRIGGER IF EXISTS tr_gk_moves_after_milestones ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_milestones
  AFTER INSERT ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_milestones();

DROP TRIGGER IF EXISTS tr_gk_moves_after_first_finder ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_first_finder
  AFTER INSERT ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_first_finder();

INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
SELECT
  'gk_loves',
  shards.shard,
  COALESCE(love_totals.cnt, 0) AS cnt
FROM generate_series(0, 15) AS shards(shard)
LEFT JOIN (
  SELECT
    (id % 16) AS shard,
    COUNT(*)::BIGINT AS cnt
  FROM geokrety.gk_loves
  GROUP BY (id % 16)
) AS love_totals
  ON love_totals.shard = shards.shard
ON CONFLICT (entity, shard) DO UPDATE SET
  cnt = EXCLUDED.cnt;

UPDATE stats.daily_activity
   SET loves_count = 0
 WHERE loves_count <> 0;

INSERT INTO stats.daily_activity (
  activity_date,
  loves_count
)
SELECT
  l.created_on_datetime::date,
  COUNT(*)::BIGINT
FROM geokrety.gk_loves l
GROUP BY l.created_on_datetime::date
ON CONFLICT (activity_date) DO UPDATE SET
  loves_count = EXCLUDED.loves_count;

UPDATE stats.country_daily_stats
   SET loves_count = 0
 WHERE loves_count <> 0;

WITH seeded_loves AS (
  SELECT
    l.created_on_datetime::date AS stats_date,
    geokrety.fn_gk_love_country_at(l.geokret, l.created_on_datetime) AS country_code,
    COUNT(*)::BIGINT AS loves_count
  FROM geokrety.gk_loves l
  GROUP BY l.created_on_datetime::date, geokrety.fn_gk_love_country_at(l.geokret, l.created_on_datetime)
)
INSERT INTO stats.country_daily_stats (
  stats_date,
  country_code,
  loves_count
)
SELECT
  stats_date,
  country_code,
  loves_count
FROM seeded_loves
WHERE country_code IS NOT NULL
ON CONFLICT (stats_date, country_code) DO UPDATE SET
  loves_count = EXCLUDED.loves_count;

CREATE INDEX IF NOT EXISTS idx_hourly_activity_date_desc
  ON stats.hourly_activity (activity_date DESC);

CREATE INDEX IF NOT EXISTS idx_country_pair_flows_month_desc
  ON stats.country_pair_flows (year_month DESC);

CREATE INDEX IF NOT EXISTS idx_country_pair_flows_from
  ON stats.country_pair_flows (from_country, year_month DESC);

CREATE INDEX IF NOT EXISTS idx_country_pair_flows_to
  ON stats.country_pair_flows (to_country, year_month DESC);
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP TRIGGER IF EXISTS tr_gk_moves_after_first_finder ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_moves_after_milestones ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_moves_emit_points_event ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_loves_activity ON geokrety.gk_loves;

DROP INDEX IF EXISTS stats.idx_country_pair_flows_to;
DROP INDEX IF EXISTS stats.idx_country_pair_flows_from;
DROP INDEX IF EXISTS stats.idx_country_pair_flows_month_desc;
DROP INDEX IF EXISTS stats.idx_hourly_activity_date_desc;

DROP FUNCTION IF EXISTS stats.fn_snapshot_country_pair_flows();
DROP FUNCTION IF EXISTS stats.fn_snapshot_hourly_activity();
DROP FUNCTION IF EXISTS geokrety.fn_gk_moves_first_finder();
DROP FUNCTION IF EXISTS geokrety.fn_gk_moves_milestones();
DROP FUNCTION IF EXISTS geokrety.fn_gk_moves_emit_points_event();
DROP FUNCTION IF EXISTS geokrety.fn_gk_loves_activity();
DROP FUNCTION IF EXISTS stats.fn_detect_first_finder(bigint, bigint, bigint, smallint, timestamp with time zone);
DROP FUNCTION IF EXISTS geokrety.fn_record_gk_milestone_event(integer, text, numeric, timestamp with time zone, bigint, bigint);
DROP FUNCTION IF EXISTS geokrety.fn_refresh_gk_loves_country_daily_stats_bucket(date, text);
DROP FUNCTION IF EXISTS geokrety.fn_refresh_gk_loves_daily_activity_date(date);
DROP FUNCTION IF EXISTS geokrety.fn_gk_love_country_at(bigint, timestamp with time zone);
DROP FUNCTION IF EXISTS geokrety.fn_current_geokret_country(bigint);

DELETE FROM stats.entity_counters_shard
WHERE entity = 'gk_loves';

UPDATE stats.daily_activity
   SET loves_count = 0
 WHERE loves_count <> 0;

UPDATE stats.country_daily_stats
   SET loves_count = 0
 WHERE loves_count <> 0;

DROP TABLE IF EXISTS stats.first_finder_events;
DROP TABLE IF EXISTS stats.gk_milestone_events;
DROP TABLE IF EXISTS stats.country_pair_flows;
DROP TABLE IF EXISTS stats.hourly_activity;
SQL
        );
    }
}
