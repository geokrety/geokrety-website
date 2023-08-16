-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(13);

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''
\set berlin '\'0101000020E61000009A99999999992A400000000000404A40\''
\set warsaw '\'0101000020E610000000000000000035409A99999999194A40\''
\set moscow '\'0101000020E6100000CDCCCCCCCCCC42409A99999999D94B40\''
\set tokyo '\'0101000020E61000006666666666766140CDCCCCCCCCCC4140\''

--GeoKrety
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (2, 'test 1', 0, '2020-04-07 00:00:00+00');
SELECT is(distance, 0::bigint, 'Never moved') FROM gk_geokrety WHERE id = 1::bigint;

-- Moves
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
SELECT is(distance, 0::bigint, 'first move has 0 km') FROM gk_geokrety WHERE id = 1::bigint;

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (2, 1, 1, :paris, '2020-04-07 01:00:00+00', 0);
SELECT is(distance, (680)::bigint, 'Nice/Paris') FROM gk_geokrety WHERE id = 1::bigint;

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (3, 1, 1, :berlin, '2020-04-07 02:00:00+00', 0);
SELECT is(distance, (680+877)::bigint, 'Nice/Paris/Berlin') FROM gk_geokrety WHERE id = 1::bigint;

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (4, 1, 1, :warsaw, '2020-04-07 03:00:00+00', 0);
SELECT is(distance, (680+877+524)::bigint, 'Nice/Paris/Berlin/Warsaw') FROM gk_geokrety WHERE id = 1::bigint;

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (5, 1, 1, :moscow, '2020-04-07 04:00:00+00', 0);
SELECT is(distance, (680+877+524+1150)::bigint, 'Nice/Paris/Berlin/Warsaw/Moscow') FROM gk_geokrety WHERE id = 1::bigint;

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (6, 1, 1, :tokyo, '2020-04-07 05:00:00+00', 0);
SELECT is(distance, (680+877+524+1150+7491)::bigint, 'Nice/Paris/Berlin/Warsaw/Moscow/Tokyo') FROM gk_geokrety WHERE id = 1::bigint;

-- Delete
DELETE FROM "gk_moves" WHERE id = 6::bigint;
SELECT is(distance, (680+877+524+1150)::bigint, 'Nice/Paris/Berlin/Warsaw/Moscow') FROM gk_geokrety WHERE id = 1::bigint;

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (6, 1, 1, :tokyo, '2020-04-07 05:00:00+00', 0);
SELECT is(distance, (680+877+524+1150+7491)::bigint, 'Nice/Paris/Berlin/Warsaw/Moscow/Tokyo') FROM gk_geokrety WHERE id = 1::bigint;

-- test move_type as comment
UPDATE "gk_moves" SET move_type=2, position=null WHERE id=5;
SELECT is(distance, (680+877+524+8590)::bigint, 'Nice/Paris/Berlin/Warsaw/Tokyo') FROM gk_geokrety WHERE id = 1::bigint;

-- test change GK
UPDATE "gk_moves" SET geokret=2 WHERE id=6;
SELECT is(distance, (680+877+524)::bigint, 'Nice/Paris/Berlin/Warsaw') FROM gk_geokrety WHERE id = 1::bigint;
SELECT is(distance, (0)::bigint, 'second GK first move') FROM gk_geokrety WHERE id = 2::bigint;

-- test not null
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (3, 'test 2', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "move_type") VALUES (7, 3, 1, 2);
SELECT is(distance, 0::bigint, 'Never moved') FROM gk_geokrety WHERE id = 3::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
