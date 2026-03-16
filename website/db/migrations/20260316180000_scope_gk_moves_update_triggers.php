<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ScopeGkMovesUpdateTriggers extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_daily_activity()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_old_date DATE;
  v_new_date DATE;
  v_activity_date DATE;
BEGIN
  IF TG_LEVEL = 'STATEMENT' THEN
    FOR v_activity_date IN
      SELECT DISTINCT activity_date
      FROM (
        SELECT o.moved_on_datetime::date AS activity_date
        FROM old_moves o
        JOIN new_moves n USING (id)
        WHERE o.moved_on_datetime IS DISTINCT FROM n.moved_on_datetime
           OR o.move_type IS DISTINCT FROM n.move_type
           OR o.km_distance IS DISTINCT FROM n.km_distance
           OR o.author IS DISTINCT FROM n.author

        UNION

        SELECT n.moved_on_datetime::date AS activity_date
        FROM old_moves o
        JOIN new_moves n USING (id)
        WHERE o.moved_on_datetime IS DISTINCT FROM n.moved_on_datetime
           OR o.move_type IS DISTINCT FROM n.move_type
           OR o.km_distance IS DISTINCT FROM n.km_distance
           OR o.author IS DISTINCT FROM n.author
      ) AS affected_dates
      WHERE activity_date IS NOT NULL
    LOOP
      PERFORM geokrety.fn_refresh_gk_moves_daily_activity_date(v_activity_date);
      PERFORM geokrety.fn_refresh_daily_active_users_date(v_activity_date);
    END LOOP;

    RETURN NULL;
  END IF;

  v_old_date := CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN OLD.moved_on_datetime::date ELSE NULL END;
  v_new_date := CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN NEW.moved_on_datetime::date ELSE NULL END;

  IF TG_OP = 'DELETE' THEN
    PERFORM geokrety.fn_refresh_gk_moves_daily_activity_date(v_old_date);
    PERFORM geokrety.fn_refresh_daily_active_users_date(v_old_date);
    RETURN NULL;
  END IF;

  IF TG_OP = 'INSERT' THEN
    PERFORM geokrety.fn_refresh_gk_moves_daily_activity_date(v_new_date);
    PERFORM geokrety.fn_refresh_daily_active_users_date(v_new_date);
    RETURN NULL;
  END IF;

  IF TG_OP = 'UPDATE'
     AND OLD.moved_on_datetime IS NOT DISTINCT FROM NEW.moved_on_datetime
     AND OLD.move_type IS NOT DISTINCT FROM NEW.move_type
     AND OLD.km_distance IS NOT DISTINCT FROM NEW.km_distance
     AND OLD.author IS NOT DISTINCT FROM NEW.author THEN
    RETURN NULL;
  END IF;

  PERFORM geokrety.fn_refresh_gk_moves_daily_activity_date(v_old_date);
  PERFORM geokrety.fn_refresh_daily_active_users_date(v_old_date);

  IF v_new_date IS DISTINCT FROM v_old_date THEN
    PERFORM geokrety.fn_refresh_gk_moves_daily_activity_date(v_new_date);
    PERFORM geokrety.fn_refresh_daily_active_users_date(v_new_date);
  END IF;

  RETURN NULL;
END;
$$;

DROP TRIGGER IF EXISTS tr_gk_moves_after_sharded_counters ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_sharded_counters
  AFTER INSERT OR DELETE OR UPDATE OF id, move_type ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_sharded_counter();

DROP TRIGGER IF EXISTS tr_gk_moves_after_country_rollups ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_country_rollups
  AFTER INSERT OR DELETE OR UPDATE OF geokret, author, country, moved_on_datetime, move_type ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_country_rollups();

DROP TRIGGER IF EXISTS tr_gk_moves_after_country_history ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_country_history
  AFTER INSERT OR DELETE OR UPDATE OF geokret, country, moved_on_datetime, move_type ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_country_history();

