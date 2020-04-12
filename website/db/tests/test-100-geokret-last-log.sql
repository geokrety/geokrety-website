-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(12);
\set nice '\'0101000020E61000000AD7A3703D0A1D409A99999999D94540\''
\set move_type_dropped 0
\set move_type_grabbed 1
\set move_type_comment 2
\set move_type_seen 3
\set move_type_archived 4
\set move_type_dipped 5

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- geokret last_log updated
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
SELECT is(last_log, 1::bigint, 'geokret last_log updated - 1') from gk_geokrety WHERE id = 1::bigint;
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (2, 1, 1, :nice, '2020-04-08 00:00:00+00', 0);
SELECT is(last_log, 2::bigint, 'geokret last_log updated - 2') from gk_geokrety WHERE id = 1::bigint;
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (3, 1, 1, :nice, '2020-04-07 12:00:00+00', 0);
SELECT is(last_log, 2::bigint, 'Added as older move has no effects') from gk_geokrety WHERE id = 1::bigint;
UPDATE "gk_moves" SET moved_on_datetime='2020-04-09 00:00:00+00'::timestamptz WHERE id=3::bigint;
SELECT is(last_log, 3::bigint, 'change move date updates geokret last_log') from gk_geokrety WHERE id = 1::bigint;
DELETE FROM "gk_moves" WHERE id=3::bigint;
SELECT is(last_log, 2::bigint, 'delete move updates geokret last_log') from gk_geokrety WHERE id = 1::bigint;

-- no move is NULL
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (2, 'test', 0);
SELECT is(last_log, NULL, 'no move is null') from gk_geokrety WHERE id = 2::bigint;
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (4, 2, 1, '2020-04-07 00:00:00+00', :move_type_comment);
SELECT is(last_log, 4::bigint, 'comment null') from gk_geokrety WHERE id = 2::bigint;
DELETE FROM "gk_moves" WHERE id=4::bigint;
SELECT is(last_log, NULL, 'removed moves is null') from gk_geokrety WHERE id = 2::bigint;

-- changing GeoKret updates old/new GeoKret last_log
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (3, 'test', 0);
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (4, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (5, 3, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (6, 3, 1, :nice, '2020-04-08 00:00:00+00', 0);
UPDATE "gk_moves" SET geokret=4 WHERE id=6::bigint;
SELECT is(last_log, 5::bigint, 'Old GeoKret new position - 1') from gk_geokrety WHERE id = 3::bigint;
SELECT is(last_log, 6::bigint, 'New GeoKret new position - 2') from gk_geokrety WHERE id = 4::bigint;

DELETE FROM "gk_moves" WHERE id=5::bigint;
SELECT is(last_log, NULL, 'Old GeoKret new position - 3') from gk_geokrety WHERE id = 3::bigint;
SELECT is(last_log, 6::bigint, 'New GeoKret new position - 4') from gk_geokrety WHERE id = 4::bigint;




-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
