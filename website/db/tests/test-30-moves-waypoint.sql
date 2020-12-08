-- Start transaction and plan the tests.

BEGIN;

SELECT * FROM no_plan();
-- SELECT plan(6);
\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set move_type_dropped 0
\set move_type_grabbed 1
\set move_type_comment 2
\set move_type_seen 3
\set move_type_archived 4
\set move_type_dipped 5

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test 1', 0, '2020-04-07 00:00:00+00');

-- waypoint may be null
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :nice, '2020-04-07 01:00:00+00', :move_type_dropped);
SELECT is(waypoint, NULL, 'may be null') from gk_moves WHERE id = 1::bigint;

-- waypoint cannot be empty
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type", "waypoint") VALUES (2,, 1 1, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2020-04-07 02:00:00+00', :move_type_dropped, '')$$);

-- waypoint accepted only for moves types with coordinates
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (3, 1, 1, '2020-04-07 03:00:00+00', 0, 'GC5BRQK', '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint") VALUES (4, 1, 1, '2020-04-07 04:00:00+00', 1, 'GC5BRQK')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint") VALUES (5, 1, 1, '2020-04-07 05:00:00+00', 2, 'GC5BRQK')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (6, 1, 1, '2020-04-07 06:00:00+00', 3, 'GC5BRQK', '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint") VALUES (7, 1, 1, '2020-04-07 07:00:00+00', 4, 'GC5BRQK')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (8, 1, 1, '2020-04-07 08:00:00+00', 5, 'GC5BRQK', '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);

-- waypoint will be saved uppercase
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type", "waypoint") VALUES (9, 1, 1, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2020-04-07 09:00:00+00', :move_type_dropped, 'gc5brqk')$$);
SELECT is(waypoint, 'GC5BRQK', 'will be saved uppercase') from gk_moves WHERE id = 9::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
