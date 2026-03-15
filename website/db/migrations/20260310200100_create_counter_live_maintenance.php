<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCounterLiveMaintenance extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION geokrety.fn_gk_moves_sharded_counter()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_old_shard INT;
  v_new_shard INT;
  v_old_type_entity TEXT;
  v_new_type_entity TEXT;
BEGIN
  v_old_shard := CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN (OLD.id % 16) ELSE NULL END;
  v_new_shard := CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN (NEW.id % 16) ELSE NULL END;
  v_old_type_entity := CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN format('gk_moves_type_%s', OLD.move_type) ELSE NULL END;
  v_new_type_entity := CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN format('gk_moves_type_%s', NEW.move_type) ELSE NULL END;

  IF TG_OP = 'INSERT' THEN
    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_moves', v_new_shard, 1)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = stats.entity_counters_shard.cnt + 1;

    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES (v_new_type_entity, v_new_shard, 1)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = stats.entity_counters_shard.cnt + 1;

    RETURN NEW;
  END IF;

  IF TG_OP = 'DELETE' THEN
    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_moves', v_old_shard, 0)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);

    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES (v_old_type_entity, v_old_shard, 0)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);

    RETURN OLD;
  END IF;

  IF (OLD.id, OLD.move_type) = (NEW.id, NEW.move_type) THEN
    RETURN NEW;
  END IF;

  IF OLD.id <> NEW.id THEN
    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_moves', v_old_shard, 0)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);

    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_moves', v_new_shard, 1)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = stats.entity_counters_shard.cnt + 1;
  END IF;

  IF OLD.id <> NEW.id OR OLD.move_type <> NEW.move_type THEN
    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES (v_old_type_entity, v_old_shard, 0)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);

    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES (v_new_type_entity, v_new_shard, 1)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = stats.entity_counters_shard.cnt + 1;
  END IF;

  RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS tr_gk_moves_after_sharded_counters ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_sharded_counters
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_sharded_counter();

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_gk_moves_daily_activity_date(
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
    total_moves,
    drops,
    grabs,
    comments,
    sees,
    archives,
    dips,
    km_contributed
  )
  SELECT
    p_activity_date,
    COUNT(*)::BIGINT,
    COUNT(*) FILTER (WHERE move_type = 0)::BIGINT,
    COUNT(*) FILTER (WHERE move_type = 1)::BIGINT,
    COUNT(*) FILTER (WHERE move_type = 2)::BIGINT,
    COUNT(*) FILTER (WHERE move_type = 3)::BIGINT,
    COUNT(*) FILTER (WHERE move_type = 4)::BIGINT,
    COUNT(*) FILTER (WHERE move_type = 5)::BIGINT,
    COALESCE(SUM(km_distance), 0)::NUMERIC(14,3)
  FROM geokrety.gk_moves
  WHERE moved_on_datetime >= p_activity_date::timestamp with time zone
    AND moved_on_datetime < (p_activity_date + 1)::timestamp with time zone
  ON CONFLICT (activity_date) DO UPDATE SET
    total_moves = EXCLUDED.total_moves,
    drops = EXCLUDED.drops,
    grabs = EXCLUDED.grabs,
    comments = EXCLUDED.comments,
    sees = EXCLUDED.sees,
    archives = EXCLUDED.archives,
    dips = EXCLUDED.dips,
    km_contributed = EXCLUDED.km_contributed;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_daily_active_users_date(
  p_activity_date DATE
)
RETURNS VOID
LANGUAGE plpgsql
AS $$
BEGIN
  IF p_activity_date IS NULL THEN
    RETURN;
  END IF;

  DELETE FROM stats.daily_active_users
  WHERE activity_date = p_activity_date;

  INSERT INTO stats.daily_active_users (activity_date, user_id)
  SELECT
    p_activity_date,
    author
  FROM geokrety.gk_moves
  WHERE author IS NOT NULL
    AND moved_on_datetime >= p_activity_date::timestamp with time zone
    AND moved_on_datetime < (p_activity_date + 1)::timestamp with time zone
  GROUP BY author;
END;
$$;

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

DROP TRIGGER IF EXISTS tr_gk_moves_after_daily_activity ON geokrety.gk_moves;
CREATE TRIGGER tr_gk_moves_after_daily_activity
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_moves
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_moves_daily_activity();

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_gk_geokrety_daily_activity_date(
  p_activity_date DATE
)
RETURNS VOID
LANGUAGE plpgsql
AS $$
BEGIN
  IF p_activity_date IS NULL THEN
    RETURN;
  END IF;

  INSERT INTO stats.daily_activity (activity_date, gk_created)
  SELECT
    p_activity_date,
    COUNT(*)::BIGINT
  FROM geokrety.gk_geokrety
  WHERE created_on_datetime >= p_activity_date::timestamp with time zone
    AND created_on_datetime < (p_activity_date + 1)::timestamp with time zone
  ON CONFLICT (activity_date) DO UPDATE SET
    gk_created = EXCLUDED.gk_created;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_geokrety_counter()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_shard INT;
  v_gk_type INT;
  v_date DATE;
