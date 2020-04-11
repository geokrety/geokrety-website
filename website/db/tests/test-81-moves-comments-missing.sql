-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(24);

\set nice '\'0101000020E61000000AD7A3703D0A1D409A99999999D94540\''
\set comment 0
\set missing 1
\set move_type_dropped 0
\set move_type_grabbed 1
\set move_type_comment 2
\set move_type_seen 3
\set move_type_archived 4
\set move_type_dipped 5


INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- insert `missing` should mark GeoKret missing
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (1, 1, :nice, '2020-04-07 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (1, 1, 'missing!', :missing);
SELECT is(missing, TRUE, 'GeoKret marked as missing') from gk_geokrety WHERE id = 1::bigint;

-- update `missing` to comment should remove GeoKret missing status
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (2, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (2, 2, :nice, '2020-04-07 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (2, 2, 'missing!', :missing);
UPDATE "gk_moves_comments" SET type=:comment WHERE id=2::bigint;
SELECT is(missing, FALSE, 'GeoKret is not missing anymore') from gk_geokrety WHERE id = 2::bigint;
UPDATE "gk_moves_comments" SET type=:missing WHERE id=2::bigint;
SELECT is(missing, TRUE, 'GeoKret is now missing again') from gk_geokrety WHERE id = 2::bigint;

-- delete `missing` should remove GeoKret missing status
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (3, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (3, 3, :nice, '2020-04-07 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (3, 3, 'missing!', :missing);
DELETE FROM "gk_moves_comments" WHERE id=3::bigint;
SELECT is(missing, FALSE, 'GeoKret marked as missing - delete') from gk_geokrety WHERE id = 3::bigint;

-- multiple missing comments can be added
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (4, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (4, 4, :nice, '2020-04-07 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (4, 4, 'missing!', :missing);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (5, 4, 'missing!', :missing);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (6, 4, 'missing!', :missing);
SELECT is(missing, TRUE, 'GeoKret marked as missing - insert') from gk_geokrety WHERE id = 4::bigint;

-- some moves types don't support missing status -- (theoritically in cache)
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (5, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (5, 5, :nice, '2020-04-07 00:00:00+00', :move_type_dropped);
SELECT lives_ok($$INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (7, 5, 'missing!', 1)$$);

INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (6, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (6, 6, :nice, '2020-04-07 00:00:00+00', :move_type_seen);
SELECT lives_ok($$INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (8, 6, 'missing!', 1)$$);

INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (7, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "moved_on_datetime", "move_type") VALUES (7, 7, '2020-04-07 00:00:00+00', :move_type_grabbed);
SELECT throws_ok($$INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (9, 7, 'missing!', 1)$$);

INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (8, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "moved_on_datetime", "move_type") VALUES (8, 8, '2020-04-07 00:00:00+00', :move_type_comment);
SELECT throws_ok($$INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (10, 8, 'missing!', 1)$$);

INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (9, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "moved_on_datetime", "move_type") VALUES (9, 9, '2020-04-07 00:00:00+00', :move_type_archived);
SELECT throws_ok($$INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (11, 9, 'missing!', 1)$$);

INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (10, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (10, 10, :nice, '2020-04-07 00:00:00+00', :move_type_dipped);
SELECT throws_ok($$INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (12, 10, 'missing!', 1)$$);

-- adding new move reset missing
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (11, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (11, 11, :nice, '2020-04-07 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (13, 11, 'missing!', :missing);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (12, 11, :nice, '2020-04-08 00:00:00+00', :move_type_dropped);
SELECT is(missing, FALSE, 'adding new move reset missing - dropped') from gk_geokrety WHERE id = 11::bigint;

UPDATE "gk_moves" SET move_type=:move_type_grabbed, position=NULL WHERE id=12::bigint;
SELECT is(missing, FALSE, 'adding new move reset missing - grabbed') from gk_geokrety WHERE id = 11::bigint;
UPDATE "gk_moves" SET move_type=:move_type_comment, position=NULL WHERE id=12::bigint;
SELECT is(missing, TRUE, 'adding new move reset missing - comment') from gk_geokrety WHERE id = 11::bigint;
UPDATE "gk_moves" SET move_type=:move_type_seen, position=:nice WHERE id=12::bigint;
SELECT is(missing, FALSE, 'adding new move reset missing - seen') from gk_geokrety WHERE id = 11::bigint;
UPDATE "gk_moves" SET move_type=:move_type_archived, position=NULL WHERE id=12::bigint;
SELECT is(missing, FALSE, 'adding new move reset missing - archived') from gk_geokrety WHERE id = 11::bigint;
UPDATE "gk_moves" SET move_type=:move_type_dipped, position=:nice WHERE id=12::bigint;
SELECT is(missing, FALSE, 'adding new move reset missing - dipped') from gk_geokrety WHERE id = 11::bigint;

-- moving move reset missing
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (12, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (13, 12, :nice, '2020-04-08 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (14, 12, :nice, '2020-04-09 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (14, 14, 'missing!', :missing);
UPDATE "gk_moves" SET moved_on_datetime='2020-04-07 00:00:00+00'::timestamp with time zone WHERE id=14::bigint;
SELECT is(missing, FALSE, 'moving move reset missing') from gk_geokrety WHERE id = 12::bigint;
DELETE FROM "gk_moves" WHERE id=13::bigint;
SELECT is(missing, TRUE, 'delete move re-set missing') from gk_geokrety WHERE id = 12::bigint;

-- missing can only be added on last_move
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (13, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (15, 13, :nice, '2020-04-07 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (16, 13, :nice, '2020-04-08 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (17, 13, :nice, '2020-04-09 00:00:00+00', :move_type_dropped);
SELECT throws_ok($$INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (15, 15, 'missing!', 1)$$);
SELECT throws_ok($$INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (16, 16, 'missing!', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (17, 17, 'missing!', 1)$$);


-- delete move which has `missing` also remove the missing status
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (14, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (18, 14, :nice, '2020-04-07 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (19, 14, :nice, '2020-04-08 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (18, 19, 'missing!', :missing);
DELETE FROM "gk_moves" WHERE id=19::bigint;
SELECT is(missing, FALSE, 'delete move which has `missing` also remove the missing status') from gk_geokrety WHERE id = 14::bigint;

-- delete move which has `missing` doesn't remove missing status if next move has a missing comment
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (15, 'test', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (20, 15, :nice, '2020-04-07 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (19, 20, 'missing!', :missing);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (21, 15, :nice, '2020-04-08 00:00:00+00', :move_type_dropped);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (20, 21, 'missing!', :missing);
DELETE FROM "gk_moves" WHERE id=21::bigint;
SELECT is(missing, TRUE, 'delete move which has `missing` does not remove missing status if next move has a missing comment') from gk_geokrety WHERE id = 15::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
