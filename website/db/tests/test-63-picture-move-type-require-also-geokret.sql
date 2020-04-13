-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(7);
\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''

INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (2, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_users" ("id", "username", "registration_ip", "secid") VALUES (1, 'test', '127.0.0.1', 'qwertyuiop');
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :nice, '2020-04-07 01:00:00+00', 0);

SELECT lives_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "type") VALUES (1, 1, 1, NULL, 1)$$);
SELECT is(geokret, 1::bigint , 'geokret is set automatically') from gk_pictures WHERE id = 1::bigint;

SELECT lives_ok($$INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "type") VALUES (2, 1, 1, 2, 1)$$);
SELECT is(geokret, 1::bigint , 'geokret cannot be overridden') from gk_pictures WHERE id = 2::bigint;

INSERT INTO "gk_pictures" ("id",  "author", "user", "type") VALUES (3, 1, 1, 2);
SELECT is(geokret, NULL , 'no geokret for user pictures') from gk_pictures WHERE id = 3::bigint;
UPDATE "gk_pictures" SET "move"=1, "user"=NULL, "type"=1 WHERE id = 3::bigint;
SELECT is(geokret, 1::bigint , 'geokret is set automatically') from gk_pictures WHERE id = 3::bigint;


-- Changing moved GeoKret must update the picture reference
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (2, 1, 1, :nice, '2020-04-07 02:00:00+00', 0);
INSERT INTO "gk_pictures" ("id",  "author", "move", "type") VALUES (4, 1, 2, 1);
UPDATE "gk_moves" set geokret=2 WHERE id = 2::bigint;
SELECT is(geokret, 2::bigint , 'geokret must be updated') from gk_pictures WHERE id = 4::bigint;


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
