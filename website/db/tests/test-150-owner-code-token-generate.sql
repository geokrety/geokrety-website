-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(6);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);

-- token is automatically added
INSERT INTO "gk_owner_codes" ("id", "geokret") VALUES (1, 1);
SELECT is(LENGTH(token), 6, 'token is automatically added') FROM gk_owner_codes WHERE id = 1::bigint;

-- Manual
INSERT INTO "gk_owner_codes" ("id", "geokret", "token") VALUES (2, 1, 'TOKEN1');
SELECT is(token, 'TOKEN1', 'Token can be manually inserted') FROM gk_owner_codes WHERE id = 2::bigint;

-- can be updated
INSERT INTO "gk_owner_codes" ("id", "geokret", "token") VALUES (3, 1, 'TOKEN2');
SELECT lives_ok($$UPDATE "gk_owner_codes" SET token = NULL WHERE id = 3::bigint$$);
SELECT isnt(token, 'TOKEN2', 'Token can be reseted') FROM gk_owner_codes WHERE id = 3::bigint;
SELECT lives_ok($$UPDATE "gk_owner_codes" SET token = 'TOKEN3' WHERE id = 3::bigint$$);

-- tokens are unique
INSERT INTO "gk_owner_codes" ("id", "geokret", "token") VALUES (4, 1, 'TOKEN3');
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "token") VALUES (4, 1, 'TOKEN3')$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
