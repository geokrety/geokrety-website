-- Start transaction and plan the tests.

BEGIN;

SELECT * FROM no_plan();

-- Automatic
INSERT INTO "gk_geokrety" ("id", "gkid", "name", "type") VALUES (1, 1, 'test 1', 0);
SELECT is(LENGTH(tracking_code), 6, 'Automatic add tracking_code') FROM gk_geokrety WHERE id = 1::bigint;

-- Manual
INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (2, 2, 'AGEOKRETY', 'test 2', 0);
SELECT is(tracking_code, 'AGEOKRETY', 'Code can be manually inserted') FROM gk_geokrety WHERE id = 2::bigint;

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
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (6, 6, 'UNIQUE1', 'uniq1', 0)$$, 23505, 'duplicate key value violates unique constraint "idx_geokrety_tracking_code"');

-- will be saved uppercase
INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (7, 7, 'jioklj', 'test 7', 0);
SELECT is(tracking_code, 'JIOKLJ', 'Tracking code will be saved uppercase') from gk_geokrety WHERE id = 7::bigint;

-- cannot start by GK or other networks prefixes
-- GK, GC, OP, OK, GE, OZ, OU, ON, OL, OJ, OS, GD, GA, VI, MS, TR, EX, GR, RH, OX, OB, OR, LT, LV
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (8, 8, 'GK1234', 'test GK', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (9, 9, 'GC', 'test GC', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (10, 10, 'OP', 'test OP', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (11, 11, 'OK', 'test OK', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (12, 12, 'GE', 'test GE', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (13, 13, 'OZ', 'test OZ', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (14, 14, 'OU', 'test OU', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (15, 15, 'ON', 'test ON', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (16, 16, 'OL', 'test OL', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (17, 17, 'OJ', 'test OJ', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (18, 18, 'OS', 'test OS', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (19, 19, 'GD', 'test GD', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (20, 20, 'GA', 'test GA', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (21, 21, 'VI', 'test VI', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (22, 22, 'MS', 'test MS', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (23, 23, 'TR', 'test TR', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (24, 24, 'EX', 'test EX', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (25, 25, 'GR', 'test GR', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (26, 26, 'RH', 'test RH', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (27, 27, 'OX', 'test OX', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (28, 28, 'OB', 'test OB', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (29, 29, 'OR', 'test OR', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (30, 30, 'LT', 'test LT', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (31, 31, 'LV', 'test LV', 0)$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
