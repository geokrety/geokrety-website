<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCounterSnapshotAndSeedFunctions extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION stats.fn_snapshot_entity_counters()
RETURNS VOID
LANGUAGE plpgsql
AS $$
BEGIN
  DELETE FROM stats.entity_counters_shard;

  INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
  SELECT
    entities.entity,
    shards.shard,
    COALESCE(entity_totals.cnt, 0) AS cnt
  FROM (
    VALUES
      ('gk_moves'), ('gk_moves_type_0'), ('gk_moves_type_1'), ('gk_moves_type_2'),
      ('gk_moves_type_3'), ('gk_moves_type_4'), ('gk_moves_type_5'),
      ('gk_geokrety'), ('gk_geokrety_type_0'), ('gk_geokrety_type_1'),
      ('gk_geokrety_type_2'), ('gk_geokrety_type_3'), ('gk_geokrety_type_4'),
      ('gk_geokrety_type_5'), ('gk_geokrety_type_6'), ('gk_geokrety_type_7'),
      ('gk_geokrety_type_8'), ('gk_geokrety_type_9'), ('gk_geokrety_type_10'),
      ('gk_pictures'), ('gk_pictures_type_0'), ('gk_pictures_type_1'),
      ('gk_pictures_type_2'), ('gk_users'), ('gk_loves')
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
    UNION ALL
    SELECT 'gk_geokrety'::TEXT, (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_geokrety
    GROUP BY (id % 16)
    UNION ALL
    SELECT format('gk_geokrety_type_%s', type), (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_geokrety
    GROUP BY type, (id % 16)
    UNION ALL
    SELECT 'gk_pictures'::TEXT, (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_pictures
    WHERE uploaded_on_datetime IS NOT NULL
    GROUP BY (id % 16)
    UNION ALL
    SELECT format('gk_pictures_type_%s', type), (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_pictures
    WHERE uploaded_on_datetime IS NOT NULL
    GROUP BY type, (id % 16)
    UNION ALL
    SELECT 'gk_users'::TEXT, (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_users
    GROUP BY (id % 16)
    UNION ALL
    SELECT 'gk_loves'::TEXT, (id % 16), COUNT(*)::BIGINT
    FROM geokrety.gk_loves
    GROUP BY (id % 16)
  ) AS entity_totals USING (entity, shard);

  RAISE NOTICE 'Entity counter snapshot completed - all 25 entities refreshed';
END;
$$;

COMMENT ON FUNCTION stats.fn_snapshot_entity_counters() IS 'Seeds entity_counters_shard from current source table counts; idempotent; run once during Sprint 6 backfill';

CREATE OR REPLACE FUNCTION stats.fn_seed_daily_activity(
  p_period tstzrange DEFAULT NULL
)
RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
  v_rows BIGINT := 0;
BEGIN
  IF p_period IS NOT NULL AND (
    lower_inc(p_period) IS DISTINCT FROM TRUE
    OR upper_inc(p_period) IS DISTINCT FROM FALSE
    OR lower(p_period) IS DISTINCT FROM date_trunc('day', lower(p_period))
    OR upper(p_period) IS DISTINCT FROM date_trunc('day', upper(p_period))
  ) THEN
    RAISE EXCEPTION 'p_period must use whole-day [) bounds'
      USING ERRCODE = '22023';
  END IF;

  IF p_period IS NULL THEN
    DELETE FROM stats.daily_active_users;
  ELSE
    DELETE FROM stats.daily_active_users
    WHERE tstzrange(
      activity_date::timestamp with time zone,
      (activity_date + 1)::timestamp with time zone,
      '[)'
    ) && p_period;
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
    km_contributed,
    gk_created,
    pictures_uploaded_total,
    pictures_uploaded_avatar,
    pictures_uploaded_move,
    pictures_uploaded_user,
    users_registered
  )
  WITH activity_days AS (
    SELECT activity_date
    FROM stats.daily_activity
    WHERE p_period IS NULL
       OR tstzrange(
            activity_date::timestamp with time zone,
            (activity_date + 1)::timestamp with time zone,
            '[)'
          ) && p_period
    UNION
    SELECT moved_on_datetime::date AS activity_date
    FROM geokrety.gk_moves
    WHERE p_period IS NULL OR moved_on_datetime <@ p_period
    UNION
    SELECT created_on_datetime::date
    FROM geokrety.gk_geokrety
    WHERE p_period IS NULL OR created_on_datetime <@ p_period
    UNION
    SELECT uploaded_on_datetime::date
    FROM geokrety.gk_pictures
    WHERE uploaded_on_datetime IS NOT NULL
      AND (p_period IS NULL OR uploaded_on_datetime <@ p_period)
    UNION
    SELECT joined_on_datetime::date
    FROM geokrety.gk_users
    WHERE p_period IS NULL OR joined_on_datetime <@ p_period
  ),
  moves_daily AS (
    SELECT
      moved_on_datetime::date AS activity_date,
      COUNT(*)::BIGINT AS total_moves,
      COUNT(*) FILTER (WHERE move_type = 0)::BIGINT AS drops,
      COUNT(*) FILTER (WHERE move_type = 1)::BIGINT AS grabs,
      COUNT(*) FILTER (WHERE move_type = 2)::BIGINT AS comments,
      COUNT(*) FILTER (WHERE move_type = 3)::BIGINT AS sees,
      COUNT(*) FILTER (WHERE move_type = 4)::BIGINT AS archives,
      COUNT(*) FILTER (WHERE move_type = 5)::BIGINT AS dips,
      COALESCE(SUM(km_distance), 0)::NUMERIC(14,3) AS km_contributed
    FROM geokrety.gk_moves
    WHERE p_period IS NULL OR moved_on_datetime <@ p_period
    GROUP BY moved_on_datetime::date
  ),
  geokrety_daily AS (
    SELECT
      created_on_datetime::date AS activity_date,
      COUNT(*)::BIGINT AS gk_created
    FROM geokrety.gk_geokrety
    WHERE p_period IS NULL OR created_on_datetime <@ p_period
    GROUP BY created_on_datetime::date
  ),
  pictures_daily AS (
    SELECT
      uploaded_on_datetime::date AS activity_date,
      COUNT(*)::BIGINT AS pictures_uploaded_total,
      COUNT(*) FILTER (WHERE type = 0)::BIGINT AS pictures_uploaded_avatar,
      COUNT(*) FILTER (WHERE type = 1)::BIGINT AS pictures_uploaded_move,
      COUNT(*) FILTER (WHERE type = 2)::BIGINT AS pictures_uploaded_user
    FROM geokrety.gk_pictures
    WHERE uploaded_on_datetime IS NOT NULL
      AND (p_period IS NULL OR uploaded_on_datetime <@ p_period)
    GROUP BY uploaded_on_datetime::date
  ),
  users_daily AS (
    SELECT
      joined_on_datetime::date AS activity_date,
      COUNT(*)::BIGINT AS users_registered
    FROM geokrety.gk_users
    WHERE p_period IS NULL OR joined_on_datetime <@ p_period
    GROUP BY joined_on_datetime::date
  )
  SELECT
    activity_days.activity_date,
    COALESCE(moves_daily.total_moves, 0),
    COALESCE(moves_daily.drops, 0),
    COALESCE(moves_daily.grabs, 0),
    COALESCE(moves_daily.comments, 0),
    COALESCE(moves_daily.sees, 0),
    COALESCE(moves_daily.archives, 0),
    COALESCE(moves_daily.dips, 0),
    COALESCE(moves_daily.km_contributed, 0)::NUMERIC(14,3),
    COALESCE(geokrety_daily.gk_created, 0),
    COALESCE(pictures_daily.pictures_uploaded_total, 0),
    COALESCE(pictures_daily.pictures_uploaded_avatar, 0),
    COALESCE(pictures_daily.pictures_uploaded_move, 0),
    COALESCE(pictures_daily.pictures_uploaded_user, 0),
    COALESCE(users_daily.users_registered, 0)
  FROM activity_days
  LEFT JOIN moves_daily USING (activity_date)
  LEFT JOIN geokrety_daily USING (activity_date)
  LEFT JOIN pictures_daily USING (activity_date)
  LEFT JOIN users_daily USING (activity_date)
  ORDER BY activity_days.activity_date
  ON CONFLICT (activity_date) DO UPDATE SET
    total_moves = EXCLUDED.total_moves,
    drops = EXCLUDED.drops,
    grabs = EXCLUDED.grabs,
    comments = EXCLUDED.comments,
    sees = EXCLUDED.sees,
    archives = EXCLUDED.archives,
    dips = EXCLUDED.dips,
    km_contributed = EXCLUDED.km_contributed,
    gk_created = EXCLUDED.gk_created,
    pictures_uploaded_total = EXCLUDED.pictures_uploaded_total,
    pictures_uploaded_avatar = EXCLUDED.pictures_uploaded_avatar,
    pictures_uploaded_move = EXCLUDED.pictures_uploaded_move,
    pictures_uploaded_user = EXCLUDED.pictures_uploaded_user,
    users_registered = EXCLUDED.users_registered;

  GET DIAGNOSTICS v_rows = ROW_COUNT;

  INSERT INTO stats.daily_active_users (activity_date, user_id)
  SELECT DISTINCT
    moved_on_datetime::date AS activity_date,
    author
  FROM geokrety.gk_moves
  WHERE author IS NOT NULL
    AND (p_period IS NULL OR moved_on_datetime <@ p_period)
  ON CONFLICT (activity_date, user_id) DO NOTHING;

  DELETE FROM stats.daily_activity AS activity
  WHERE (p_period IS NULL OR tstzrange(
          activity.activity_date::timestamp with time zone,
          (activity.activity_date + 1)::timestamp with time zone,
          '[)'
        ) && p_period)
    AND activity.total_moves = 0
    AND activity.drops = 0
    AND activity.grabs = 0
    AND activity.comments = 0
    AND activity.sees = 0
    AND activity.archives = 0
    AND activity.dips = 0
    AND activity.km_contributed = 0
    AND activity.gk_created = 0
    AND activity.pictures_uploaded_total = 0
    AND activity.pictures_uploaded_avatar = 0
    AND activity.pictures_uploaded_move = 0
    AND activity.pictures_uploaded_user = 0
    AND activity.users_registered = 0
    AND COALESCE(activity.points_contributed, 0) = 0
    AND COALESCE(activity.loves_count, 0) = 0;

  RAISE NOTICE 'Daily activity seed completed: % rows in daily_activity affected', v_rows;
  RETURN v_rows;
END;
$$;

COMMENT ON FUNCTION stats.fn_seed_daily_activity(tstzrange) IS 'Idempotent backfill of daily_activity and daily_active_users from source tables; p_period limits to a date range; pass NULL to seed all history';
SQL
        );
    }

    public function down(): void {
        $this->execute('DROP FUNCTION IF EXISTS stats.fn_seed_daily_activity(tstzrange);');
        $this->execute('DROP FUNCTION IF EXISTS stats.fn_snapshot_entity_counters();');
    }
}
