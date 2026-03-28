<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateWaypointRelationshipTables extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE TABLE stats.gk_cache_visits (
  gk_id INT NOT NULL,
  waypoint_id BIGINT NOT NULL,
  visit_count BIGINT NOT NULL DEFAULT 0,
  first_visited_at TIMESTAMPTZ NOT NULL,
  last_visited_at TIMESTAMPTZ NOT NULL,
  PRIMARY KEY (gk_id, waypoint_id),
  CONSTRAINT fk_gk_cache_visits_waypoint
    FOREIGN KEY (waypoint_id) REFERENCES stats.waypoints(id)
    DEFERRABLE INITIALLY DEFERRED
);

COMMENT ON TABLE stats.gk_cache_visits IS 'Per-GeoKret per-waypoint visit counter; enables cache analytics without gk_moves scans';
COMMENT ON COLUMN stats.gk_cache_visits.gk_id IS 'GeoKret internal ID (references geokrety.gk_geokrety.id, not FK to avoid cross-schema dep)';
COMMENT ON COLUMN stats.gk_cache_visits.waypoint_id IS 'FK to stats.waypoints(id); surrogated to allow rename/merge without cascading issues';
COMMENT ON COLUMN stats.gk_cache_visits.visit_count IS 'Count of moves referencing this waypoint for this GeoKret; incremented by trigger';

CREATE TABLE stats.user_cache_visits (
  user_id INT NOT NULL,
  waypoint_id BIGINT NOT NULL,
  visit_count BIGINT NOT NULL DEFAULT 0,
  first_visited_at TIMESTAMPTZ NOT NULL,
  last_visited_at TIMESTAMPTZ NOT NULL,
  PRIMARY KEY (user_id, waypoint_id),
  CONSTRAINT fk_user_cache_visits_waypoint
    FOREIGN KEY (waypoint_id) REFERENCES stats.waypoints(id)
    DEFERRABLE INITIALLY DEFERRED
);

COMMENT ON TABLE stats.user_cache_visits IS 'Per-user per-waypoint visit counter; enables user cache analytics without gk_moves scans';
COMMENT ON COLUMN stats.user_cache_visits.user_id IS 'User internal ID (references geokrety.gk_users.id; no cross-schema FK)';
COMMENT ON COLUMN stats.user_cache_visits.visit_count IS 'Number of moves by this user at this waypoint; incremented by trigger';

CREATE TABLE stats.gk_related_users (
  geokrety_id INT NOT NULL,
  user_id INT NOT NULL,
  interaction_count BIGINT NOT NULL DEFAULT 0,
  first_interaction TIMESTAMPTZ NOT NULL,
  last_interaction TIMESTAMPTZ NOT NULL,
  PRIMARY KEY (geokrety_id, user_id)
);

COMMENT ON TABLE stats.gk_related_users IS 'Per-GeoKret per-user interaction counter; powers UC3, reach bonus, and social graph';
COMMENT ON COLUMN stats.gk_related_users.geokrety_id IS 'GeoKret internal ID (no cross-schema FK)';
COMMENT ON COLUMN stats.gk_related_users.user_id IS 'User internal ID; only authenticated users (author IS NOT NULL)';
COMMENT ON COLUMN stats.gk_related_users.interaction_count IS 'Count of qualifying moves (DROP/GRAB/SEEN/DIP, not COMMENT) by this user on this GK';

CREATE TABLE stats.user_related_users (
  user_id INT NOT NULL,
  related_user_id INT NOT NULL,
  shared_geokrety_count BIGINT NOT NULL DEFAULT 0,
  first_seen_at TIMESTAMPTZ NOT NULL,
  last_seen_at TIMESTAMPTZ NOT NULL,
  PRIMARY KEY (user_id, related_user_id),
  CONSTRAINT chk_user_related_users_no_self CHECK (user_id <> related_user_id)
);

COMMENT ON TABLE stats.user_related_users IS 'Directional user-user relation via shared GeoKrety; both directions stored; powers UC2 social graph';
COMMENT ON COLUMN stats.user_related_users.shared_geokrety_count IS 'Number of distinct GeoKrety that both users have interacted with';
COMMENT ON COLUMN stats.user_related_users.user_id IS 'Source user (authenticated only)';
COMMENT ON COLUMN stats.user_related_users.related_user_id IS 'Target user (authenticated only); never equals user_id';

CREATE INDEX IF NOT EXISTS idx_gk_cache_visits_waypoint
  ON stats.gk_cache_visits (waypoint_id, gk_id);

CREATE INDEX IF NOT EXISTS idx_user_cache_visits_waypoint
  ON stats.user_cache_visits (waypoint_id, user_id);

CREATE INDEX IF NOT EXISTS idx_gk_related_users_user
  ON stats.gk_related_users (user_id);
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP INDEX IF EXISTS stats.idx_gk_related_users_user;
DROP INDEX IF EXISTS stats.idx_user_cache_visits_waypoint;
DROP INDEX IF EXISTS stats.idx_gk_cache_visits_waypoint;
DROP TABLE IF EXISTS stats.user_related_users;
DROP TABLE IF EXISTS stats.gk_related_users;
DROP TABLE IF EXISTS stats.user_cache_visits;
DROP TABLE IF EXISTS stats.gk_cache_visits;
SQL
        );
    }
}
