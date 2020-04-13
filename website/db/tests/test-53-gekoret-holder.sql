-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(9);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');

-- On create holder is owner
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);
SELECT is(holder, owner, 'On create holder is owner') FROM gk_geokrety WHERE id = 1::bigint;
SELECT is(holder, NULL, 'On create holder is owner') FROM gk_geokrety WHERE id = 1::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type", "owner") VALUES (2, 'test', 0, 1);
SELECT is(holder, owner, 'On create holder is owner') FROM gk_geokrety WHERE id = 2::bigint;
SELECT is(holder, 1::bigint, 'On create holder is owner') FROM gk_geokrety WHERE id = 2::bigint;

-- On create holder can be overridden
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (3, 'test', 0, 1, NULL);
SELECT is(holder, owner, 'holder can be overridden') FROM gk_geokrety WHERE id = 3::bigint;
UPDATE "gk_geokrety" SET holder = 2 WHERE id = 3::bigint;
SELECT is(holder, 2::bigint, 'holder can be overridden') FROM gk_geokrety WHERE id = 3::bigint;

INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (4, 'test', 0, 1, 2);
SELECT isnt(holder, owner, 'holder can be overridden') FROM gk_geokrety WHERE id = 4::bigint;
SELECT is(holder, 2::bigint, 'holder can be overridden') FROM gk_geokrety WHERE id = 4::bigint;

-- reset holder
INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (5, 'test', 0, 1, 2);
UPDATE "gk_geokrety" SET holder = NULL WHERE id = 5::bigint;
SELECT is(holder, 1::bigint, 'reset holder') FROM gk_geokrety WHERE id = 5::bigint;


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
