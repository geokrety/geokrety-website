-- Start transaction and plan the tests.

BEGIN;

SELECT * FROM no_plan();

-- Automatic
INSERT INTO "gk_geokrety" ("id", "gkid", "name", "type") VALUES (1, 1, 'test 1', 0);
SELECT is(LENGTH(tracking_code), 6, 'Automatic add tracking_code') FROM gk_geokrety WHERE id = 1::bigint;

-- Manual
INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (2, 2, 'GEOKRETY', 'test 2', 0);
SELECT is(tracking_code, 'GEOKRETY', 'Code can be manually inserted') FROM gk_geokrety WHERE id = 2::bigint;

UPDATE "gk_geokrety" SET tracking_code='KRETYGEO' WHERE id=2;
SELECT is(tracking_code, 'KRETYGEO', 'Code can be manually updated') FROM gk_geokrety WHERE id = 2::bigint;

-- Reset
INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (3, 3, 'GMJUIN', 'test 3', 0);
UPDATE "gk_geokrety" SET tracking_code=NULL WHERE id=3;
SELECT isnt(tracking_code, 'GMJUIN', 'Set to NULL to generate a new one - 1') FROM gk_geokrety WHERE id = 3::bigint;
SELECT isnt(tracking_code, NULL, 'Set to NULL to generate a new one - 2') from gk_geokrety WHERE id = 3::bigint;

-- Update other untouched
INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (4, 4, 'HIOGRE', 'test 4', 0);
UPDATE "gk_geokrety" SET name='My Name' WHERE id=4;
SELECT is(tracking_code, 'HIOGRE', 'Tracking code is unmodified') from gk_geokrety WHERE id = 4::bigint;

-- Uniq
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (5, 5, 'UNIQUE1', 'uniq1', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (6, 6, 'UNIQUE1', 'uniq1', 0)$$, 23505, 'duplicate key value violates unique constraint "gk_geokrety_uniq_tracking_code"');

-- will be saved uppercase
INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (7, 7, 'jioklj', 'test 7', 0);
SELECT is(tracking_code, 'JIOKLJ', 'Tracking code will be saved uppercase') from gk_geokrety WHERE id = 7::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
