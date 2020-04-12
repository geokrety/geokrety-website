-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(8);

\set nice '\'0101000020E61000000AD7A3703D0A1D409A99999999D94540\''
\set move_type_comment 2

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- created_on_datetime can be overridden
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test', 0, '2020-04-07 00:00:00+00');
SELECT is(created_on_datetime, '2020-04-07 00:00:00+00'::timestamptz, 'created_on_datetime can be overridden') from gk_geokrety WHERE id = 1::bigint;

-- move before GK birth
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (2, 'test', 0, '2020-04-07 00:00:00+00');
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (1, 2, 1, '2020-04-06 00:00:00+00', 2)$$);

-- move after NOW()
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (3, 'test', 0, '2020-04-07 00:00:00+00');
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (2, 3, 1, '2222-04-06 00:00:00+00', 2)$$);

-- same move on this GK at this datetime
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (4, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (5, 'test', 0, '2020-04-07 00:00:00+00');
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (3, 4, 1, '2020-04-08 00:00:00+00', 2)$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (4, 4, 1, '2020-04-08 00:00:00+00', 2)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (5, 5, 1, '2020-04-08 00:00:00+00', 2)$$);

-- move in the right range
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (6, 'test', 0, '2020-04-07 00:00:00+00');
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (6, 6, 1, '2020-04-08 00:00:00+00', 2)$$);
SELECT is(COUNT(*) > 0, TRUE, 'move in the right range') from gk_geokrety WHERE id = 6::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
