
# Create views

```sql
BEGIN;
DROP VIEW IF EXISTS "gk_geokrety_near_users_homes";
DROP MATERIALIZED VIEW IF EXISTS "gk_geokrety_in_caches";
DROP VIEW IF EXISTS "gk_geokrety_with_details";


CREATE VIEW "gk_geokrety_with_details" AS
SELECT gk_geokrety.*,
    gk_moves."position",
    gk_moves.lat,
    gk_moves.lon,
    gk_moves.waypoint,
    gk_moves.elevation,
    gk_moves.country,
    gk_moves.move_type,
    gk_moves.author,
    gk_moves.moved_on_datetime,
    COALESCE(gk_moves.username, m_author.username) AS author_username,
    COALESCE(g_owner.username, 'Abandoned') AS owner_username,
    g_avatar.key AS avatar_key
   FROM gk_geokrety
     LEFT JOIN gk_moves ON (gk_geokrety.last_position = gk_moves.id)
     LEFT JOIN gk_users AS m_author ON (gk_moves.author = m_author.id)
     LEFT JOIN gk_users AS g_owner ON (gk_geokrety.owner = g_owner.id)
     LEFT JOIN gk_pictures AS g_avatar ON (gk_geokrety.avatar = g_avatar.id)
;


CREATE MATERIALIZED VIEW "gk_geokrety_in_caches" AS
SELECT gk_geokrety_with_details.*
   FROM gk_geokrety_with_details
  WHERE gk_geokrety_with_details.move_type = ANY (moves_types_markable_as_missing());


CREATE VIEW "gk_geokrety_near_users_homes" AS
SELECT c_user.id AS c_user_id,
    c_user.username AS c_username,
    gk_geokrety_in_caches.*,
    public.st_distance(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude)) AS home_distance
   FROM gk_geokrety_in_caches,
    gk_users c_user
  WHERE public.st_dwithin(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude), ((c_user.observation_area * 1000))::double precision)
  ORDER BY (public.st_distance(gk_geokrety_in_caches."position", coords2position(c_user.home_latitude, c_user.home_longitude)) < ((c_user.observation_area * 1000))::double precision);


END;
```
