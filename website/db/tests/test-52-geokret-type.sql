-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(24);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- Automatic
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (1, 'test', 0, 1, 1)$$);
SELECT is(non_collectible, NULL) FROM "gk_geokrety" WHERE id=1;
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (2, 'test', 1, 1, 1)$$);
SELECT is(non_collectible, NULL) FROM "gk_geokrety" WHERE id=2;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (3, 'test', 2, 1, 1)$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id=3;
UPDATE "gk_geokrety" SET non_collectible=NULL WHERE id=3;
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id=3;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (4, 'test', 3, 1, 1)$$);
SELECT is(non_collectible, NULL) FROM "gk_geokrety" WHERE id=4;
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (5, 'test', 4, 1, 1)$$);
SELECT is(non_collectible, NULL) FROM "gk_geokrety" WHERE id=5;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (6, 'test', 5, 1, 1)$$);
SELECT is(non_collectible, NULL) FROM "gk_geokrety" WHERE id=6;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (7, 'test', 6, 1, 1)$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id=7;
UPDATE "gk_geokrety" SET non_collectible=NULL WHERE id=7;
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id=7;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (8, 'test', 7, 1, 1)$$);
SELECT is(non_collectible, NULL) FROM "gk_geokrety" WHERE id=8;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (9, 'test', 8, 1, 1)$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id=9;
UPDATE "gk_geokrety" SET non_collectible=NULL WHERE id=9;
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id=9;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (10, 'test', 9, 1, 1)$$);
SELECT is(non_collectible, NULL) FROM "gk_geokrety" WHERE id=8;

SELECT throws_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder") VALUES (11, 'test', 10, 1, 1)$$);


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
