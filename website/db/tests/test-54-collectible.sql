-- Start transaction and plan the tests.

BEGIN;
SELECT * FROM no_plan();
-- SELECT plan(21);

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''
\set move_type_dropped 0
\set move_type_grabbed 1
\set move_type_comment 2
\set move_type_seen 3
\set move_type_archived 4
\set move_type_dipped 5

SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1')$$);
SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1')$$);
SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1')$$);

-- non_collectible can be set
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (1, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT is(owner, 1::bigint) FROM "gk_geokrety" WHERE id = 1::bigint;
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 1::bigint;
SELECT is(non_collectible, NULL, 'Is null by default') FROM gk_geokrety WHERE id = 1::bigint;
SELECT lives_ok($$UPDATE gk_geokrety SET non_collectible=NOW() WHERE id = 1::bigint$$);
SELECT isnt(non_collectible, NULL, 'Can be updated') FROM gk_geokrety WHERE id = 1::bigint;
SELECT is(owner, 1::bigint) FROM "gk_geokrety" WHERE id = 1::bigint;
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 1::bigint;

-- When non_collectible is NULL, all log types are allowed by everyone
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (2, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 2, 2, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:00+00', 0)$$);
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (3, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (2, 3, 2, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:00+00', 3)$$);
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (4, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (3, 4, 2, '2024-05-24 00:00:03+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (5, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (4, 5, 2, '2024-05-24 00:00:04+00', 2)$$);
-- Archive is only for owner
-- SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (5, 2, 2, '2024-05-24 00:00:05+00', 4)$$);
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (6, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (6, 6, 2, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:00+00', 5)$$);


-- When non_collectible is NULL, all log types are allowed by owner
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (7, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (11, 7, 1, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:00+00', 0)$$);
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (8, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (12, 8, 1, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:00+00', 3)$$);
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (9, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (13, 9, 1, '2024-05-24 00:00:03+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (10, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (14, 10, 1, '2024-05-24 00:00:04+00', 2)$$);
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (11, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (15, 11, 1, '2024-05-24 00:00:05+00', 4)$$);
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (12, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (16, 12, 1, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:00+00', 5)$$);


-- When non_collectible is set to a date, only discovered types are allowed by everyone
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner", "non_collectible") VALUES (14, 'test', 0, '2024-05-24 00:00:00+00', 1, '2024-05-24 00:00:02+00')$$);
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 14::bigint;
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (21, 14, 2, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:03+00', 0)$$);
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 14::bigint;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner", "non_collectible") VALUES (15, 'test', 0, '2024-05-24 00:00:00+00', 1, '2024-05-24 00:00:02+00')$$);
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 15::bigint;
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (22, 15, 2, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:03+00', 3)$$);
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 15::bigint;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner", "non_collectible") VALUES (16, 'test', 0, '2024-05-24 00:00:00+00', 1, '2024-05-24 00:00:02+00')$$);
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 16::bigint;
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (23, 16, 2, '2024-05-24 00:00:03+00', 1)$$);
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 16::bigint;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner", "non_collectible") VALUES (17, 'test', 0, '2024-05-24 00:00:00+00', 1, '2024-05-24 00:00:02+00')$$);
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 17::bigint;
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (24, 17, 2, '2024-05-24 00:00:03+00', 2)$$);
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 17::bigint;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner", "non_collectible") VALUES (18, 'test', 0, '2024-05-24 00:00:00+00', 1, '2024-05-24 00:00:02+00')$$);
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 18::bigint;
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (26, 18, 2, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:03+00', 5)$$);
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 18::bigint;


-- When non_collectible is set to a date, holder can ....
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (30, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (30, 30, 3, '2024-05-24 00:00:01+00', 1);
UPDATE "gk_geokrety" SET non_collectible='2024-05-24 00:00:02+00' WHERE id = 30::bigint;
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 30::bigint;
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 30::bigint;
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (31, 30, 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:03+00', 0)$$);
-- logtype 0 should disable non_collectible flag
SELECT is(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 30::bigint;
SELECT is(holder, NULL) FROM "gk_geokrety" WHERE id = 30::bigint;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (31, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (32, 31, 3, '2024-05-24 00:00:01+00', 1);
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 31::bigint;
UPDATE "gk_geokrety" SET non_collectible='2024-05-24 00:00:02+00' WHERE id = 31::bigint;
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (33, 31, 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:03+00', 3)$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 31::bigint;
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 31::bigint;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (32, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (34, 32, 3, '2024-05-24 00:00:01+00', 1);
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 32::bigint;
UPDATE "gk_geokrety" SET non_collectible='2024-05-24 00:00:02+00' WHERE id = 32::bigint;
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (35, 32, 3, '2024-05-24 00:00:03+00', 1)$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 32::bigint;
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 32::bigint;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (33, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (36, 33, 3, '2024-05-24 00:00:01+00', 1);
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 33::bigint;
UPDATE "gk_geokrety" SET non_collectible='2024-05-24 00:00:02+00' WHERE id = 33::bigint;
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 33::bigint;
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (37, 12, 3, '2024-05-24 00:00:03+00', 2)$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 33::bigint;
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 33::bigint;

-- Only GeoKret owner can archive it s GeoKrety

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (34, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (38, 34, 3, '2024-05-24 00:00:01+00', 1);
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 34::bigint;
UPDATE "gk_geokrety" SET non_collectible='2024-05-24 00:00:02+00' WHERE id = 34::bigint;
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 34::bigint;
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (39, 34, 3, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:03+00', 5)$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 34::bigint;
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 34::bigint;


-- -- When non_collectible is set to a date and holder is someone else, owner can ....
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (35, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (40, 35, 3, '2024-05-24 00:00:01+00', 1);
SELECT is(owner, 1::bigint) FROM "gk_geokrety" WHERE id = 35::bigint;
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 35::bigint;
UPDATE "gk_geokrety" SET non_collectible='2024-05-24 00:00:02+00' WHERE id = 35::bigint;
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 35::bigint;
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (41, 35, 1, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:03+00', 0)$$);
SELECT is(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 35::bigint;
SELECT is(holder, NULL) FROM "gk_geokrety" WHERE id = 35::bigint;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (36, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (42, 36, 3, '2024-05-24 00:00:01+00', 1);
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 36::bigint;
UPDATE "gk_geokrety" SET non_collectible='2024-05-24 00:00:02+00' WHERE id = 36::bigint;
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 36::bigint;
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (43, 36, 1, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:03+00', 3)$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 36::bigint;
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 36::bigint;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (37, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (44, 37, 3, '2024-05-24 00:00:01+00', 1);
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 37::bigint;
UPDATE "gk_geokrety" SET non_collectible='2024-05-24 00:00:02+00' WHERE id = 37::bigint;
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 37::bigint;
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (45, 37, 1, '2024-05-24 00:01:03+00', 1)$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 37::bigint;
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 37::bigint;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (38, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (46, 38, 3, '2024-05-24 00:00:01+00', 1);
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 38::bigint;
UPDATE "gk_geokrety" SET non_collectible='2024-05-24 00:00:02+00' WHERE id = 38::bigint;
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 38::bigint;
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (47, 38, 1, '2024-05-24 00:00:03+00', 2)$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 38::bigint;
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 38::bigint;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (39, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (48, 39, 3, '2024-05-24 00:00:01+00', 1);
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 39::bigint;
UPDATE "gk_geokrety" SET non_collectible='2024-05-24 00:00:02+00' WHERE id = 39::bigint;
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 39::bigint;
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (49, 39, 1, '2024-05-24 00:00:03+00', 4)$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 39::bigint;
SELECT is(holder, NULL) FROM "gk_geokrety" WHERE id = 39::bigint;

SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (40, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (50, 40, 3, '2024-05-24 00:00:01+00', 1);
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 40::bigint;
UPDATE "gk_geokrety" SET non_collectible='2024-05-24 00:00:02+00' WHERE id = 40::bigint;
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 40::bigint;
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (51, 40, 1, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:03+00', 5)$$);
SELECT isnt(non_collectible, NULL) FROM "gk_geokrety" WHERE id = 40::bigint;
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 40::bigint;

-- Not effective if move is before non_collectible activation
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner", "non_collectible") VALUES (41, 'test', 0, '2024-05-24 00:00:00+00', 1, '2024-05-24 00:00:02+00')$$);
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 41::bigint;
-- 121
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (53, 41, 2, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:01+00', 0)$$);
SELECT is(holder, NULL) FROM "gk_geokrety" WHERE id = 41::bigint;

-- Effective as soon as non_collectible is enabled
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner", "non_collectible") VALUES (42, 'test', 0, '2024-05-24 00:00:00+00', 1, '2024-05-24 00:00:02+00')$$);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (54, 42, 3, '2024-05-24 00:00:01+00', 1);
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 42::bigint;
SELECT throws_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (55, 42, 2, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:02+00', 0)$$);
SELECT is(holder, 3::bigint) FROM "gk_geokrety" WHERE id = 42::bigint;

-- Can be enabled only if it has an holder
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (43, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (56, 43, 2, '0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540', '2024-05-24 00:00:02+00', 0)$$);
SELECT is(holder, NULL) FROM "gk_geokrety" WHERE id = 43::bigint;
SELECT throws_ok($$UPDATE "gk_geokrety" SET non_collectible='2024-05-24 00:00:02+00' WHERE id = 43::bigint$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