DROP TRIGGER IF EXISTS tr_gk_moves_after_waypoint_visits ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_waypoint_visits
  AFTER INSERT OR DELETE OR UPDATE OF geokret, author, waypoint, moved_on_datetime, move_type, position, country ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_waypoint_cache();

DROP TRIGGER IF EXISTS tr_gk_moves_after_relations ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_relations
  AFTER INSERT OR DELETE OR UPDATE OF geokret, author, moved_on_datetime, move_type ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_relations();

DROP TRIGGER IF EXISTS tr_gk_moves_after_daily_activity ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_moves_after_daily_activity_insert ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_moves_after_daily_activity_delete ON geokrety.gk_moves;

CREATE TRIGGER tr_gk_moves_after_daily_activity_insert
  AFTER INSERT ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_daily_activity();

CREATE TRIGGER tr_gk_moves_after_daily_activity
  AFTER UPDATE ON geokrety.gk_moves
  REFERENCING OLD TABLE AS old_moves NEW TABLE AS new_moves
  FOR EACH STATEMENT EXECUTE FUNCTION geokrety.fn_gk_moves_daily_activity();

CREATE TRIGGER tr_gk_moves_after_daily_activity_delete
  AFTER DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_daily_activity();
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_daily_activity()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_old_date DATE;
  v_new_date DATE;
BEGIN
  v_old_date := CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN OLD.moved_on_datetime::date ELSE NULL END;
  v_new_date := CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN NEW.moved_on_datetime::date ELSE NULL END;

  IF TG_OP = 'UPDATE'
     AND OLD.moved_on_datetime IS NOT DISTINCT FROM NEW.moved_on_datetime
     AND OLD.move_type IS NOT DISTINCT FROM NEW.move_type
     AND OLD.km_distance IS NOT DISTINCT FROM NEW.km_distance
     AND OLD.author IS NOT DISTINCT FROM NEW.author THEN
    RETURN NULL;
  END IF;

  IF TG_OP = 'DELETE' THEN
    PERFORM geokrety.fn_refresh_gk_moves_daily_activity_date(v_old_date);
    PERFORM geokrety.fn_refresh_daily_active_users_date(v_old_date);
    RETURN NULL;
  END IF;

  IF TG_OP = 'INSERT' THEN
    PERFORM geokrety.fn_refresh_gk_moves_daily_activity_date(v_new_date);
    PERFORM geokrety.fn_refresh_daily_active_users_date(v_new_date);
    RETURN NULL;
  END IF;

  PERFORM geokrety.fn_refresh_gk_moves_daily_activity_date(v_old_date);
  PERFORM geokrety.fn_refresh_daily_active_users_date(v_old_date);

  IF v_new_date IS DISTINCT FROM v_old_date THEN
    PERFORM geokrety.fn_refresh_gk_moves_daily_activity_date(v_new_date);
    PERFORM geokrety.fn_refresh_daily_active_users_date(v_new_date);
  END IF;

  RETURN NULL;
END;
$$;

DROP TRIGGER IF EXISTS tr_gk_moves_after_daily_activity_delete ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_moves_after_daily_activity_insert ON geokrety.gk_moves;
DROP TRIGGER IF EXISTS tr_gk_moves_after_daily_activity ON geokrety.gk_moves;

CREATE TRIGGER tr_gk_moves_after_daily_activity
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_daily_activity();

DROP TRIGGER IF EXISTS tr_gk_moves_after_relations ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_relations
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_relations();

DROP TRIGGER IF EXISTS tr_gk_moves_after_waypoint_visits ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_waypoint_visits
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_waypoint_cache();

DROP TRIGGER IF EXISTS tr_gk_moves_after_country_history ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_country_history
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_country_history();

DROP TRIGGER IF EXISTS tr_gk_moves_after_country_rollups ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_country_rollups
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_country_rollups();

DROP TRIGGER IF EXISTS tr_gk_moves_after_sharded_counters ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_sharded_counters
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_sharded_counter();
SQL
        );
    }
}
