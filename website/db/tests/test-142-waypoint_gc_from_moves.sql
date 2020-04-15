-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(6);

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- Waypoint is inserted
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "waypoint", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :paris, 'GC12345', '2020-04-07 01:00:00+00', 0);
SELECT is(COUNT(*), 1::bigint, 'Waypoint is inserted') FROM gk_waypoints_gc WHERE waypoint = 'GC12345';
SELECT is(lat, 48.8::double precision, 'latitude is inserted') FROM gk_waypoints_gc WHERE waypoint = 'GC12345';
SELECT is(lon, 2.3::double precision, 'longitude is inserted') FROM gk_waypoints_gc WHERE waypoint = 'GC12345';

-- nothing if waypoint is EMPTY
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (2, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (2, 2, 1, :paris, '2020-04-07 02:00:00+00', 0);
SELECT is(COUNT(*), 0::bigint, 'Waypoint is inserted') FROM gk_waypoints_gc WHERE waypoint = NULL; -- TODO how to preciselly test that case?

-- nothing if waypoint already exists
-- TODO: may change in the future, what about updating?
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (3, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "waypoint", "moved_on_datetime", "move_type") VALUES (3, 1, 1, :paris, 'GC12345', '2020-04-07 03:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "waypoint", "moved_on_datetime", "move_type") VALUES (4, 1, 1, :nice, 'GC12345', '2020-04-07 04:00:00+00', 0);
SELECT is(position, :paris, 'Waypoint is not updated') FROM gk_waypoints_gc WHERE waypoint = 'GC12345';

-- skip country is not found
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (4, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "waypoint", "moved_on_datetime", "move_type") VALUES (5, 4, 1, 0, 0, 'GC45678', '2020-04-07 02:00:00+00', 0);
SELECT is(country, NULL, 'Waypoint is inserted') FROM gk_waypoints_gc WHERE waypoint = 'GC45678';

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