BEGIN
  IF TG_OP = 'INSERT' THEN
    v_shard := NEW.id % 16;
    v_gk_type := NEW.type;
    v_date := NEW.created_on_datetime::date;

    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_geokrety', v_shard, 1)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = stats.entity_counters_shard.cnt + 1;

    IF v_gk_type BETWEEN 0 AND 10 THEN
      INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
      VALUES (format('gk_geokrety_type_%s', v_gk_type), v_shard, 1)
      ON CONFLICT (entity, shard) DO UPDATE SET cnt = stats.entity_counters_shard.cnt + 1;
    END IF;

    PERFORM geokrety.fn_refresh_gk_geokrety_daily_activity_date(v_date);
    RETURN NULL;
  END IF;

  v_shard := OLD.id % 16;
  v_gk_type := OLD.type;
  v_date := OLD.created_on_datetime::date;

  INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
  VALUES ('gk_geokrety', v_shard, 0)
  ON CONFLICT (entity, shard) DO UPDATE SET cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);

  IF v_gk_type BETWEEN 0 AND 10 THEN
    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES (format('gk_geokrety_type_%s', v_gk_type), v_shard, 0)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);
  END IF;

  PERFORM geokrety.fn_refresh_gk_geokrety_daily_activity_date(v_date);
  RETURN NULL;
END;
$$;

DROP TRIGGER IF EXISTS tr_gk_geokrety_counters ON geokrety.gk_geokrety;
CREATE TRIGGER tr_gk_geokrety_counters
  AFTER INSERT OR DELETE ON geokrety.gk_geokrety
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_geokrety_counter();

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_gk_pictures_daily_activity_date(
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
    pictures_uploaded_total,
    pictures_uploaded_avatar,
    pictures_uploaded_move,
    pictures_uploaded_user
  )
  SELECT
    p_activity_date,
    COUNT(*)::BIGINT,
    COUNT(*) FILTER (WHERE type = 0)::BIGINT,
    COUNT(*) FILTER (WHERE type = 1)::BIGINT,
    COUNT(*) FILTER (WHERE type = 2)::BIGINT
  FROM geokrety.gk_pictures
  WHERE uploaded_on_datetime IS NOT NULL
    AND uploaded_on_datetime >= p_activity_date::timestamp with time zone
    AND uploaded_on_datetime < (p_activity_date + 1)::timestamp with time zone
  ON CONFLICT (activity_date) DO UPDATE SET
    pictures_uploaded_total = EXCLUDED.pictures_uploaded_total,
    pictures_uploaded_avatar = EXCLUDED.pictures_uploaded_avatar,
    pictures_uploaded_move = EXCLUDED.pictures_uploaded_move,
    pictures_uploaded_user = EXCLUDED.pictures_uploaded_user;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_pictures_counter()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_old_shard INT;
  v_new_shard INT;
  v_old_date DATE;
  v_new_date DATE;
  v_old_type_entity TEXT;
  v_new_type_entity TEXT;
  v_old_active BOOLEAN;
  v_new_active BOOLEAN;
