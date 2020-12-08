-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(9);
\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''

INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (2, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_secid") VALUES (1, 'test', '127.0.0.1', 'qwertyuiop');
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 2, 1, :nice, '2020-04-07 01:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (2, 2, 1, :nice, '2020-04-07 02:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (3, 1, 1, :nice, '2020-04-07 03:00:00+00', 0);

-- require uploaded_on_datetime
INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "type") VALUES (1, 1, 1, 2, 1);
SELECT is(pictures_count, 0::smallint , 'uploaded_date is necessary for counting') from gk_moves WHERE id = 1::bigint;
UPDATE "gk_pictures" set uploaded_on_datetime = NOW() WHERE id = 1::bigint;
SELECT is(pictures_count, 1::smallint , 'uploaded_date counting') from gk_moves WHERE id = 1::bigint;
UPDATE "gk_pictures" set uploaded_on_datetime = NULL WHERE id = 1::bigint;
SELECT is(pictures_count, 0::smallint , 'uploaded_date back to 0') from gk_moves WHERE id = 1::bigint;

-- Increment/Decrement
INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "type", "uploaded_on_datetime") VALUES (2, 1, 1, 2, 1, NOW());
SELECT is(pictures_count, 1::smallint , 'Pictures count is incremented 1') from gk_moves WHERE id = 1::bigint;
INSERT INTO "gk_pictures" ("id",  "author", "move", "geokret", "type", "uploaded_on_datetime") VALUES (3, 1, 1, 2, 1, NOW());
SELECT is(pictures_count, 2::smallint , 'Pictures count is incremented 2') from gk_moves WHERE id = 1::bigint;

-- Delete
DELETE FROM "gk_pictures"  WHERE id = 3::bigint;
SELECT is(pictures_count, 1::smallint , 'Pictures count is decremented') from gk_moves WHERE id = 1::bigint;

-- Update change moves id
UPDATE "gk_pictures" set move=2 WHERE id = 2::bigint;
SELECT is(pictures_count, 1::smallint , 'pictures count is now on move 2') from gk_moves WHERE id = 2::bigint;
SELECT is(pictures_count, 0::smallint , 'move 1 has now move now') from gk_moves WHERE id = 1::bigint;

-- Update to another type
UPDATE "gk_pictures" set move=null, geokret=2, type=0 WHERE id = 2::bigint;
SELECT is(pictures_count, 0::smallint , 'Move 2 has no pictures left') from gk_moves WHERE id = 2::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
