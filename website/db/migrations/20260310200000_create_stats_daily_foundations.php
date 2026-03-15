<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStatsDailyFoundations extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE TABLE stats.entity_counters_shard (
  entity VARCHAR(32) NOT NULL,
  shard INT NOT NULL,
  cnt BIGINT NOT NULL DEFAULT 0,
  CONSTRAINT entity_counters_shard_shard_range_chk CHECK (shard BETWEEN 0 AND 15),
  CONSTRAINT entity_counters_shard_cnt_nonnegative_chk CHECK (cnt >= 0),
  PRIMARY KEY (entity, shard)
);

COMMENT ON TABLE stats.entity_counters_shard IS 'Sharded counter table for exact entity counts; sum cnt across all 16 shards to read an entity total';
COMMENT ON COLUMN stats.entity_counters_shard.entity IS 'Counter entity name, e.g. gk_moves, gk_moves_type_0, gk_geokrety_type_3';
COMMENT ON COLUMN stats.entity_counters_shard.shard IS 'Shard index from 0 to 15 used to spread concurrent counter updates';
COMMENT ON COLUMN stats.entity_counters_shard.cnt IS 'Exact counter value stored for one entity shard';

INSERT INTO stats.entity_counters_shard (entity, shard, cnt)
SELECT entities.entity, shards.shard, 0
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
CROSS JOIN generate_series(0, 15) AS shards(shard);

CREATE TABLE stats.daily_activity (
  activity_date DATE PRIMARY KEY,
  total_moves BIGINT NOT NULL DEFAULT 0,
  drops BIGINT NOT NULL DEFAULT 0,
  grabs BIGINT NOT NULL DEFAULT 0,
  comments BIGINT NOT NULL DEFAULT 0,
  sees BIGINT NOT NULL DEFAULT 0,
  archives BIGINT NOT NULL DEFAULT 0,
  dips BIGINT NOT NULL DEFAULT 0,
  km_contributed NUMERIC(14,3) NOT NULL DEFAULT 0,
  points_contributed NUMERIC(16,4) NOT NULL DEFAULT 0,
  gk_created BIGINT NOT NULL DEFAULT 0,
  pictures_uploaded_total BIGINT NOT NULL DEFAULT 0,
  pictures_uploaded_avatar BIGINT NOT NULL DEFAULT 0,
  pictures_uploaded_move BIGINT NOT NULL DEFAULT 0,
  pictures_uploaded_user BIGINT NOT NULL DEFAULT 0,
  loves_count BIGINT NOT NULL DEFAULT 0,
  users_registered BIGINT NOT NULL DEFAULT 0
);

COMMENT ON TABLE stats.daily_activity IS 'Per-calendar-day aggregate activity metrics; one row per day';
COMMENT ON COLUMN stats.daily_activity.points_contributed IS 'Total gamification points awarded on this date; updated by points-awarder service (Sprint 4)';
COMMENT ON COLUMN stats.daily_activity.gk_created IS 'New GeoKrety created on this date; updated by gk_geokrety trigger (Step 2.8)';
COMMENT ON COLUMN stats.daily_activity.pictures_uploaded_total IS 'Total pictures uploaded; updated by gk_pictures trigger (Step 2.9)';
COMMENT ON COLUMN stats.daily_activity.loves_count IS 'Loves given on this date; updated by loves trigger (Sprint 5)';
COMMENT ON COLUMN stats.daily_activity.users_registered IS 'New user registrations; updated by gk_users trigger (Step 2.10)';

CREATE TABLE stats.daily_active_users (
  activity_date DATE NOT NULL,
  user_id INT NOT NULL,
  PRIMARY KEY (activity_date, user_id)
);

COMMENT ON TABLE stats.daily_active_users IS 'Presence table for users active on a given day; one row per (activity_date, user_id)';

CREATE TABLE stats.daily_entity_counts (
  count_date DATE NOT NULL,
  entity VARCHAR(32) NOT NULL,
  cnt BIGINT NOT NULL DEFAULT 0,
  CONSTRAINT daily_entity_counts_cnt_nonnegative_chk CHECK (cnt >= 0),
  PRIMARY KEY (count_date, entity)
);

COMMENT ON TABLE stats.daily_entity_counts IS 'Daily cumulative entity counts for trend charts; populated by nightly snapshot job';
COMMENT ON COLUMN stats.daily_entity_counts.entity IS 'Entity name matching entity_counters_shard.entity';
COMMENT ON COLUMN stats.daily_entity_counts.cnt IS 'Entity count snapshot value for count_date';
SQL
        );
    }

    public function down(): void {
        $this->execute('DROP TABLE IF EXISTS stats.daily_entity_counts;');
        $this->execute('DROP TABLE IF EXISTS stats.daily_active_users;');
        $this->execute('DROP TABLE IF EXISTS stats.daily_activity;');
        $this->execute('DROP TABLE IF EXISTS stats.entity_counters_shard;');
    }
}