BEGIN
  v_old_shard := CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN (OLD.id % 16) ELSE NULL END;
  v_new_shard := CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN (NEW.id % 16) ELSE NULL END;
  v_old_active := TG_OP IN ('UPDATE', 'DELETE') AND OLD.uploaded_on_datetime IS NOT NULL;
  v_new_active := TG_OP IN ('INSERT', 'UPDATE') AND NEW.uploaded_on_datetime IS NOT NULL;
  v_old_date := CASE WHEN v_old_active THEN OLD.uploaded_on_datetime::date ELSE NULL END;
  v_new_date := CASE WHEN v_new_active THEN NEW.uploaded_on_datetime::date ELSE NULL END;
  v_old_type_entity := CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN format('gk_pictures_type_%s', OLD.type) ELSE NULL END;
  v_new_type_entity := CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN format('gk_pictures_type_%s', NEW.type) ELSE NULL END;

  IF TG_OP = 'INSERT' THEN
    IF NOT v_new_active THEN
      RETURN NULL;
    END IF;

    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_pictures', v_new_shard, 1)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = stats.entity_counters_shard.cnt + 1;

    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES (v_new_type_entity, v_new_shard, 1)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = stats.entity_counters_shard.cnt + 1;

    PERFORM geokrety.fn_refresh_gk_pictures_daily_activity_date(v_new_date);
    RETURN NULL;
  END IF;

  IF TG_OP = 'DELETE' THEN
    IF NOT v_old_active THEN
      RETURN NULL;
    END IF;

    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_pictures', v_old_shard, 0)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);

    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES (v_old_type_entity, v_old_shard, 0)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);

    PERFORM geokrety.fn_refresh_gk_pictures_daily_activity_date(v_old_date);
    RETURN NULL;
  END IF;

  IF (OLD.id, OLD.type, OLD.uploaded_on_datetime::date) IS NOT DISTINCT FROM (NEW.id, NEW.type, NEW.uploaded_on_datetime::date)
     AND v_old_active = v_new_active THEN
    RETURN NULL;
  END IF;

  IF v_old_active AND (NOT v_new_active OR OLD.id <> NEW.id) THEN
    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_pictures', v_old_shard, 0)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);
  END IF;

  IF v_new_active AND (NOT v_old_active OR OLD.id <> NEW.id) THEN
    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_pictures', v_new_shard, 1)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = stats.entity_counters_shard.cnt + 1;
  END IF;

  IF v_old_active AND (NOT v_new_active OR OLD.id <> NEW.id OR OLD.type <> NEW.type) THEN
    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES (v_old_type_entity, v_old_shard, 0)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);
  END IF;

  IF v_new_active AND (NOT v_old_active OR OLD.id <> NEW.id OR OLD.type <> NEW.type) THEN
    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES (v_new_type_entity, v_new_shard, 1)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = stats.entity_counters_shard.cnt + 1;
  END IF;

  IF v_old_active AND (NOT v_new_active OR OLD.type IS DISTINCT FROM NEW.type OR v_old_date IS DISTINCT FROM v_new_date) THEN
    PERFORM geokrety.fn_refresh_gk_pictures_daily_activity_date(v_old_date);
  END IF;

  IF v_new_active AND (NOT v_old_active OR OLD.type IS DISTINCT FROM NEW.type OR v_new_date IS DISTINCT FROM v_old_date) THEN
    PERFORM geokrety.fn_refresh_gk_pictures_daily_activity_date(v_new_date);
  END IF;

  RETURN NULL;
END;
$$;

DROP TRIGGER IF EXISTS tr_gk_pictures_after_counter ON geokrety.gk_pictures;
CREATE TRIGGER tr_gk_pictures_after_counter
  AFTER INSERT OR UPDATE OR DELETE ON geokrety.gk_pictures
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_pictures_counter();

CREATE OR REPLACE FUNCTION geokrety.fn_refresh_gk_users_daily_activity_date(
  p_activity_date DATE
)
RETURNS VOID
LANGUAGE plpgsql
AS $$
BEGIN
  IF p_activity_date IS NULL THEN
    RETURN;
  END IF;

  INSERT INTO stats.daily_activity (activity_date, users_registered)
  SELECT
    p_activity_date,
    COUNT(*)::BIGINT
  FROM geokrety.gk_users
  WHERE joined_on_datetime >= p_activity_date::timestamp with time zone
    AND joined_on_datetime < (p_activity_date + 1)::timestamp with time zone
  ON CONFLICT (activity_date) DO UPDATE SET
    users_registered = EXCLUDED.users_registered;
END;
$$;

CREATE OR REPLACE FUNCTION geokrety.fn_gk_users_counter()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_shard INT;
  v_date DATE;
BEGIN
  IF TG_OP = 'INSERT' THEN
    v_shard := NEW.id % 16;
    v_date := NEW.joined_on_datetime::date;

    INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
    VALUES ('gk_users', v_shard, 1)
    ON CONFLICT (entity, shard) DO UPDATE SET cnt = stats.entity_counters_shard.cnt + 1;

    PERFORM geokrety.fn_refresh_gk_users_daily_activity_date(v_date);
    RETURN NULL;
  END IF;

  v_shard := OLD.id % 16;
  v_date := OLD.joined_on_datetime::date;

  INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
  VALUES ('gk_users', v_shard, 0)
  ON CONFLICT (entity, shard) DO UPDATE SET cnt = GREATEST(0, stats.entity_counters_shard.cnt - 1);

  PERFORM geokrety.fn_refresh_gk_users_daily_activity_date(v_date);
  RETURN NULL;
END;
$$;

DROP TRIGGER IF EXISTS tr_gk_users_activity ON geokrety.gk_users;
CREATE TRIGGER tr_gk_users_activity
  AFTER INSERT OR DELETE ON geokrety.gk_users
  FOR EACH ROW EXECUTE FUNCTION geokrety.fn_gk_users_counter();

INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
SELECT
  entities.entity,
  shards.shard,
  COALESCE(entity_totals.cnt, 0) AS cnt
