-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(15);
\set nice '\'0101000020E61000000AD7A3703D0A1D409A99999999D94540\''
\set move_type_dropped 0
\set move_type_grabbed 1
\set move_type_comment 2
\set move_type_seen 3
\set move_type_archived 4
\set move_type_dipped 5

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- geokret last_position updated
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :nice, '2020-04-07 00:00:00+00', :move_type_dropped);
SELECT is(last_position, 1::bigint, 'geokret last_position updated - 1') from gk_geokrety WHERE id = 1::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (2, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (2, 2, 1, '2020-04-07 00:00:00+00', :move_type_grabbed);
SELECT is(last_position, 2::bigint, 'geokret last_position updated - 2') from gk_geokrety WHERE id = 2::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (3, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (3, 3, 1, '2020-04-07 00:00:00+00', :move_type_comment);
SELECT is(last_position, NULL, 'geokret last_position updated - 3') from gk_geokrety WHERE id = 3::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (4, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (4, 4, 1, :nice, '2020-04-07 00:00:00+00', :move_type_seen);
SELECT is(last_position, 4::bigint, 'geokret last_position updated - 4') from gk_geokrety WHERE id = 4::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (5, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (5, 5, 1, '2020-04-07 00:00:00+00', :move_type_archived);
SELECT is(last_position, 5::bigint, 'geokret last_position updated - 5') from gk_geokrety WHERE id = 5::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (6, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (6, 6, 1, :nice, '2020-04-07 00:00:00+00', :move_type_dipped);
SELECT is(last_position, 6::bigint, 'geokret last_position updated - 6') from gk_geokrety WHERE id = 6::bigint;


-- no move is NULL
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (7, 'test', 0);
SELECT is(last_position, NULL, 'no move is null') from gk_geokrety WHERE id = 7::bigint;
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (7, 7, 1, '2020-04-07 00:00:00+00', :move_type_comment);
SELECT is(last_position, NULL, 'comment null') from gk_geokrety WHERE id = 7::bigint;
UPDATE "gk_moves" SET move_type=:move_type_seen, position=:nice WHERE id=7::bigint;
SELECT is(last_position, 7::bigint, 'comment is set') from gk_geokrety WHERE id = 7::bigint;
DELETE FROM "gk_moves" WHERE id=7::bigint;
SELECT is(last_position, NULL, 'removed moves is null') from gk_geokrety WHERE id = 7::bigint;

-- changing GeoKret updates old/new GeoKret last_position
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (8, 'test', 0);
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (9, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (8, 8, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (9, 9, 1, :nice, '2020-04-08 00:00:00+00', 0);
UPDATE "gk_moves" SET geokret=9 WHERE id=8::bigint;
SELECT is(last_position, NULL, 'Old GeoKret new position - 1') from gk_geokrety WHERE id = 8::bigint;
SELECT is(last_position, 9::bigint, 'New GeoKret new position - 2') from gk_geokrety WHERE id = 9::bigint;
DELETE FROM "gk_moves" WHERE id=9::bigint;
SELECT is(last_position, NULL, 'Old GeoKret new position - 3') from gk_geokrety WHERE id = 8::bigint;
SELECT is(last_position, 8::bigint, 'New GeoKret new position - 4') from gk_geokrety WHERE id = 9::bigint;
DELETE FROM "gk_moves" WHERE id=8::bigint;
SELECT is(last_position, NULL, 'New GeoKret new position - 4') from gk_geokrety WHERE id = 9::bigint;


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
