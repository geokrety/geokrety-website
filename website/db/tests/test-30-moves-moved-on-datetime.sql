-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(23);

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set move_type_comment 2

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- created_on_datetime is now by default
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);
SELECT is(born_on_datetime, created_on_datetime, 'born_on_datetime is set to created_on_datetime on create') from gk_geokrety WHERE id = 1::bigint;

-- created_on_datetime can be overridden
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (2, 'test', 0, '2020-04-07 00:00:00+00');
SELECT is(created_on_datetime, '2020-04-07 00:00:00+00'::timestamptz, 'created_on_datetime can be overridden') from gk_geokrety WHERE id = 2::bigint;
SELECT is(born_on_datetime, created_on_datetime, 'born_on_datetime is set to created_on_datetime on create') from gk_geokrety WHERE id = 2::bigint;

-- move before GK birth
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (3, 'test', 0, '2020-04-07 00:00:00+00');
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (1, 3, 1, '2020-04-06 00:00:00+00', 2)$$);

-- move after NOW()
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (4, 'test', 0, '2020-04-07 00:00:00+00');
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (2, 4, 1, '2222-04-06 00:00:00+00', 2)$$);

-- same move on this GK at this datetime
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (5, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (6, 'test', 0, '2020-04-07 00:00:00+00');
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (3, 5, 1, '2020-04-08 00:00:00+00', 1)$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (4, 5, 1, '2020-04-08 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (5, 6, 1, '2020-04-08 00:00:00+00', 1)$$);

-- move in the right range
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (7, 'test', 0, '2020-04-07 00:00:00+00');
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (6, 7, 1, '2020-04-08 00:00:00+00', 2)$$);
SELECT is(COUNT(*) > 0, TRUE, 'move in the right range') from gk_geokrety WHERE id = 7::bigint;

-- update can reuse same date
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (8, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (9, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (7, 8, 1, '2020-04-08 00:00:00+00', 1);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (8, 9, 1, '2020-04-08 00:00:00+00', 1);
SELECT lives_ok($$UPDATE "gk_moves" set moved_on_datetime = '2020-04-08 00:00:00+00'::timestamptz WHERE id = 7::bigint$$);
SELECT throws_ok($$UPDATE "gk_moves" set geokret=9, moved_on_datetime = '2020-04-08 00:00:00+00'::timestamptz WHERE id = 7::bigint$$);

-- GK birth date update must be lower than older move
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (10, 'test', 0, '2024-05-18 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (10, 10, 1, '2024-05-18 01:00:00+00', 2);
SELECT lives_ok($$UPDATE "gk_geokrety" set born_on_datetime = '2024-05-17 00:00:00+00'::timestamptz WHERE id = 10::bigint$$);
SELECT throws_ok($$UPDATE "gk_geokrety" set born_on_datetime = '2024-05-19 00:00:00+00'::timestamptz WHERE id = 10::bigint$$);

-- GK birth date cannot be created after now
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (11, 'test', 0, '2024-05-19 00:00:00+00');
SELECT lives_ok($$UPDATE "gk_geokrety" set born_on_datetime = NOW() WHERE id = 11::bigint$$);
SELECT throws_ok($$UPDATE "gk_geokrety" set born_on_datetime = NOW() + INTERVAL '1 hour' WHERE id = 11::bigint$$);

-- New moves can be posted until the new GK birth
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (12, 'test', 0, '2024-05-19 00:00:00+00');
SELECT lives_ok($$UPDATE "gk_geokrety" set born_on_datetime = '2024-05-10 00:00:00+00' WHERE id = 12::bigint$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (12, 12, 1, '2024-05-10 00:00:00+00', 2)$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (13, 12, 1, '2024-05-09 00:00:00+00', 2)$$);

-- Multiple comments can exists at this datetime
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (13, 'test', 0, '2020-04-07 00:00:00+00');
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (14, 13, 1, '2020-04-08 00:00:00+00', 2)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (15, 13, 1, '2020-04-08 00:00:00+00', 1)$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (16, 13, 1, '2020-04-08 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (17, 13, 1, '2020-04-08 00:00:00+00', 2)$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
