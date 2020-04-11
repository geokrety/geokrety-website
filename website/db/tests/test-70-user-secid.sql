-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(6);

-- Automatic
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
SELECT is(LENGTH(secid), 42, 'Automatic add secid') FROM gk_users WHERE id = 1::bigint;

-- Manual
INSERT INTO "gk_users" ("id", "username", "registration_ip", "secid") VALUES (2, 'test 2', '127.0.0.1', 'SECRETID');
SELECT is(secid, 'SECRETID', 'Code can be manually inserted') FROM gk_users WHERE id = 2::bigint;

-- Uniq
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip", "secid") VALUES (3, 'test 3', '127.0.0.1', 'SECRETID')$$, 23505, 'duplicate key value violates unique constraint "gk_users_uniq_secid"');

-- Reset
INSERT INTO "gk_users" ("id", "username", "registration_ip", "secid") VALUES (4, 'test 4', '127.0.0.1', 'SECRETID2');
UPDATE "gk_users" SET secid=NULL WHERE id=4;
SELECT isnt(secid, 'SECRETID2', 'Set to NULL to generate a new one - 1') FROM gk_users WHERE id = 4::bigint;
SELECT isnt(secid, NULL, 'Set to NULL to generate a new one - 2') from gk_users WHERE id = 4::bigint;

-- Update other untouched
INSERT INTO "gk_users" ("id", "username", "registration_ip", "secid") VALUES (5, 'test 5', '127.0.0.1', 'SECRETID5');
UPDATE "gk_users" SET username='My Name' WHERE id=5;
SELECT is(secid, 'SECRETID5', 'Secid is unmodified') from gk_users WHERE id = 5::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;