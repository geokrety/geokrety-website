-- Start transaction and plan the tests.

BEGIN;

SELECT * FROM no_plan();
-- SELECT plan(15);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- token is automatically added
INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip") VALUES (1, 1, '127.0.0.1');
SELECT is(LENGTH(token), 42, 'token is automatically added') FROM gk_account_activation WHERE id = 1::bigint;

-- Manual
INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "token") VALUES (2, 1, '127.0.0.1', 'TOKEN');
SELECT is(token, 'TOKEN', 'Token can be manually inserted') FROM gk_account_activation WHERE id = 2::bigint;

-- Reset
INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "token") VALUES (3, 1, '127.0.0.1', 'TOKEN');
UPDATE "gk_account_activation" SET token=NULL WHERE id=3;
SELECT isnt(token, 'TOKEN', 'Reset') FROM gk_account_activation WHERE id = 3::bigint;
SELECT isnt(token, NULL, 'Not null') FROM gk_account_activation WHERE id = 3::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
