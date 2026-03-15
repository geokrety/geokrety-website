BEGIN;
SELECT plan(13);

SELECT has_table('stats', 'waypoints', 'stats.waypoints table exists');
SELECT col_is_pk('stats', 'waypoints', 'id', 'waypoints primary key is id');
SELECT col_type_is('stats', 'waypoints', 'waypoint_code', 'character varying(20)', 'waypoint_code column is varchar(20)');
SELECT col_type_is('stats', 'waypoints', 'source', 'character(2)', 'source column is char(2)');
SELECT col_type_is('stats', 'waypoints', 'country', 'character(2)', 'country column is char(2)');
SELECT col_is_null('stats', 'waypoints', 'lat', 'lat is nullable');
SELECT ok((SELECT EXISTS (SELECT 1 FROM pg_constraint WHERE connamespace = 'stats'::regnamespace AND conname = 'uq_waypoints_code')), 'uq_waypoints_code constraint exists');
SELECT ok((SELECT EXISTS (SELECT 1 FROM pg_constraint WHERE connamespace = 'stats'::regnamespace AND conname = 'chk_waypoints_source')), 'chk_waypoints_source constraint exists');
SELECT is((SELECT COUNT(*)::bigint FROM stats.waypoints WHERE waypoint_code = 'S4T01NULLS'), 0::bigint, 'fixture waypoint code is absent before insert');
SELECT lives_ok($$
  INSERT INTO stats.waypoints (waypoint_code, lat, lon, country)
  VALUES ('S4T01NULLS', NULL, NULL, NULL)
$$, 'waypoints accepts NULL coordinates and country');
SELECT is((SELECT source FROM stats.waypoints WHERE waypoint_code = 'S4T01NULLS'), 'UK'::bpchar, 'source defaults to UK');
SELECT throws_ok($$
  INSERT INTO stats.waypoints (waypoint_code, source)
  VALUES ('S4T01BAD', 'ZZ')
$$, '23514', NULL, 'invalid source is rejected');
SELECT throws_ok($$
  INSERT INTO stats.waypoints (waypoint_code) VALUES ('S4T01NULLS')
$$, '23505', NULL, 'duplicate waypoint_code is rejected');

SELECT * FROM finish();
ROLLBACK;
