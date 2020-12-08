-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(3);

-- Automatic
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);
SELECT is(gkid, 1::bigint, 'Automatic gkid 1') FROM gk_geokrety WHERE id = 1::bigint;

-- Manual
INSERT INTO "gk_geokrety" ("id",  "gkid", "name", "type") VALUES (2, 42, 'test', 0);
SELECT is(gkid, 42::bigint, 'gkid set manually') FROM gk_geokrety WHERE id = 2::bigint;
INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (3, 'test', 0);
SELECT is(gkid, 43::bigint, 'numbering continues') FROM gk_geokrety WHERE id = 3::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
