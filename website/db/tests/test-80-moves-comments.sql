-- Start transaction and plan the tests.

BEGIN;

SELECT * FROM no_plan();
-- SELECT plan(3);
\set nice '\'0101000020E61000000AD7A3703D0A1D409A99999999D94540\''

INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- moves comments counter incremented decremented on insert
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (1, 1, 'content', 0);
SELECT is(comments_count, 1, 'comments_count incremented - 1') from gk_moves WHERE id = 1::bigint;
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (2, 1, 'content', 0);
SELECT is(comments_count, 2, 'comments_count incremented - 2') from gk_moves WHERE id = 1::bigint;

-- moves comments counter incremented decremented on update
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (2, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (3, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (3, 2, 'content', 0);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (4, 2, 'content', 0);
UPDATE "gk_moves_comments" SET move=3 WHERE id=3::bigint;
SELECT is(comments_count, 1, 'update - comments_count decremented') from gk_moves WHERE id = 2::bigint;
SELECT is(comments_count, 1, 'update - comments_count incremented - 1') from gk_moves WHERE id = 3::bigint;

-- moves comments counter untouched on simple edit
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (4, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (5, 4, 'content', 0);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (6, 4, 'content', 0);
UPDATE "gk_moves_comments" SET content='new content' WHERE id=5::bigint;
SELECT is(comments_count, 2, 'update - comments_count untouched') from gk_moves WHERE id = 4::bigint;

-- moves comments counter decremented on delete
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (5, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (7, 5, 'content', 0);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (8, 5, 'content', 0);
DELETE FROM "gk_moves_comments" WHERE id=7::bigint;
SELECT is(comments_count, 1, 'delete move comments - 1') from gk_moves WHERE id = 5::bigint;
DELETE FROM "gk_moves_comments" WHERE id=8::bigint;
SELECT is(comments_count, 0, 'delete move comments - 2') from gk_moves WHERE id = 5::bigint;

-- geokret cannot be overridden
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (6, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (2, 'test', 0);
INSERT INTO "gk_moves_comments" ("id", "move", "geokret", "content", "type") VALUES (9, 6, 2, 'content', 0);
SELECT is(geokret, 1::bigint, 'geokret cannot be overridden - 1') from gk_moves_comments WHERE id = 9::bigint;
UPDATE "gk_moves_comments" SET geokret=2::bigint WHERE id=9::bigint;
SELECT is(geokret, 1::bigint, 'geokret cannot be overridden - 2') from gk_moves_comments WHERE id = 9::bigint;
UPDATE "gk_moves_comments" SET geokret=NULL WHERE id=9::bigint;
SELECT is(geokret, 1::bigint, 'geokret cannot be overridden - 3') from gk_moves_comments WHERE id = 9::bigint;

-- geokret link is automatically managed
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (7, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (10, 7, 'content', 0);
SELECT is(geokret, 1::bigint, 'geokret is automatically managed') from gk_moves_comments WHERE id = 10::bigint;

-- delete move also deletes comments
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (8, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_moves_comments" ("id", "move", "content", "type") VALUES (11, 8, 'content', 0);
DELETE FROM "gk_moves" WHERE id=8::bigint;
SELECT is(count(*), 0::bigint, 'delete move deletes moves comments') from gk_moves_comments WHERE move = 8::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
