<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateWaypointRelationshipTriggers extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
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

DROP TRIGGER IF EXISTS tr_gk_moves_after_waypoint_visits ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_waypoint_visits
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_waypoint_cache();

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

DROP TRIGGER IF EXISTS tr_gk_moves_after_relations ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_relations
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_relations();
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP TRIGGER IF EXISTS tr_gk_moves_after_relations ON geokrety.gk_moves;
DROP FUNCTION IF EXISTS geokrety.fn_gk_moves_relations();
DROP TRIGGER IF EXISTS tr_gk_moves_after_waypoint_visits ON geokrety.gk_moves;
DROP FUNCTION IF EXISTS geokrety.fn_gk_moves_waypoint_cache();
SQL
        );
    }
}
