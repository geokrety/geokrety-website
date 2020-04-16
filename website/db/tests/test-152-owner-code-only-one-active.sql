-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(4);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (2, 'test', 0);
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (3, 'test', 0);

-- only one active token per geokret
INSERT INTO "gk_owner_codes" ("id", "geokret") VALUES (1, 1);
SELECT lives_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret", "claimed_on_datetime", "adopter", "validating_ip", "used") VALUES (2, 1, '2020-04-07 00:00:00+00', 1, '127.0.0.1', 1)$$);
SELECT throws_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret") VALUES (3, 1)$$);
UPDATE "gk_owner_codes" SET "claimed_on_datetime"='2020-04-07 00:00:00+00'::timestamptz, "adopter"=1, "validating_ip"='127.0.0.1', "used"=1 WHERE id = 1::bigint;
SELECT lives_ok($$INSERT INTO "gk_owner_codes" ("id", "geokret") VALUES (4, 1)$$);

INSERT INTO "gk_owner_codes" ("id", "geokret") VALUES (5, 2);
INSERT INTO "gk_owner_codes" ("id", "geokret") VALUES (6, 3);
SELECT throws_ok($$UPDATE "gk_owner_codes" SET "geokret"=5 WHERE id = 6::bigint$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
