-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(87);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1');

-- Traditional
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (1, 'test', 0, 1, 1, '2024-07-21 12:15:00+00')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (1, 1, '2024-07-21 12:15:01+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (1, 1, '2024-07-21 12:15:02+00', 1, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (1, 1, '2024-07-21 12:15:03+00', 2, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (1, 1, '2024-07-21 12:15:04+00', 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (1, 1, '2024-07-21 12:15:05+00', 4, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (1, 1, '2024-07-21 12:15:06+00', 5, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);

-- Book
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (2, 'test', 1, 1, 1, '2024-07-21 12:15:00+00')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (2, 1, '2024-07-21 12:15:01+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (2, 1, '2024-07-21 12:15:02+00', 1, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (2, 1, '2024-07-21 12:15:03+00', 2, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (2, 1, '2024-07-21 12:15:04+00', 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (2, 1, '2024-07-21 12:15:05+00', 4, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (2, 1, '2024-07-21 12:15:06+00', 5, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);

-- Human
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (3, 'test', 2, 1, 2, '2024-07-21 12:15:00+00')$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id=3;
SELECT lives_ok($$UPDATE "gk_geokrety" SET non_collectible='2024-07-21 12:15:00+00' WHERE id=3;$$);
SELECT is(non_collectible, '2024-07-21 12:15:00+00') FROM "gk_geokrety" WHERE id=3;
-- Owner
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (3, 1, '2024-07-21 12:15:01+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (3, 1, '2024-07-21 12:15:02+00', 1, NULL)$$);
-- Holder
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (3, 2, '2024-07-21 12:15:03+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (3, 2, '2024-07-21 12:15:04+00', 1, NULL)$$);
-- Other user
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (3, 3, '2024-07-21 12:15:05+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (3, 3, '2024-07-21 12:15:06+00', 1, NULL)$$);

SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (3, 1, '2024-07-21 12:15:07+00', 2, NULL)$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (3, 1, '2024-07-21 12:15:08+00', 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (3, 3, '2024-07-21 12:15:08+00', 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (3, 1, '2024-07-21 12:15:09+00', 4, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (3, 1, '2024-07-21 12:15:10+00', 5, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);

-- Coin
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (4, 'test', 3, 1, 1, '2024-07-21 12:15:00+00')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (4, 1, '2024-07-21 12:15:01+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (4, 1, '2024-07-21 12:15:02+00', 1, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (4, 1, '2024-07-21 12:15:03+00', 2, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (4, 1, '2024-07-21 12:15:04+00', 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (4, 1, '2024-07-21 12:15:05+00', 4, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (4, 1, '2024-07-21 12:15:06+00', 5, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);

-- KretyPost
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (5, 'test', 4, 1, 1, '2024-07-21 12:15:00+00')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (5, 1, '2024-07-21 12:15:01+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (5, 1, '2024-07-21 12:15:02+00', 1, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (5, 1, '2024-07-21 12:15:03+00', 2, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (5, 1, '2024-07-21 12:15:04+00', 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (5, 1, '2024-07-21 12:15:05+00', 4, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (5, 1, '2024-07-21 12:15:06+00', 5, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);

-- Pebble
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (6, 'test', 5, 1, 1, '2024-07-21 12:15:00+00')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (6, 1, '2024-07-21 12:15:01+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (6, 1, '2024-07-21 12:15:02+00', 1, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (6, 1, '2024-07-21 12:15:03+00', 2, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (6, 1, '2024-07-21 12:15:04+00', 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (6, 1, '2024-07-21 12:15:05+00', 4, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (6, 1, '2024-07-21 12:15:06+00', 5, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);

-- Car
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (7, 'test', 6, 1, 1, '2024-07-21 12:15:00+00')$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id=7;
SELECT lives_ok($$UPDATE "gk_geokrety" SET non_collectible='2024-07-21 12:15:00+00' WHERE id=7;$$);
SELECT is(non_collectible, '2024-07-21 12:15:00+00') FROM "gk_geokrety" WHERE id=7;
-- Owner
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (7, 1, '2024-07-21 12:15:01+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (7, 1, '2024-07-21 12:15:02+00', 1, NULL)$$);
-- Holder
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (7, 2, '2024-07-21 12:15:03+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (7, 2, '2024-07-21 12:15:04+00', 1, NULL)$$);
-- Other user
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (7, 3, '2024-07-21 12:15:05+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (7, 3, '2024-07-21 12:15:06+00', 1, NULL)$$);

SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (7, 1, '2024-07-21 12:15:03+00', 2, NULL)$$);

SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (7, 1, '2024-07-21 12:15:08+00', 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (7, 3, '2024-07-21 12:15:08+00', 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);

SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (7, 1, '2024-07-21 12:15:05+00', 4, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (7, 1, '2024-07-21 12:15:06+00', 5, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);

-- Card
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (8, 'test', 7, 1, 1, '2024-07-21 12:15:00+00')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (8, 1, '2024-07-21 12:15:01+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (8, 1, '2024-07-21 12:15:02+00', 1, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (8, 1, '2024-07-21 12:15:03+00', 2, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (8, 1, '2024-07-21 12:15:04+00', 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (8, 1, '2024-07-21 12:15:05+00', 4, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (8, 1, '2024-07-21 12:15:06+00', 5, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);

-- Dog
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "owner", "holder", "created_on_datetime") VALUES (9, 'test', 8, 1, 1, '2024-07-21 12:15:00+00')$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id=9;
SELECT lives_ok($$UPDATE "gk_geokrety" SET non_collectible='2024-07-21 12:15:00+00' WHERE id=9;$$);
SELECT is(non_collectible, '2024-07-21 12:15:00+00') FROM "gk_geokrety" WHERE id=9;
-- Owner
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (9, 1, '2024-07-21 12:15:01+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (9, 1, '2024-07-21 12:15:02+00', 1, NULL)$$);
-- Holder
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (9, 2, '2024-07-21 12:15:03+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (9, 2, '2024-07-21 12:15:04+00', 1, NULL)$$);
-- Other user
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (9, 3, '2024-07-21 12:15:05+00', 0, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (9, 3, '2024-07-21 12:15:06+00', 1, NULL)$$);

SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (9, 1, '2024-07-21 12:15:07+00', 2, NULL)$$);

SELECT throws_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (9, 1, '2024-07-21 12:15:08+00', 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (9, 3, '2024-07-21 12:15:08+00', 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);

SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (9, 1, '2024-07-21 12:15:09+00', 4, NULL)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("geokret", "author", "moved_on_datetime", "move_type", "position") VALUES (9, 1, '2024-07-21 12:15:10+00', 5, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540')$$);






-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
