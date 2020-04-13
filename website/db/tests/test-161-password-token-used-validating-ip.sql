-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(8);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);

-- used 0 require requesting_ip an created_on_datetime
SELECT lives_ok($$INSERT INTO "gk_password_tokens" ("id", "user", "requesting_ip", "used_on_datetime", "validating_ip", "used") VALUES (1, 1, '127.0.0.1', NULL, NULL, 0)$$);
SELECT throws_ok($$INSERT INTO "gk_password_tokens" ("id", "user", "requesting_ip", "used_on_datetime", "validating_ip", "used") VALUES (2, 1, '127.0.0.1', NULL, '127.0.0.1', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_password_tokens" ("id", "user", "requesting_ip", "used_on_datetime", "validating_ip", "used") VALUES (3, 1, '127.0.0.1', '2020-04-07 01:00:00+00', NULL, 0)$$);
SELECT throws_ok($$INSERT INTO "gk_password_tokens" ("id", "user", "requesting_ip", "used_on_datetime", "validating_ip", "used") VALUES (4, 1, '127.0.0.1', '2020-04-07 02:00:00+00', '127.0.0.1', 0)$$);

-- used 1 require all values
SELECT throws_ok($$INSERT INTO "gk_password_tokens" ("id", "user", "requesting_ip", "used_on_datetime", "validating_ip", "used") VALUES (5, 1, '127.0.0.1', NULL, NULL, 1)$$);
SELECT throws_ok($$INSERT INTO "gk_password_tokens" ("id", "user", "requesting_ip", "used_on_datetime", "validating_ip", "used") VALUES (6, 1, '127.0.0.1', NULL, '127.0.0.1', 1)$$);
SELECT throws_ok($$INSERT INTO "gk_password_tokens" ("id", "user", "requesting_ip", "used_on_datetime", "validating_ip", "used") VALUES (7, 1, '127.0.0.1', '2020-04-07 03:00:00+00', NULL, 1)$$);
SELECT lives_ok($$INSERT INTO "gk_password_tokens" ("id", "user", "requesting_ip", "used_on_datetime", "validating_ip", "used") VALUES (8, 1, '127.0.0.1', '2020-04-07 04:00:00+00', '127.0.0.1', 1)$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
