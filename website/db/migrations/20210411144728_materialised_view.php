<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MaterialisedView extends AbstractMigration {
    public function up() {
        $this->execute('
DROP VIEW geokrety.gk_geokrety_near_users_homes;
CREATE OR REPLACE VIEW geokrety.gk_geokrety_near_users_homes
    AS
     SELECT c_user.id AS c_user_id,
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
    st_distance(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude))/1000 AS home_distance
   FROM gk_geokrety_in_caches,
    gk_users c_user
  WHERE st_dwithin(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude), (c_user.observation_area * 1000)::double precision)
  ORDER BY (st_distance(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude)) < (c_user.observation_area * 1000)::double precision);
');
        $this->execute('CREATE UNIQUE INDEX idx_gk_geokrety_in_caches_id ON gk_geokrety_in_caches (id);');
        $this->execute('CREATE INDEX idx_gk_geokrety_in_caches_moved_on_datetime ON gk_geokrety_in_caches (moved_on_datetime);');
    }

    public function down() {
        $this->execute('DROP INDEX idx_gk_geokrety_in_caches_moved_on_datetime;');
        $this->execute('DROP INDEX idx_gk_geokrety_in_caches_id;');
        $this->execute('
DROP VIEW geokrety.gk_geokrety_near_users_homes;
CREATE OR REPLACE VIEW geokrety.gk_geokrety_near_users_homes
    AS
     SELECT c_user.id AS c_user_id,
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
    st_distance(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude)) AS home_distance
   FROM gk_geokrety_in_caches,
    gk_users c_user
  WHERE st_dwithin(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude), (c_user.observation_area * 1000)::double precision)
  ORDER BY (st_distance(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude)) < (c_user.observation_area * 1000)::double precision);
');
    }
}
