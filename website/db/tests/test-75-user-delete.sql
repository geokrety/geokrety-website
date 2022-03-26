-- Start transaction and plan the tests.

BEGIN;

SELECT * FROM no_plan();
-- SELECT plan(22);

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set move_type_dropped 0
\set move_type_grabbed 1

\set picture_type_geokret 0
\set picture_type_move 1
\set picture_type_user 2

\set move_comment_type_comment 0
\set move_comment_type_missing 1


-- basic test
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'username1', '127.0.0.1');
SELECT lives_ok($$DELETE FROM "gk_users" WHERE id=1;$$);

-- unaffected by others GeoKrety
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'username2', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (2, 'test 2', 0, '2022-03-14 00:00:00+00');
SELECT lives_ok($$DELETE FROM "gk_users" WHERE id = 2;$$);
SELECT is(count(*), 1::bigint) from gk_geokrety WHERE id = 2::bigint;

-- owned GeoKrety are detached from account
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'username3', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (3, 'test 3', 0, '2022-03-14 00:00:00+00', 3);
SELECT lives_ok($$DELETE FROM "gk_users" WHERE id = 3;$$);
SELECT is(count(*), 1::bigint) from gk_geokrety WHERE id = 3::bigint;
SELECT is(owner, NULL) from gk_geokrety WHERE id = 3::bigint;
SELECT is(holder, NULL) from gk_geokrety WHERE id = 3::bigint;

-- held GeoKrety are detached from account
-- and moves are marked as 'Deleted user'
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (4, 'username4', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (5, 'username5', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (4, 'test 4', 0, '2022-03-14 00:00:00+00', 4);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", comment) VALUES (4, 4, 5, '2022-03-14 00:00:00+00', :move_type_grabbed, 'Comment 4');

SELECT is(owner, 4::bigint) from gk_geokrety WHERE id = 4::bigint;
SELECT is(holder, 5::bigint) from gk_geokrety WHERE id = 4::bigint;

SELECT lives_ok($$DELETE FROM "gk_users" WHERE id = 5;$$);
SELECT is(count(*), 1::bigint) from gk_geokrety WHERE id = 4::bigint;
SELECT is(owner, 4::bigint) from gk_geokrety WHERE id = 4::bigint;
SELECT is(holder, NULL) from gk_geokrety WHERE id = 4::bigint;
SELECT is(author, NULL) from gk_moves WHERE id = 4::bigint;
SELECT is(username, 'Deleted user') from gk_moves WHERE id = 4::bigint;
SELECT is(comment, 'Comment 4') from gk_moves WHERE id = 4::bigint;

-- user avatar pictures are deleted
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (6, 'username6', '127.0.0.1');
INSERT INTO "gk_pictures" ("id",  "author", "user", "type") VALUES (6, 6, 6, :picture_type_user);
SELECT is(count(*), 1::bigint) from gk_pictures WHERE id = 6::bigint;
SELECT lives_ok($$DELETE FROM "gk_users" WHERE id = 6;$$);
SELECT is(count(*), 0::bigint) from gk_pictures WHERE id = 6::bigint;

-- geokrety avatar pictures are detached
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (7, 'username7', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (7, 'test 7', 0, '2022-03-14 00:00:00+00', 7);
INSERT INTO "gk_pictures" ("id",  "author", "geokret", "type") VALUES (7, 7, 7, :picture_type_geokret);
SELECT lives_ok($$DELETE FROM "gk_users" WHERE id = 7;$$);
SELECT is(author, NULL) from gk_pictures WHERE id = 7::bigint;

-- move pictures are detached
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (8, 'username8', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (8, 'test 8', 0, '2022-03-14 00:00:00+00', 8);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (8, 8, 8, '2022-03-14 00:00:00+00', :move_type_grabbed);
INSERT INTO "gk_pictures" ("id",  "author", "move", "type") VALUES (8, 8, 8, :picture_type_move);
SELECT lives_ok($$DELETE FROM "gk_users" WHERE id = 8;$$);
SELECT is(author, NULL) from gk_pictures WHERE id = 8::bigint;

--
-- Delete using function to anonymize comments
--

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (30, 'username30', '127.0.0.1');
SELECT lives_ok($$SELECT delete_user(30, TRUE);$$);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (31, 'username31', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (32, 'username32', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (31, 'test 31', 0, '2022-03-14 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "comment") VALUES (31, 31, 31, '2022-03-14 00:00:00+00', :move_type_grabbed, 'Comment 31');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "comment") VALUES (32, 31, 32, '2022-03-14 00:00:01+00', :move_type_grabbed, 'Comment 32');
SELECT lives_ok($$SELECT delete_user(31, TRUE);$$);
SELECT is(comment, 'Comment suppressed') from gk_moves WHERE id = 31::bigint;
SELECT is(comment, 'Comment 32') from gk_moves WHERE id = 32::bigint;


INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (33, 'username33', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (34, 'username34', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (33, 'test 33', 0, '2022-03-14 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", position, "comment") VALUES (33, 33, 33, '2022-03-14 00:00:00+00', :move_type_dropped, :nice, 'Comment 33');
INSERT INTO "gk_moves_comments" (id, type, move, author, content) VALUES (33, :move_comment_type_comment, 33, 33, 'Comment 33');
INSERT INTO "gk_moves_comments" (id, type, move, author, content) VALUES (34, :move_comment_type_missing, 33, 33, 'Comment 34');
INSERT INTO "gk_moves_comments" (id, type, move, author, content) VALUES (35, :move_comment_type_comment, 33, 34, 'Comment 35');
INSERT INTO "gk_moves_comments" (id, type, move, author, content) VALUES (36, :move_comment_type_missing, 33, 34, 'Comment 36');
SELECT lives_ok($$SELECT delete_user(33, TRUE);$$);
SELECT is(content, 'Comment suppressed') from gk_moves_comments WHERE id = 33::bigint;
SELECT is(content, 'Comment suppressed') from gk_moves_comments WHERE id = 34::bigint;
SELECT is(content, 'Comment 35') from gk_moves_comments WHERE id = 35::bigint;
SELECT is(content, 'Comment 36') from gk_moves_comments WHERE id = 36::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
;