FROM (
  VALUES
    ('gk_moves'), ('gk_moves_type_0'), ('gk_moves_type_1'), ('gk_moves_type_2'),
    ('gk_moves_type_3'), ('gk_moves_type_4'), ('gk_moves_type_5')
) AS entities(entity)
CROSS JOIN generate_series(0, 15) AS shards(shard)
LEFT JOIN (
  SELECT 'gk_moves'::TEXT AS entity, (id % 16) AS shard, COUNT(*)::BIGINT AS cnt
  FROM geokrety.gk_moves
  GROUP BY (id % 16)
  UNION ALL
  SELECT format('gk_moves_type_%s', move_type), (id % 16), COUNT(*)::BIGINT
  FROM geokrety.gk_moves
  GROUP BY move_type, (id % 16)
) AS entity_totals USING (entity, shard)
ON CONFLICT (entity, shard) DO UPDATE SET cnt = EXCLUDED.cnt;

INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
SELECT
  entities.entity,
  shards.shard,
  COALESCE(entity_totals.cnt, 0) AS cnt
FROM (
  VALUES
    ('gk_geokrety'), ('gk_geokrety_type_0'), ('gk_geokrety_type_1'), ('gk_geokrety_type_2'),
    ('gk_geokrety_type_3'), ('gk_geokrety_type_4'), ('gk_geokrety_type_5'), ('gk_geokrety_type_6'),
    ('gk_geokrety_type_7'), ('gk_geokrety_type_8'), ('gk_geokrety_type_9'), ('gk_geokrety_type_10')
) AS entities(entity)
CROSS JOIN generate_series(0, 15) AS shards(shard)
LEFT JOIN (
  SELECT 'gk_geokrety'::TEXT AS entity, (id % 16) AS shard, COUNT(*)::BIGINT AS cnt
  FROM geokrety.gk_geokrety
  GROUP BY (id % 16)
  UNION ALL
  SELECT format('gk_geokrety_type_%s', type), (id % 16), COUNT(*)::BIGINT
  FROM geokrety.gk_geokrety
  GROUP BY type, (id % 16)
) AS entity_totals USING (entity, shard)
ON CONFLICT (entity, shard) DO UPDATE SET cnt = EXCLUDED.cnt;

INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
SELECT
  entities.entity,
  shards.shard,
  COALESCE(entity_totals.cnt, 0) AS cnt
FROM (
  VALUES
    ('gk_pictures'),
    ('gk_pictures_type_0'),
    ('gk_pictures_type_1'),
    ('gk_pictures_type_2')
) AS entities(entity)
CROSS JOIN generate_series(0, 15) AS shards(shard)
LEFT JOIN (
  SELECT 'gk_pictures'::TEXT AS entity, (id % 16) AS shard, COUNT(*)::BIGINT AS cnt
  FROM geokrety.gk_pictures
  WHERE uploaded_on_datetime IS NOT NULL
  GROUP BY (id % 16)
  UNION ALL
  SELECT format('gk_pictures_type_%s', type), (id % 16), COUNT(*)::BIGINT
  FROM geokrety.gk_pictures
  WHERE uploaded_on_datetime IS NOT NULL
  GROUP BY type, (id % 16)
) AS entity_totals USING (entity, shard)
ON CONFLICT (entity, shard) DO UPDATE SET cnt = EXCLUDED.cnt;

INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
SELECT
  'gk_users',
  shards.shard,
  COALESCE(user_totals.cnt, 0) AS cnt
FROM generate_series(0, 15) AS shards(shard)
LEFT JOIN (
  SELECT (id % 16) AS shard, COUNT(*)::BIGINT AS cnt
  FROM geokrety.gk_users
  GROUP BY (id % 16)
) AS user_totals USING (shard)
ON CONFLICT (entity, shard) DO UPDATE SET cnt = EXCLUDED.cnt;
SQL
        );
    }

    public function down(): void {
        $this->execute('DROP TRIGGER IF EXISTS tr_gk_users_activity ON geokrety.gk_users;');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_gk_users_counter();');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_refresh_gk_users_daily_activity_date(date);');
        $this->execute('DROP TRIGGER IF EXISTS tr_gk_pictures_after_counter ON geokrety.gk_pictures;');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_gk_pictures_counter();');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_refresh_gk_pictures_daily_activity_date(date);');
        $this->execute('DROP TRIGGER IF EXISTS tr_gk_geokrety_counters ON geokrety.gk_geokrety;');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_gk_geokrety_counter();');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_refresh_gk_geokrety_daily_activity_date(date);');
        $this->execute('DROP TRIGGER IF EXISTS tr_gk_moves_after_daily_activity ON geokrety.gk_moves;');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_gk_moves_daily_activity();');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_refresh_daily_active_users_date(date);');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_refresh_gk_moves_daily_activity_date(date);');
        $this->execute('DROP TRIGGER IF EXISTS tr_gk_moves_after_sharded_counters ON geokrety.gk_moves;');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.fn_gk_moves_sharded_counter();');
    }
}
