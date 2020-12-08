-- Start transaction and plan the tests.

BEGIN;

SELECT * FROM no_plan();
-- SELECT plan(15);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 1', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'TEST 1', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (4, ' TEST 1', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (5, ' TEST 1 ', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (6, 'TEST  1', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (7, 'test        1', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (8, 'tESt        1', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');

-- nbsp u+00A0
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (9, 'test 1', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');
-- carriage return u+0D
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (10, 'test
1', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');
-- tab u+0009
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (11, 'test	1', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');
-- FIGURE SPACE u+2007
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (12, 'test 1', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');

-- Trim
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (13, ' test 1', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (14, 'test 1 ', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (15, ' test 1 ', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (16, '   test  1   ', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');

INSERT INTO "gk_users" ("id", "username", "registration_ip", "_email") VALUES (17, 'test 2', '127.0.0.1', 'nobody@geokrety.org');
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (18, 'nobody@geokrety.org', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_email_uniq"');

SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (19, 'nobody2@geokrety.org', '127.0.0.1')$$);
SELECT lives_ok($$UPDATE "gk_users" SET "username"='nobody2@geokrety.org' WHERE id=19$$);
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (20, 'nobody2@geokrety.org', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_username_uniq"');

INSERT INTO "gk_users" ("id", "username", "registration_ip", "_email") VALUES (21, 'test 3', '127.0.0.1', 'nobody+3@geokrety.org');
SELECT lives_ok($$UPDATE "gk_users" SET "username"='nobody+3bis@geokrety.org' WHERE id=21$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
