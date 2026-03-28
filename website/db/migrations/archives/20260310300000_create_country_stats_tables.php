<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCountryStatsTables extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE TABLE stats.country_daily_stats (
  stats_date DATE NOT NULL,
  country_code CHAR(2) NOT NULL,
  moves_count BIGINT NOT NULL DEFAULT 0,
  drops BIGINT NOT NULL DEFAULT 0,
  grabs BIGINT NOT NULL DEFAULT 0,
  comments BIGINT NOT NULL DEFAULT 0,
  sees BIGINT NOT NULL DEFAULT 0,
  archives BIGINT NOT NULL DEFAULT 0,
  dips BIGINT NOT NULL DEFAULT 0,
  unique_users BIGINT NOT NULL DEFAULT 0,
  unique_gks BIGINT NOT NULL DEFAULT 0,
  km_contributed NUMERIC(14,3) NOT NULL DEFAULT 0,
  points_contributed NUMERIC(16,4) NOT NULL DEFAULT 0,
  loves_count BIGINT NOT NULL DEFAULT 0,
  pictures_uploaded_total BIGINT NOT NULL DEFAULT 0,
  pictures_uploaded_avatar BIGINT NOT NULL DEFAULT 0,
  pictures_uploaded_move BIGINT NOT NULL DEFAULT 0,
  pictures_uploaded_user BIGINT NOT NULL DEFAULT 0,
  PRIMARY KEY (stats_date, country_code)
);

COMMENT ON TABLE stats.country_daily_stats IS 'Daily per-country aggregate statistics for moves, distance, users, GKs, and content';
COMMENT ON COLUMN stats.country_daily_stats.unique_users IS 'Exact distinct user count maintained online for the date/country bucket';
COMMENT ON COLUMN stats.country_daily_stats.unique_gks IS 'Exact distinct GK count maintained online for the date/country bucket';

CREATE TABLE stats.gk_countries_visited (
  geokrety_id INT NOT NULL,
  country_code CHAR(2) NOT NULL,
  first_visited_at TIMESTAMPTZ NOT NULL,
  first_move_id BIGINT NOT NULL,
  move_count INT NOT NULL DEFAULT 1,
  PRIMARY KEY (geokrety_id, country_code)
);

COMMENT ON TABLE stats.gk_countries_visited IS 'Tracks which countries each GK has visited, with first-visit metadata and move counts';
COMMENT ON COLUMN stats.gk_countries_visited.first_move_id IS 'ID of the first move that placed this GK in this country';
COMMENT ON COLUMN stats.gk_countries_visited.move_count IS 'Total number of moves by this GK in this country';

CREATE TABLE stats.user_countries (
  user_id INT NOT NULL,
  country_code CHAR(2) NOT NULL,
  move_count BIGINT NOT NULL DEFAULT 0,
  first_visit TIMESTAMPTZ NOT NULL,
  last_visit TIMESTAMPTZ NOT NULL,
  PRIMARY KEY (user_id, country_code)
);

COMMENT ON TABLE stats.user_countries IS 'Tracks which countries each user has interacted in, with move counts and visit timestamps';
COMMENT ON COLUMN stats.user_countries.first_visit IS 'Timestamp of first move by this user in this country';
COMMENT ON COLUMN stats.user_countries.last_visit IS 'Timestamp of most recent move by this user in this country';

CREATE EXTENSION IF NOT EXISTS btree_gist;

CREATE TABLE stats.gk_country_history (
  id BIGSERIAL PRIMARY KEY,
  geokrety_id INT NOT NULL,
  country_code CHAR(2) NOT NULL,
  arrived_at TIMESTAMPTZ NOT NULL,
  departed_at TIMESTAMPTZ,
  move_id BIGINT NOT NULL,
  CONSTRAINT gk_country_history_excl
    EXCLUDE USING gist (
      geokrety_id WITH =,
      tstzrange(arrived_at, COALESCE(departed_at, 'infinity')) WITH &&
    )
);

COMMENT ON TABLE stats.gk_country_history IS 'Temporal intervals of GK presence in countries; exclusion constraint prevents overlapping intervals per GK';
COMMENT ON COLUMN stats.gk_country_history.departed_at IS 'NULL means the GK is currently in this country (open interval)';
COMMENT ON COLUMN stats.gk_country_history.move_id IS 'Move ID that caused the GK to arrive in this country';
SQL
        );
    }

    public function down(): void {
        $this->execute('DROP TABLE IF EXISTS stats.gk_country_history;');
        $this->execute('DROP TABLE IF EXISTS stats.user_countries;');
        $this->execute('DROP TABLE IF EXISTS stats.gk_countries_visited;');
        $this->execute('DROP TABLE IF EXISTS stats.country_daily_stats;');
    }
}
