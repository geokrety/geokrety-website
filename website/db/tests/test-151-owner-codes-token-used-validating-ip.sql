-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(16);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);

-- used 0 require requesting_ip an created_on_datetime
SELECT lives_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (1, 1, NULL, NULL, NULL, 0)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (2, 1, NULL, NULL, '127.0.0.1', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (3, 1, NULL, 1, NULL, 0)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (4, 1, NULL, 1, '127.0.0.1', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (5, 1, '2020-04-07 01:00:00+00', NULL, NULL, 0)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (6, 1, '2020-04-07 02:00:00+00', NULL, '127.0.0.1', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (7, 1, '2020-04-07 03:00:00+00', 1, NULL, 0)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (8, 1, '2020-04-07 04:00:00+00', 1, '127.0.0.1', 0)$$);

-- used 1 require all values
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (11, 1, NULL, NULL, NULL, 1)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (12, 1, NULL, NULL, '127.0.0.1', 1)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (13, 1, NULL, 1, NULL, 1)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (14, 1, NULL, 1, '127.0.0.1', 1)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (15, 1, '2020-04-07 05:00:00+00', NULL, NULL, 1)$$);
SELECT lives_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (16, 1, '2020-04-07 06:00:00+00', NULL, '127.0.0.1', 1)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (17, 1, '2020-04-07 07:00:00+00', 1, NULL, 1)$$);
SELECT lives_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "user", "validating_ip", "used") VALUES (18, 1, '2020-04-07 08:00:00+00', 1, '127.0.0.1', 1)$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
