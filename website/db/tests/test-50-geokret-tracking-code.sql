-- Start transaction and plan the tests.

BEGIN;

SELECT plan(33);

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
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (9, 9, 'GC1234', 'test GC', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (10, 10, 'OP1234', 'test OP', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (11, 11, 'OK1234', 'test OK', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (12, 12, 'GE1234', 'test GE', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (13, 13, 'OZ1234', 'test OZ', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (14, 14, 'OU1234', 'test OU', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (15, 15, 'ON1234', 'test ON', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (16, 16, 'OL1234', 'test OL', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (17, 17, 'OJ1234', 'test OJ', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (18, 18, 'OS1234', 'test OS', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (19, 19, 'GD1234', 'test GD', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (20, 20, 'GA1234', 'test GA', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (21, 21, 'VI1234', 'test VI', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (22, 22, 'MS1234', 'test MS', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (23, 23, 'TR1234', 'test TR', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (24, 24, 'EX1234', 'test EX', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (25, 25, 'GR1234', 'test GR', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (26, 26, 'RH1234', 'test RH', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (27, 27, 'OX1234', 'test OX', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (28, 28, 'OB1234', 'test OB', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (29, 29, 'OR1234', 'test OR', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (30, 30, 'LT1234', 'test LT', 0)$$);
SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (31, 31, 'LV1234', 'test LV', 0)$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
