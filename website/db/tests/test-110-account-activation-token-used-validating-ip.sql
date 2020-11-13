-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(8);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- validating_ip MUST be null for move type 0
SELECT lives_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (1, 1, '127.0.0.1', 0, NULL)$$);
SELECT throws_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (2, 1, '127.0.0.1', 0, '127.0.0.1')$$);

-- validating_ip MUST be set for move type 1
SELECT throws_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (3, 1, '127.0.0.1', 1, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (4, 1, '127.0.0.1', 1, '127.0.0.1')$$);

-- validating_ip MUST be null for move type 2
SELECT lives_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (5, 1, '127.0.0.1', 2, NULL)$$);
SELECT throws_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (6, 1, '127.0.0.1', 2, '127.0.0.1')$$);

-- validating_ip MUST be set for move type 3
SELECT throws_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (7, 1, '127.0.0.1', 3, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_account_activation" ("id", "user", "requesting_ip", "used", "validating_ip") VALUES (8, 1, '127.0.0.1', 3, '127.0.0.1')$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
