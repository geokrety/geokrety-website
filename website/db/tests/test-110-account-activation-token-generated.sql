-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(9);

\set TOKEN_UNUSED   0::smallint
\set TOKEN_VALID    1::smallint
-- \set TOKEN_EXPIRED  2::smallint

-- token is automatically added
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip") VALUES (1, 1, '127.0.0.1');
SELECT is(used, :TOKEN_UNUSED, 'used defaults to UNUSED') FROM gk_account_activation WHERE id = 1::bigint;
SELECT is(LENGTH(token), 42, 'token is automatically added') FROM gk_account_activation WHERE id = 1::bigint;

-- Manual
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "token") VALUES (2, 2, '127.0.0.1', 'TOKEN');
SELECT is(token, 'TOKEN', 'Token can be manually inserted') FROM gk_account_activation WHERE id = 2::bigint;

-- token regenered when set to null
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1');
INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "token") VALUES (3, 3, '127.0.0.1', 'TOKEN');
UPDATE "gk_account_activation" SET token=NULL WHERE id=3;
SELECT isnt(token, 'TOKEN', 'Reset') FROM gk_account_activation WHERE id = 3::bigint;
SELECT isnt(token, NULL, 'Not null') FROM gk_account_activation WHERE id = 3::bigint;

-- ip is mandatory
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (4, 'test 4', '127.0.0.1');
SELECT throws_ok($$INSERT INTO "gk_account_activation" ("id", "user", "used") VALUES (4, 4, 0::smallint)$$, 23502, 'null value in column "requesting_ip" of relation "gk_account_activation" violates not-null constraint');

-- only **one** token per user
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (5, 'test 5', '127.0.0.1');
INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip") VALUES (5, 5, '127.0.0.1');
SELECT throws_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used") VALUES (6, 5, '127.0.0.1', 0::smallint)$$, 'duplicate key value violates unique constraint "idx_20969_user"');
SELECT throws_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (7, 5, '127.0.0.1', 1::smallint, '127.0.0.1')$$, 'duplicate key value violates unique constraint "idx_20969_user"');

-- normal flow
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (7, 'test 7', '127.0.0.1');
INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip") VALUES (7, 7, '127.0.0.1');
SELECT lives_ok($$UPDATE "gk_account_activation" SET "used"=1::smallint, "validating_ip" = '127.0.0.1' WHERE id = 7::bigint;$$);


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
