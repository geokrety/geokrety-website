-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(13);

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''
\set berlin '\'0101000020E61000009A99999999992A400000000000404A40\''
\set warsaw '\'0101000020E610000000000000000035409A99999999194A40\''
\set moscow '\'0101000020E6100000CDCCCCCCCCCC42409A99999999D94B40\''
\set tokyo '\'0101000020E61000006666666666766140CDCCCCCCCCCC4140\''

--GeoKrety
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test 1', 0, '2020-04-07 00:00:00+00');
SELECT is(caches_count, 0, 'Never moved') FROM gk_geokrety WHERE id = 1::bigint;

-- Moves
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (2, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (2, 2, 1, '2020-04-07 00:00:00+00', 0, 'GC5BRQK', :nice);
SELECT is(caches_count, 1, 'move-type 0') FROM gk_geokrety WHERE id = 2::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (3, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (3, 3, 1, '2020-04-07 00:00:00+00', 1);
SELECT is(caches_count, 0, 'move-type 1') FROM gk_geokrety WHERE id = 3::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (4, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (4, 4, 1, '2020-04-07 00:00:00+00', 2);
SELECT is(caches_count, 0, 'move-type 2') FROM gk_geokrety WHERE id = 4::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (5, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (5, 5, 1, '2020-04-07 00:00:00+00', 3, 'GC5BRQK', :nice);
SELECT is(caches_count, 1, 'move-type 3') FROM gk_geokrety WHERE id = 5::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (6, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (6, 6, 1, '2020-04-07 00:00:00+00', 4);
SELECT is(caches_count, 0, 'move-type 4') FROM gk_geokrety WHERE id = 6::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (7, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (7, 7, 1, '2020-04-07 00:00:00+00', 5, 'GC5BRQK', :nice);
SELECT is(caches_count, 1, 'move-type 5') FROM gk_geokrety WHERE id = 7::bigint;

-- Multiple but same place
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (8, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (8, 8, 1, '2020-04-07 00:00:00+00', 0, 'GC5BRQK', :nice);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (9, 8, 1, '2020-04-07 01:00:00+00', 0, 'GC5BRQK', :nice);
SELECT is(caches_count, 1, 'Multiple but same place') FROM gk_geokrety WHERE id = 8::bigint;

-- Multiple but different place
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (18, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (19, 18, 1, '2020-04-07 00:00:00+00', 0, 'GC5BRQK', :nice);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (20, 18, 1, '2020-04-07 01:00:00+00', 0, 'GC5BRQK', :paris);
SELECT is(caches_count, 2, 'v') FROM gk_geokrety WHERE id = 18::bigint;

-- Multiple
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (10, 'test 1', 0, '2020-04-07 01:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (10, 10, 1, '2020-04-07 02:00:00+00', 0, 'GC5BRQK', :nice);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (11, 10, 1, '2020-04-07 03:00:00+00', 1);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (12, 10, 1, '2020-04-07 04:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (13, 10, 1, '2020-04-07 05:00:00+00', 3, 'GC5BRQK', :paris);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (14, 10, 1, '2020-04-07 06:00:00+00', 4);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (15, 10, 1, '2020-04-07 07:00:00+00', 5, 'GC5BRQK', :berlin);
SELECT is(caches_count, 3, 'Multiple') FROM gk_geokrety WHERE id = 10::bigint;


-- Update
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (16, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (17, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (16, 16, 1, '2020-04-07 00:00:00+00', 0, 'GC5BRQK', :nice);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (17, 16, 1, '2020-04-07 01:00:00+00', 0, 'GC5BRQK', :paris);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "waypoint", "position") VALUES (18, 17, 1, '2020-04-07 01:00:00+00', 0, 'GC5BRQK', :nice);
SELECT is(caches_count, 2, 'Update - 0') FROM gk_geokrety WHERE id = 16::bigint;
UPDATE "gk_moves" SET geokret=17, position=:paris WHERE id = 16::bigint;
SELECT is(caches_count, 1, 'Update - 1') FROM gk_geokrety WHERE id = 16::bigint;
SELECT is(caches_count, 2, 'Update - 2') FROM gk_geokrety WHERE id = 17::bigint;


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
