<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixGeokretNearHome extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
BEGIN;

DROP VIEW geokrety.gk_geokrety_near_users_homes;
DROP MATERIALIZED VIEW geokrety.gk_geokrety_in_caches;

CREATE MATERIALIZED VIEW geokrety.gk_geokrety_in_caches AS
SELECT
    id, gkid, tracking_code, name, mission, owner, distance, caches_count,
    pictures_count, last_position, last_log, holder, avatar,
    created_on_datetime, updated_on_datetime, missing, type, "position",
    lat, lon, waypoint, elevation, country, move_type, author,
    moved_on_datetime, author_username, owner_username, avatar_key
FROM geokrety.gk_geokrety_with_details
WHERE move_type IN (0,3)
  AND position IS NOT NULL
WITH NO DATA;

REFRESH MATERIALIZED VIEW geokrety.gk_geokrety_in_caches;

CREATE VIEW geokrety.gk_geokrety_near_users_homes
    AS SELECT c_user.id AS c_user_id,
        c_user.username AS c_username,
        gk_geokrety_in_caches.id,
        gk_geokrety_in_caches.gkid,
        gk_geokrety_in_caches.tracking_code,
        gk_geokrety_in_caches.name,
        gk_geokrety_in_caches.mission,
        gk_geokrety_in_caches.owner,
        gk_geokrety_in_caches.distance,
        gk_geokrety_in_caches.caches_count,
        gk_geokrety_in_caches.pictures_count,
        gk_geokrety_in_caches.last_position,
        gk_geokrety_in_caches.last_log,
        gk_geokrety_in_caches.holder,
        gk_geokrety_in_caches.avatar,
        gk_geokrety_in_caches.created_on_datetime,
        gk_geokrety_in_caches.updated_on_datetime,
        gk_geokrety_in_caches.missing,
        gk_geokrety_in_caches.type,
        gk_geokrety_in_caches."position",
        gk_geokrety_in_caches.lat,
        gk_geokrety_in_caches.lon,
        gk_geokrety_in_caches.waypoint,
        gk_geokrety_in_caches.elevation,
        gk_geokrety_in_caches.country,
        gk_geokrety_in_caches.move_type,
        gk_geokrety_in_caches.author,
        gk_geokrety_in_caches.moved_on_datetime,
        gk_geokrety_in_caches.author_username,
        gk_geokrety_in_caches.owner_username,
        gk_geokrety_in_caches.avatar_key,
        st_distance(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude)) / 1000::double precision AS home_distance
    FROM gk_geokrety_in_caches,
        gk_users c_user
    WHERE st_dwithin(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude), (c_user.observation_area * 1000)::double precision)
    ORDER BY (st_distance(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude)) < (c_user.observation_area * 1000)::double precision);
COMMIT;
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
BEGIN;

DROP VIEW geokrety.gk_geokrety_near_users_homes;
DROP MATERIALIZED VIEW geokrety.gk_geokrety_in_caches;

CREATE MATERIALIZED VIEW geokrety.gk_geokrety_in_caches AS
SELECT
    id, gkid, tracking_code, name, mission, owner, distance, caches_count,
    pictures_count, last_position, last_log, holder, avatar,
    created_on_datetime, updated_on_datetime, missing, type, "position",
    lat, lon, waypoint, elevation, country, move_type, author,
    moved_on_datetime, author_username, owner_username, avatar_key
FROM geokrety.gk_geokrety_with_details
WHERE (move_type = ANY (geokrety.moves_types_markable_as_missing()))
WITH NO DATA;

REFRESH MATERIALIZED VIEW geokrety.gk_geokrety_in_caches;

CREATE VIEW geokrety.gk_geokrety_near_users_homes
    AS SELECT c_user.id AS c_user_id,
        c_user.username AS c_username,
        gk_geokrety_in_caches.id,
        gk_geokrety_in_caches.gkid,
        gk_geokrety_in_caches.tracking_code,
        gk_geokrety_in_caches.name,
        gk_geokrety_in_caches.mission,
        gk_geokrety_in_caches.owner,
        gk_geokrety_in_caches.distance,
        gk_geokrety_in_caches.caches_count,
        gk_geokrety_in_caches.pictures_count,
        gk_geokrety_in_caches.last_position,
        gk_geokrety_in_caches.last_log,
        gk_geokrety_in_caches.holder,
        gk_geokrety_in_caches.avatar,
        gk_geokrety_in_caches.created_on_datetime,
        gk_geokrety_in_caches.updated_on_datetime,
        gk_geokrety_in_caches.missing,
        gk_geokrety_in_caches.type,
        gk_geokrety_in_caches."position",
        gk_geokrety_in_caches.lat,
        gk_geokrety_in_caches.lon,
        gk_geokrety_in_caches.waypoint,
        gk_geokrety_in_caches.elevation,
        gk_geokrety_in_caches.country,
        gk_geokrety_in_caches.move_type,
        gk_geokrety_in_caches.author,
        gk_geokrety_in_caches.moved_on_datetime,
        gk_geokrety_in_caches.author_username,
        gk_geokrety_in_caches.owner_username,
        gk_geokrety_in_caches.avatar_key,
        st_distance(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude)) / 1000::double precision AS home_distance
    FROM gk_geokrety_in_caches,
        gk_users c_user
    WHERE st_dwithin(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude), (c_user.observation_area * 1000)::double precision)
    ORDER BY (st_distance(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude)) < (c_user.observation_area * 1000)::double precision);
COMMIT;
SQL
        );
    }
}
