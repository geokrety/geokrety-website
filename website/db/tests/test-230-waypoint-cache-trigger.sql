BEGIN;
SELECT plan(15);

INSERT INTO gk_users (id, username, registration_ip) VALUES (23001, 'waypoint-cache-user', '127.0.0.1');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (23001, 'Waypoint cache GK 1', 0, '2020-08-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (23002, 'Waypoint cache GK 2', 0, '2020-08-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (23003, 'Waypoint cache GK 3', 0, '2020-08-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (23004, 'Waypoint cache GK 4', 0, '2020-08-01 00:00:00+00');

SELECT has_function('geokrety', 'fn_gk_moves_waypoint_cache', ARRAY[]::text[], 'fn_gk_moves_waypoint_cache function exists');
SELECT function_returns('geokrety', 'fn_gk_moves_waypoint_cache', ARRAY[]::text[], 'trigger', 'fn_gk_moves_waypoint_cache returns trigger');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_after_waypoint_visits', 'tr_gk_moves_after_waypoint_visits trigger exists');

INSERT INTO gk_moves (id, geokret, author, position, waypoint, moved_on_datetime, move_type)
VALUES (23010, 23001, 23001, coords2position(52.22968, 21.01223), 'zzs4t8a', '2020-08-01 10:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.waypoints WHERE waypoint_code = 'ZZS4T8A'), 1::bigint, 'insert creates a waypoint row using uppercase code');
SELECT is((SELECT source FROM stats.waypoints WHERE waypoint_code = 'ZZS4T8A'), 'UK'::bpchar, 'new move-driven waypoint is recorded with UK source');
SELECT is((SELECT visit_count FROM stats.gk_cache_visits gv JOIN stats.waypoints w ON w.id = gv.waypoint_id WHERE gv.gk_id = 23001 AND w.waypoint_code = 'ZZS4T8A'), 1::bigint, 'insert creates a gk_cache_visits row');
SELECT is((SELECT visit_count FROM stats.user_cache_visits uv JOIN stats.waypoints w ON w.id = uv.waypoint_id WHERE uv.user_id = 23001 AND w.waypoint_code = 'ZZS4T8A'), 1::bigint, 'insert with author creates a user_cache_visits row');

INSERT INTO gk_moves (id, geokret, username, position, waypoint, moved_on_datetime, move_type)
VALUES (23011, 23002, 'anon-waypoint-cache', coords2position(50.07550, 14.43780), 'zzs4t8b', '2020-08-01 11:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.user_cache_visits uv JOIN stats.waypoints w ON w.id = uv.waypoint_id WHERE w.waypoint_code = 'ZZS4T8B'), 0::bigint, 'anonymous moves skip user_cache_visits');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23012, 23003, 23001, coords2position(48.20820, 16.37380), '2020-08-01 12:00:00+00', 2);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_cache_visits WHERE gk_id = 23003), 0::bigint, 'comment moves skip cache visit aggregation');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23013, 23004, 23001, coords2position(48.85660, 2.35220), '2020-08-01 13:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_cache_visits WHERE gk_id = 23004), 0::bigint, 'NULL waypoint moves skip cache visits');

INSERT INTO gk_moves (id, geokret, author, position, waypoint, moved_on_datetime, move_type)
VALUES (23014, 23001, 23001, coords2position(52.52000, 13.40500), 'ZZS4T8A', '2020-08-01 14:00:00+00', 0);

SELECT is((SELECT visit_count FROM stats.gk_cache_visits gv JOIN stats.waypoints w ON w.id = gv.waypoint_id WHERE gv.gk_id = 23001 AND w.waypoint_code = 'ZZS4T8A'), 2::bigint, 'two qualifying moves on the same GK and waypoint reconcile to visit_count 2');

UPDATE gk_moves
SET waypoint = 'ZZS4T8D'
WHERE id = 23014;

SELECT is((SELECT visit_count FROM stats.gk_cache_visits gv JOIN stats.waypoints w ON w.id = gv.waypoint_id WHERE gv.gk_id = 23001 AND w.waypoint_code = 'ZZS4T8A'), 1::bigint, 'update repairs the old gk_cache_visits aggregate exactly');
SELECT is((SELECT visit_count FROM stats.gk_cache_visits gv JOIN stats.waypoints w ON w.id = gv.waypoint_id WHERE gv.gk_id = 23001 AND w.waypoint_code = 'ZZS4T8D'), 1::bigint, 'update creates the new gk_cache_visits aggregate exactly');

DELETE FROM gk_moves WHERE id = 23010;

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_cache_visits gv JOIN stats.waypoints w ON w.id = gv.waypoint_id WHERE gv.gk_id = 23001 AND w.waypoint_code = 'ZZS4T8A'), 0::bigint, 'delete removes empty gk_cache_visits aggregates');
SELECT is((SELECT COUNT(*)::bigint FROM stats.user_cache_visits uv JOIN stats.waypoints w ON w.id = uv.waypoint_id WHERE uv.user_id = 23001 AND w.waypoint_code = 'ZZS4T8A'), 0::bigint, 'delete removes empty user_cache_visits aggregates');

SELECT * FROM finish();
ROLLBACK;
