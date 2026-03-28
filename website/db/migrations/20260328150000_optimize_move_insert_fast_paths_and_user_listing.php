<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OptimizeMoveInsertFastPathsAndUserListing extends AbstractMigration {
    public function up(): void {
        $this->execute('COMMIT;');
        $this->execute(
            <<<'SQL'
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_gk_users_joined_listing
ON geokrety.gk_users (joined_on_datetime DESC, id DESC)
INCLUDE (username, home_country, avatar);
SQL,
        );
        $this->execute('BEGIN;');

        $this->execute(
            <<<'SQL'
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
      WITH inserted AS (
        SELECT
          nm.geokret,
          nm.id,
          nm.moved_on_datetime
        FROM new_moves nm
        WHERE nm.geokret IS NOT NULL
      ),
      per_geokret AS (
        SELECT
          inserted.geokret,
          COUNT(*)::BIGINT AS inserted_rows,
          BOOL_OR(
            EXISTS (
              SELECT 1
              FROM geokrety.gk_moves later
              WHERE later.geokret = inserted.geokret
                AND later.id <> inserted.id
                AND (
                  later.moved_on_datetime > inserted.moved_on_datetime
                  OR (
                    later.moved_on_datetime = inserted.moved_on_datetime
                    AND later.id > inserted.id
                  )
                )
            )
          ) AS needs_refresh
        FROM inserted
        GROUP BY inserted.geokret
      )
      SELECT geokret
      FROM per_geokret
      WHERE inserted_rows > 1
         OR needs_refresh
    )
  );

  RETURN NULL;
END;
$BODY$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_country_history()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_active_history RECORD;
  v_new_country CHAR(2);
BEGIN
  IF TG_OP = 'INSERT' THEN
    IF NEW.geokret IS NULL
       OR NEW.move_type NOT IN (0, 1, 3, 5)
       OR NEW.country IS NULL THEN
      RETURN NEW;
    END IF;

    IF EXISTS (
      SELECT 1
      FROM geokrety.gk_moves m
      WHERE m.geokret = NEW.geokret
        AND m.id <> NEW.id
        AND (
          m.moved_on_datetime > NEW.moved_on_datetime
          OR (m.moved_on_datetime = NEW.moved_on_datetime AND m.id > NEW.id)
        )
    ) THEN
      PERFORM geokrety.fn_refresh_gk_country_history(NEW.geokret);
      RETURN NEW;
    END IF;

    v_new_country := geokrety.fn_normalize_country_code(NEW.country);

    SELECT
      history.country_code,
      history.arrived_at,
      history.move_id
      INTO v_active_history
    FROM stats.gk_country_history history
    WHERE history.geokrety_id = NEW.geokret
      AND history.departed_at IS NULL
    ORDER BY history.arrived_at DESC, history.move_id DESC
    LIMIT 1
    FOR UPDATE;

    IF NOT FOUND THEN
      INSERT INTO stats.gk_country_history (
        geokrety_id,
        country_code,
        arrived_at,
        departed_at,
        move_id
      )
      VALUES (
        NEW.geokret,
        v_new_country,
        NEW.moved_on_datetime,
        NULL,
        NEW.id
      );

      RETURN NEW;
    END IF;

    IF v_active_history.country_code = v_new_country THEN
      RETURN NEW;
    END IF;

    UPDATE stats.gk_country_history
    SET departed_at = NEW.moved_on_datetime
    WHERE geokrety_id = NEW.geokret
      AND country_code = v_active_history.country_code
      AND arrived_at = v_active_history.arrived_at
      AND move_id = v_active_history.move_id
      AND departed_at IS NULL;

    INSERT INTO stats.gk_country_history (
      geokrety_id,
      country_code,
      arrived_at,
      departed_at,
      move_id
    )
    VALUES (
      NEW.geokret,
      v_new_country,
      NEW.moved_on_datetime,
      NULL,
      NEW.id
    );

    RETURN NEW;
  END IF;

  IF TG_OP = 'DELETE' THEN
    PERFORM geokrety.fn_refresh_gk_country_history(OLD.geokret);
    RETURN OLD;
  END IF;

  IF OLD.geokret IS DISTINCT FROM NEW.geokret THEN
    PERFORM geokrety.fn_refresh_gk_country_history(OLD.geokret);
  END IF;

  PERFORM geokrety.fn_refresh_gk_country_history(NEW.geokret);

  RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_waypoint_cache()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_waypoint_id BIGINT;
  v_waypoint_code TEXT;
BEGIN
  IF TG_OP = 'INSERT'
     AND NEW.move_type <> 2
     AND NEW.waypoint IS NOT NULL
     AND BTRIM(NEW.waypoint) <> '' THEN
    v_waypoint_code := UPPER(BTRIM(NEW.waypoint));

    INSERT INTO stats.waypoints (
      waypoint_code,
      source,
      lat,
      lon,
      country,
      first_seen_at
    )
    VALUES (
      v_waypoint_code,
      'UK',
      CASE
        WHEN NEW.position IS NULL THEN NULL
        ELSE public.ST_Y(NEW.position::public.geometry)::DOUBLE PRECISION
      END,
      CASE
        WHEN NEW.position IS NULL THEN NULL
        ELSE public.ST_X(NEW.position::public.geometry)::DOUBLE PRECISION
      END,
      CASE
        WHEN NEW.country IS NULL OR BTRIM(NEW.country) = '' THEN NULL
        ELSE UPPER(BTRIM(NEW.country))::CHAR(2)
      END,
      NEW.moved_on_datetime
    )
    ON CONFLICT (waypoint_code) DO UPDATE SET
      lat = COALESCE(stats.waypoints.lat, EXCLUDED.lat),
      lon = COALESCE(stats.waypoints.lon, EXCLUDED.lon),
      country = COALESCE(stats.waypoints.country, EXCLUDED.country),
      first_seen_at = LEAST(stats.waypoints.first_seen_at, EXCLUDED.first_seen_at)
    RETURNING id INTO v_waypoint_id;

    INSERT INTO stats.gk_cache_visits (
      gk_id,
      waypoint_id,
      visit_count,
      first_visited_at,
      last_visited_at
    )
    VALUES (
      NEW.geokret,
      v_waypoint_id,
      1,
      NEW.moved_on_datetime,
      NEW.moved_on_datetime
    )
    ON CONFLICT (gk_id, waypoint_id) DO UPDATE SET
      visit_count = stats.gk_cache_visits.visit_count + 1,
      first_visited_at = LEAST(stats.gk_cache_visits.first_visited_at, EXCLUDED.first_visited_at),
      last_visited_at = GREATEST(stats.gk_cache_visits.last_visited_at, EXCLUDED.last_visited_at);

    IF NEW.author IS NOT NULL THEN
      INSERT INTO stats.user_cache_visits (
        user_id,
        waypoint_id,
        visit_count,
        first_visited_at,
        last_visited_at
      )
      VALUES (
        NEW.author,
        v_waypoint_id,
        1,
        NEW.moved_on_datetime,
        NEW.moved_on_datetime
      )
      ON CONFLICT (user_id, waypoint_id) DO UPDATE SET
        visit_count = stats.user_cache_visits.visit_count + 1,
        first_visited_at = LEAST(stats.user_cache_visits.first_visited_at, EXCLUDED.first_visited_at),
        last_visited_at = GREATEST(stats.user_cache_visits.last_visited_at, EXCLUDED.last_visited_at);
    END IF;

    RETURN NEW;
  END IF;

  IF TG_OP IN ('UPDATE', 'DELETE')
     AND OLD.move_type <> 2
     AND OLD.waypoint IS NOT NULL
     AND BTRIM(OLD.waypoint) <> '' THEN
    v_waypoint_code := UPPER(BTRIM(OLD.waypoint));

    SELECT id
      INTO v_waypoint_id
    FROM stats.waypoints
    WHERE waypoint_code = v_waypoint_code;

    IF v_waypoint_id IS NOT NULL THEN
      DELETE FROM stats.gk_cache_visits
      WHERE gk_id = OLD.geokret
        AND waypoint_id = v_waypoint_id;

      INSERT INTO stats.gk_cache_visits (
        gk_id,
        waypoint_id,
        visit_count,
        first_visited_at,
        last_visited_at
      )
      SELECT
        OLD.geokret,
        v_waypoint_id,
        COUNT(*)::BIGINT,
        MIN(m.moved_on_datetime),
        MAX(m.moved_on_datetime)
      FROM geokrety.gk_moves m
      WHERE m.geokret = OLD.geokret
        AND m.move_type <> 2
        AND m.waypoint IS NOT NULL
        AND BTRIM(m.waypoint) <> ''
        AND UPPER(BTRIM(m.waypoint)) = v_waypoint_code
      GROUP BY OLD.geokret;

      IF OLD.author IS NOT NULL THEN
        DELETE FROM stats.user_cache_visits
        WHERE user_id = OLD.author
          AND waypoint_id = v_waypoint_id;

        INSERT INTO stats.user_cache_visits (
          user_id,
          waypoint_id,
          visit_count,
          first_visited_at,
          last_visited_at
        )
        SELECT
          OLD.author,
          v_waypoint_id,
          COUNT(*)::BIGINT,
          MIN(m.moved_on_datetime),
          MAX(m.moved_on_datetime)
        FROM geokrety.gk_moves m
        WHERE m.author = OLD.author
          AND m.move_type <> 2
          AND m.waypoint IS NOT NULL
          AND BTRIM(m.waypoint) <> ''
          AND UPPER(BTRIM(m.waypoint)) = v_waypoint_code
        GROUP BY OLD.author;
      END IF;
    END IF;
  END IF;

  IF TG_OP = 'UPDATE'
     AND NEW.move_type <> 2
     AND NEW.waypoint IS NOT NULL
     AND BTRIM(NEW.waypoint) <> '' THEN
    v_waypoint_code := UPPER(BTRIM(NEW.waypoint));

    INSERT INTO stats.waypoints (
      waypoint_code,
      source,
      lat,
      lon,
      country,
      first_seen_at
    )
    VALUES (
      v_waypoint_code,
      'UK',
      CASE
        WHEN NEW.position IS NULL THEN NULL
        ELSE public.ST_Y(NEW.position::public.geometry)::DOUBLE PRECISION
      END,
      CASE
        WHEN NEW.position IS NULL THEN NULL
        ELSE public.ST_X(NEW.position::public.geometry)::DOUBLE PRECISION
      END,
      CASE
        WHEN NEW.country IS NULL OR BTRIM(NEW.country) = '' THEN NULL
        ELSE UPPER(BTRIM(NEW.country))::CHAR(2)
      END,
      NEW.moved_on_datetime
    )
    ON CONFLICT (waypoint_code) DO UPDATE SET
      lat = COALESCE(stats.waypoints.lat, EXCLUDED.lat),
      lon = COALESCE(stats.waypoints.lon, EXCLUDED.lon),
      country = COALESCE(stats.waypoints.country, EXCLUDED.country),
      first_seen_at = LEAST(stats.waypoints.first_seen_at, EXCLUDED.first_seen_at)
    RETURNING id INTO v_waypoint_id;

    DELETE FROM stats.gk_cache_visits
    WHERE gk_id = NEW.geokret
      AND waypoint_id = v_waypoint_id;

    INSERT INTO stats.gk_cache_visits (
      gk_id,
      waypoint_id,
      visit_count,
      first_visited_at,
      last_visited_at
    )
    SELECT
      NEW.geokret,
      v_waypoint_id,
      COUNT(*)::BIGINT,
      MIN(m.moved_on_datetime),
      MAX(m.moved_on_datetime)
    FROM geokrety.gk_moves m
    WHERE m.geokret = NEW.geokret
      AND m.move_type <> 2
      AND m.waypoint IS NOT NULL
      AND BTRIM(m.waypoint) <> ''
      AND UPPER(BTRIM(m.waypoint)) = v_waypoint_code
    GROUP BY NEW.geokret;

    IF NEW.author IS NOT NULL THEN
      DELETE FROM stats.user_cache_visits
      WHERE user_id = NEW.author
        AND waypoint_id = v_waypoint_id;

      INSERT INTO stats.user_cache_visits (
        user_id,
        waypoint_id,
        visit_count,
        first_visited_at,
        last_visited_at
      )
      SELECT
        NEW.author,
        v_waypoint_id,
        COUNT(*)::BIGINT,
        MIN(m.moved_on_datetime),
        MAX(m.moved_on_datetime)
      FROM geokrety.gk_moves m
      WHERE m.author = NEW.author
        AND m.move_type <> 2
        AND m.waypoint IS NOT NULL
        AND BTRIM(m.waypoint) <> ''
        AND UPPER(BTRIM(m.waypoint)) = v_waypoint_code
      GROUP BY NEW.author;
    END IF;
  END IF;

  RETURN COALESCE(NEW, OLD);
END;
$$;
SQL,
        );
    }

    public function down(): void {
        $this->execute('COMMIT;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_users_joined_listing;');
        $this->execute('BEGIN;');

        $this->execute(
            <<<'SQL'
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

CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_waypoint_cache()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_waypoint_id BIGINT;
  v_waypoint_code TEXT;
BEGIN
  IF TG_OP IN ('UPDATE', 'DELETE')
     AND OLD.move_type <> 2
     AND OLD.waypoint IS NOT NULL
     AND BTRIM(OLD.waypoint) <> '' THEN
    v_waypoint_code := UPPER(BTRIM(OLD.waypoint));

    SELECT id
      INTO v_waypoint_id
    FROM stats.waypoints
    WHERE waypoint_code = v_waypoint_code;

    IF v_waypoint_id IS NOT NULL THEN
      DELETE FROM stats.gk_cache_visits
      WHERE gk_id = OLD.geokret
        AND waypoint_id = v_waypoint_id;

      INSERT INTO stats.gk_cache_visits (
        gk_id,
        waypoint_id,
        visit_count,
        first_visited_at,
        last_visited_at
      )
      SELECT
        OLD.geokret,
        v_waypoint_id,
        COUNT(*)::BIGINT,
        MIN(m.moved_on_datetime),
        MAX(m.moved_on_datetime)
      FROM geokrety.gk_moves m
      WHERE m.geokret = OLD.geokret
        AND m.move_type <> 2
        AND m.waypoint IS NOT NULL
        AND BTRIM(m.waypoint) <> ''
        AND UPPER(BTRIM(m.waypoint)) = v_waypoint_code
      GROUP BY OLD.geokret;

      IF OLD.author IS NOT NULL THEN
        DELETE FROM stats.user_cache_visits
        WHERE user_id = OLD.author
          AND waypoint_id = v_waypoint_id;

        INSERT INTO stats.user_cache_visits (
          user_id,
          waypoint_id,
          visit_count,
          first_visited_at,
          last_visited_at
        )
        SELECT
          OLD.author,
          v_waypoint_id,
          COUNT(*)::BIGINT,
          MIN(m.moved_on_datetime),
          MAX(m.moved_on_datetime)
        FROM geokrety.gk_moves m
        WHERE m.author = OLD.author
          AND m.move_type <> 2
          AND m.waypoint IS NOT NULL
          AND BTRIM(m.waypoint) <> ''
          AND UPPER(BTRIM(m.waypoint)) = v_waypoint_code
        GROUP BY OLD.author;
      END IF;
    END IF;
  END IF;

  IF TG_OP IN ('INSERT', 'UPDATE')
     AND NEW.move_type <> 2
     AND NEW.waypoint IS NOT NULL
     AND BTRIM(NEW.waypoint) <> '' THEN
    v_waypoint_code := UPPER(BTRIM(NEW.waypoint));

    INSERT INTO stats.waypoints (
      waypoint_code,
      source,
      lat,
      lon,
      country,
      first_seen_at
    )
    VALUES (
      v_waypoint_code,
      'UK',
      CASE
        WHEN NEW.position IS NULL THEN NULL
        ELSE public.ST_Y(NEW.position::public.geometry)::DOUBLE PRECISION
      END,
      CASE
        WHEN NEW.position IS NULL THEN NULL
        ELSE public.ST_X(NEW.position::public.geometry)::DOUBLE PRECISION
      END,
      CASE
        WHEN NEW.country IS NULL OR BTRIM(NEW.country) = '' THEN NULL
        ELSE UPPER(BTRIM(NEW.country))::CHAR(2)
      END,
      NEW.moved_on_datetime
    )
    ON CONFLICT (waypoint_code) DO UPDATE SET
      lat = COALESCE(stats.waypoints.lat, EXCLUDED.lat),
      lon = COALESCE(stats.waypoints.lon, EXCLUDED.lon),
      country = COALESCE(stats.waypoints.country, EXCLUDED.country),
      first_seen_at = LEAST(stats.waypoints.first_seen_at, EXCLUDED.first_seen_at)
    RETURNING id INTO v_waypoint_id;

    DELETE FROM stats.gk_cache_visits
    WHERE gk_id = NEW.geokret
      AND waypoint_id = v_waypoint_id;

    INSERT INTO stats.gk_cache_visits (
      gk_id,
      waypoint_id,
      visit_count,
      first_visited_at,
      last_visited_at
    )
    SELECT
      NEW.geokret,
      v_waypoint_id,
      COUNT(*)::BIGINT,
      MIN(m.moved_on_datetime),
      MAX(m.moved_on_datetime)
    FROM geokrety.gk_moves m
    WHERE m.geokret = NEW.geokret
      AND m.move_type <> 2
      AND m.waypoint IS NOT NULL
      AND BTRIM(m.waypoint) <> ''
      AND UPPER(BTRIM(m.waypoint)) = v_waypoint_code
    GROUP BY NEW.geokret;

    IF NEW.author IS NOT NULL THEN
      DELETE FROM stats.user_cache_visits
      WHERE user_id = NEW.author
        AND waypoint_id = v_waypoint_id;

      INSERT INTO stats.user_cache_visits (
        user_id,
        waypoint_id,
        visit_count,
        first_visited_at,
        last_visited_at
      )
      SELECT
        NEW.author,
        v_waypoint_id,
        COUNT(*)::BIGINT,
        MIN(m.moved_on_datetime),
        MAX(m.moved_on_datetime)
      FROM geokrety.gk_moves m
      WHERE m.author = NEW.author
        AND m.move_type <> 2
        AND m.waypoint IS NOT NULL
        AND BTRIM(m.waypoint) <> ''
        AND UPPER(BTRIM(m.waypoint)) = v_waypoint_code
      GROUP BY NEW.author;
    END IF;
  END IF;

  RETURN COALESCE(NEW, OLD);
END;
$$;
SQL,
        );
    }
}
