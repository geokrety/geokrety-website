-- Start transaction and plan the tests.

BEGIN;
SELECT plan(12);
-- SELECT * FROM no_plan();

--GeoKrety
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (2, 'test 2', 0, '2020-04-07 00:00:00+00', 1);
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (3, 'test 3', 0, '2020-04-07 00:00:00+00', 2);
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (4, 'test 4', 0, '2020-04-07 00:00:00+00', 1);

-- Only Owner can archive
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (1, 1, 1, '2020-04-07 01:00:00+00', 4);$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (2, 1, 2, '2020-04-07 02:00:00+00', 4);$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (3, 2, 1, '2020-04-07 03:00:00+00', 4);$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (4, 2, 2, '2020-04-07 04:00:00+00', 4);$$);

-- Check against anonymous logs
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "username", "moved_on_datetime", "move_type") VALUES (5, 1, 'someone', '2020-04-07 05:00:00+00', 4);$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "username", "moved_on_datetime", "move_type") VALUES (6, 2, 'someone', '2020-04-07 06:00:00+00', 4);$$);


-- Update to someone else GK
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (7, 2, 1, '2020-04-07 07:00:00+00', 4);$$);
SELECT throws_ok($$UPDATE "gk_moves" SET "geokret"=3 WHERE id=7::bigint;$$);

-- Update to another owned GK
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (8, 2, 1, '2020-04-07 08:00:00+00', 4);$$);
SELECT lives_ok($$UPDATE "gk_moves" SET "geokret"=4 WHERE id=8::bigint;$$);

-- Update to un-hold GK
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (9, 2, 1, '2020-04-07 09:00:00+00', 4);$$);
SELECT throws_ok($$UPDATE "gk_moves" SET "geokret"=1 WHERE id=9::bigint;$$);


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
