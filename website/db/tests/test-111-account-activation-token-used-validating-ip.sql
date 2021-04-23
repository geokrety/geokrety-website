-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(6);

\set TOKEN_UNUSED   0::smallint
\set TOKEN_VALID    1::smallint
-- \set TOKEN_EXPIRED  2::smallint


-- validating_ip not set for TOKEN_UNUSED
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
SELECT  lives_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (1, 1, '127.0.0.1', 0::smallint, NULL)$$);
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
SELECT throws_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (2, 2, '127.0.0.1', 0::smallint, '127.0.0.1')$$);

-- validating_ip not set for TOKEN_VALID
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1');
SELECT throws_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (3, 3, '127.0.0.1', 1::smallint, NULL)$$);
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (4, 'test 4', '127.0.0.1');
SELECT  lives_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (4, 4, '127.0.0.1', 1::smallint, '127.0.0.1')$$);

-- type does not exists
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (5, 'test 5', '127.0.0.1');
SELECT throws_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (5, 5, '127.0.0.1', 2::smallint, NULL)$$);
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (6, 'test 6', '127.0.0.1');
SELECT throws_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (6, 6, '127.0.0.1', 2::smallint, '127.0.0.1')$$);


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
