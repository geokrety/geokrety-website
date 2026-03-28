<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OptimizeLegacyDistanceTrigger extends AbstractMigration {
    public function up(): void {
        $this->execute(
            <<<'SQL'
CREATE OR REPLACE FUNCTION geokrety.fn_refresh_move_distance_row(
  p_move_id BIGINT
)
RETURNS VOID
LANGUAGE plpgsql
AS $$
BEGIN
  WITH target AS (
    SELECT
      m.id,
      m.geokret,
      m.moved_on_datetime,
      m.position,
      m.move_type
    FROM geokrety.gk_moves m
    WHERE m.id = p_move_id
  ),
  previous_counted_move AS (
    SELECT pm.position
    FROM target t
    JOIN geokrety.gk_moves pm
      ON pm.geokret = t.geokret
    WHERE pm.id <> t.id
      AND geokrety.move_type_count_kilometers(pm.move_type)
      AND (
        pm.moved_on_datetime < t.moved_on_datetime
        OR (pm.moved_on_datetime = t.moved_on_datetime AND pm.id < t.id)
      )
    ORDER BY pm.moved_on_datetime DESC, pm.id DESC
    LIMIT 1
  ),
  computed AS (
    SELECT
      t.id,
      CASE
        WHEN NOT geokrety.move_type_count_kilometers(t.move_type) THEN NULL::INTEGER
        ELSE COALESCE(
          ROUND(
            public.ST_Distance(
              t.position,
              (SELECT position FROM previous_counted_move),
              false
            ) / 1000.0
          ),
          0
        )::INTEGER
      END AS new_distance
    FROM target t
  )
  UPDATE geokrety.gk_moves g
     SET distance = c.new_distance
    FROM computed c
   WHERE g.id = c.id
     AND g.distance IS DISTINCT FROM c.new_distance;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_move_distance_window(
  p_geokret_id BIGINT,
  p_reference_moved_on TIMESTAMPTZ,
  p_reference_move_id BIGINT,
  p_include_current BOOLEAN DEFAULT true
)
RETURNS VOID
LANGUAGE plpgsql
AS $$
DECLARE
  v_next_move_id BIGINT;
BEGIN
  IF p_geokret_id IS NULL OR p_reference_moved_on IS NULL OR p_reference_move_id IS NULL THEN
    RETURN;
  END IF;

  IF p_include_current THEN
    PERFORM geokrety.fn_refresh_move_distance_row(p_reference_move_id);
  END IF;

  SELECT m.id
    INTO v_next_move_id
  FROM geokrety.gk_moves m
  WHERE m.geokret = p_geokret_id
    AND geokrety.move_type_count_kilometers(m.move_type)
    AND (
      m.moved_on_datetime > p_reference_moved_on
      OR (m.moved_on_datetime = p_reference_moved_on AND m.id > p_reference_move_id)
    )
  ORDER BY m.moved_on_datetime, m.id
  LIMIT 1;

  IF v_next_move_id IS NOT NULL THEN
    PERFORM geokrety.fn_refresh_move_distance_row(v_next_move_id);
  END IF;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.update_next_move_distance(
  geokret_id BIGINT,
  move_id BIGINT,
  exclude_current BOOLEAN DEFAULT false
)
RETURNS SMALLINT
LANGUAGE plpgsql
AS $$
DECLARE
  v_reference_moved_on TIMESTAMPTZ;
BEGIN
  SELECT m.moved_on_datetime
    INTO v_reference_moved_on
  FROM geokrety.gk_moves m
  WHERE m.id = move_id;

  IF v_reference_moved_on IS NULL THEN
    RETURN NULL;
  END IF;

  PERFORM geokrety.fn_refresh_move_distance_window(
    geokret_id,
    v_reference_moved_on,
    move_id,
    NOT exclude_current
  );

  RETURN NULL;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.moves_distances_after()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
  IF TG_OP = 'DELETE' THEN
    PERFORM geokrety.fn_refresh_move_distance_window(
      OLD.geokret,
      OLD.moved_on_datetime,
      OLD.id,
      false
    );
    PERFORM geokrety.geokret_compute_total_distance(OLD.geokret);
    PERFORM geokrety.geokret_compute_total_places_visited(OLD.geokret);
    RETURN OLD;
  END IF;

  IF TG_OP = 'UPDATE'
     AND OLD.geokret IS NOT DISTINCT FROM NEW.geokret
     AND OLD.moved_on_datetime IS NOT DISTINCT FROM NEW.moved_on_datetime
     AND OLD.move_type IS NOT DISTINCT FROM NEW.move_type
     AND OLD.position IS NOT DISTINCT FROM NEW.position THEN
    RETURN NEW;
  END IF;

  IF TG_OP = 'UPDATE' THEN
    PERFORM geokrety.fn_refresh_move_distance_window(
      OLD.geokret,
      OLD.moved_on_datetime,
      OLD.id,
      false
    );

    IF OLD.geokret IS DISTINCT FROM NEW.geokret THEN
      PERFORM geokrety.geokret_compute_total_distance(OLD.geokret);
      PERFORM geokrety.geokret_compute_total_places_visited(OLD.geokret);
    END IF;
  END IF;

  PERFORM geokrety.fn_refresh_move_distance_window(
    NEW.geokret,
    NEW.moved_on_datetime,
    NEW.id,
    true
  );
  PERFORM geokrety.geokret_compute_total_distance(NEW.geokret);
  PERFORM geokrety.geokret_compute_total_places_visited(NEW.geokret);

  RETURN NEW;
END;
$$;
SQL,
        );
    }

    public function down(): void {
        $this->execute(
            <<<'SQL'
DROP FUNCTION IF EXISTS geokrety.fn_refresh_move_distance_window(BIGINT, TIMESTAMPTZ, BIGINT, BOOLEAN);
DROP FUNCTION IF EXISTS geokrety.fn_refresh_move_distance_row(BIGINT);

CREATE OR REPLACE FUNCTION geokrety.update_next_move_distance(geokret_id bigint, move_id bigint, exclude_current boolean DEFAULT false) RETURNS smallint
    LANGUAGE plpgsql
    AS $$DECLARE
updated_rows smallint;
BEGIN

UPDATE gk_moves
SET distance = NULL
WHERE geokret = geokret_id
AND NOT move_type_count_kilometers(move_type)
AND distance IS NOT NULL;

WITH cte AS (
	SELECT
		id, distance, moved_on_datetime,
		COALESCE(ROUND(public.ST_Distance(position, LAG(position, 1) OVER (ORDER BY moved_on_datetime ASC), false) / 1000), 0) AS new_distance
	FROM gk_moves
	WHERE geokret = geokret_id
	AND move_type_count_kilometers(move_type)
	ORDER BY moved_on_datetime DESC
)
UPDATE gk_moves
SET distance = cte.new_distance
FROM cte
WHERE gk_moves.id = cte.id;

RETURN NULL;
END;$$;

CREATE OR REPLACE FUNCTION geokrety.moves_distances_after() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

IF (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') THEN
	IF (OLD.geokret != NEW.geokret) THEN
    	PERFORM update_next_move_distance(OLD.geokret, OLD.id, true);
		PERFORM geokret_compute_total_places_visited(OLD.geokret);
		PERFORM geokret_compute_total_distance(OLD.geokret);
	END IF;
	PERFORM update_next_move_distance(NEW.geokret, NEW.id);
END IF;

IF (TG_OP = 'DELETE') THEN
	PERFORM geokret_compute_total_distance(OLD.geokret);
	PERFORM geokret_compute_total_places_visited(OLD.geokret);
	RETURN OLD;
END IF;

PERFORM geokret_compute_total_distance(NEW.geokret);
PERFORM geokret_compute_total_places_visited(NEW.geokret);

RETURN NEW;
END;
$$;
SQL,
        );
    }
}
