<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class HardenFirstFinderLiveReconciliation extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION stats.fn_reconcile_first_finder_event(
  p_gk_id BIGINT
)
RETURNS BOOLEAN
LANGUAGE plpgsql
SECURITY DEFINER
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

COMMENT ON FUNCTION stats.fn_reconcile_first_finder_event(BIGINT) IS 'Recomputes the canonical first_finder_events row and first_find milestone for one GeoKret after live source changes.';

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

CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_first_finder()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_first_gk_id BIGINT;
  v_second_gk_id BIGINT;
BEGIN
  IF TG_OP = 'INSERT' THEN
    PERFORM stats.fn_detect_first_finder(
      NEW.geokret,
      NEW.id,
      NEW.author,
      NEW.move_type,
      NEW.moved_on_datetime
    );

    RETURN NEW;
  END IF;

  IF TG_OP = 'DELETE' THEN
    PERFORM stats.fn_reconcile_first_finder_event(OLD.geokret);
    RETURN OLD;
  END IF;

  IF OLD.geokret IS DISTINCT FROM NEW.geokret THEN
    v_first_gk_id := LEAST(OLD.geokret, NEW.geokret);
    v_second_gk_id := GREATEST(OLD.geokret, NEW.geokret);

    PERFORM stats.fn_reconcile_first_finder_event(v_first_gk_id);
    PERFORM stats.fn_reconcile_first_finder_event(v_second_gk_id);

    RETURN NEW;
  END IF;

  PERFORM stats.fn_reconcile_first_finder_event(NEW.geokret);

  RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_geokrety_first_finder()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  PERFORM stats.fn_reconcile_first_finder_event(COALESCE(NEW.id, OLD.id));

  RETURN COALESCE(NEW, OLD);
END;
$$;

DROP TRIGGER IF EXISTS tr_gk_moves_after_first_finder ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_first_finder
  AFTER INSERT OR DELETE OR UPDATE OF geokret, author, move_type, moved_on_datetime ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_first_finder();

DROP TRIGGER IF EXISTS tr_gk_geokrety_after_first_finder ON geokrety.gk_geokrety;
CREATE TRIGGER tr_gk_geokrety_after_first_finder
  AFTER UPDATE OF owner, created_on_datetime OR DELETE ON geokrety.gk_geokrety
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_geokrety_first_finder();
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP TRIGGER IF EXISTS tr_gk_geokrety_after_first_finder ON geokrety.gk_geokrety;
DROP FUNCTION IF EXISTS geokrety.fn_gk_geokrety_first_finder();

DROP TRIGGER IF EXISTS tr_gk_moves_after_first_finder ON geokrety.gk_moves;

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

CREATE TRIGGER tr_gk_moves_after_first_finder
  AFTER INSERT ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_first_finder();

DROP FUNCTION IF EXISTS stats.fn_reconcile_first_finder_event(BIGINT);
SQL
        );
    }
}
