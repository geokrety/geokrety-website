-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(6);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- created_on_datetime can be overridden
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test', 0, '2020-04-07 00:00:00+00');
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "username", "move_type", "moved_on_datetime") VALUES (1, 1, 1, NULL, 2, '2020-04-07 01:00:00+00')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "username", "move_type", "moved_on_datetime") VALUES (2, 1, 1, '', 2, '2020-04-07 02:00:00+00')$$, 23514, 'new row for relation "gk_moves" violates check constraint "check_author_username"');
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "username", "move_type", "moved_on_datetime") VALUES (2, 1, 1, 'user', 2, '2020-04-07 03:00:00+00')$$, 23514, 'new row for relation "gk_moves" violates check constraint "check_author_username"');

SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "username", "move_type", "moved_on_datetime") VALUES (1, 1, NULL, NULL, 2, '2020-04-07 04:00:00+00')$$, 23514, 'new row for relation "gk_moves" violates check constraint "check_author_username"');
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "username", "move_type", "moved_on_datetime") VALUES (2, 1, NULL, '', 2, '2020-04-07 05:00:00+00')$$, 23514, 'new row for relation "gk_moves" violates check constraint "check_author_username"');
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "username", "move_type", "moved_on_datetime") VALUES (2, 1, NULL, 'user', 2, '2020-04-07 06:00:00+00')$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
