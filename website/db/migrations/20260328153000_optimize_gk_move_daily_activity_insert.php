<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OptimizeGkMoveDailyActivityInsert extends AbstractMigration {
    public function up(): void {
        $this->execute(
            <<<'SQL'
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
    INSERT INTO stats.daily_activity (
      activity_date,
      total_moves,
      drops,
      grabs,
      comments,
      sees,
      archives,
      dips,
      km_contributed
    )
    VALUES (
      v_new_date,
      1,
      CASE WHEN NEW.move_type = 0 THEN 1 ELSE 0 END,
      CASE WHEN NEW.move_type = 1 THEN 1 ELSE 0 END,
      CASE WHEN NEW.move_type = 2 THEN 1 ELSE 0 END,
      CASE WHEN NEW.move_type = 3 THEN 1 ELSE 0 END,
      CASE WHEN NEW.move_type = 4 THEN 1 ELSE 0 END,
      CASE WHEN NEW.move_type = 5 THEN 1 ELSE 0 END,
      COALESCE(NEW.km_distance, 0)::NUMERIC(14,3)
    )
    ON CONFLICT (activity_date) DO UPDATE SET
      total_moves = stats.daily_activity.total_moves + 1,
      drops = stats.daily_activity.drops + EXCLUDED.drops,
      grabs = stats.daily_activity.grabs + EXCLUDED.grabs,
      comments = stats.daily_activity.comments + EXCLUDED.comments,
      sees = stats.daily_activity.sees + EXCLUDED.sees,
      archives = stats.daily_activity.archives + EXCLUDED.archives,
      dips = stats.daily_activity.dips + EXCLUDED.dips,
      km_contributed = stats.daily_activity.km_contributed + EXCLUDED.km_contributed;

    IF NEW.author IS NOT NULL THEN
      INSERT INTO stats.daily_active_users (activity_date, user_id)
      VALUES (v_new_date, NEW.author)
      ON CONFLICT (activity_date, user_id) DO NOTHING;
    END IF;

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
SQL,
        );
    }

    public function down(): void {
        $this->execute(
            <<<'SQL'
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
SQL,
        );
    }
}
