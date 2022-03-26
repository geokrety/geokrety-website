-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(5);

\set move_type_grabbed 1

\set picture_type_geokret 0
\set picture_type_move 1
\set picture_type_user 2


-- delete move delete picture
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'username1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (1, 'test 1', 0, '2022-03-14 00:00:00+00', 1);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (1, 1, 1, '2022-03-14 00:00:00+00', :move_type_grabbed);
INSERT INTO "gk_pictures" ("id",  "author", "move", "type") VALUES (1, 1, 1, :picture_type_move);
SELECT lives_ok($$DELETE FROM "gk_moves" WHERE id = 1;$$);
SELECT is(count(*), 0::bigint) from gk_pictures WHERE id = 1::bigint;


-- delete georekty delete move and picture in cascade
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'username2', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (2, 'test 2', 0, '2022-03-14 00:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (2, 2, 2, '2022-03-14 00:00:00+00', :move_type_grabbed);
INSERT INTO "gk_pictures" ("id",  "author", "move", "type") VALUES (2, 2, 2, :picture_type_move);
SELECT lives_ok($$DELETE FROM "gk_geokrety" WHERE id = 2;$$);
SELECT is(count(*), 0::bigint) from gk_moves WHERE id = 2::bigint;
SELECT is(count(*), 0::bigint) from gk_pictures WHERE id = 2::bigint;


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
;
