-- Start transaction and plan the tests.

BEGIN;

SELECT plan(12);

\set nice '\'0101000020E61000000AD7A3703D0A1D409A99999999D94540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''
\set berlin '\'0101000020E61000009A99999999992A400000000000404A40\''
\set warsaw '\'0101000020E610000000000000000035409A99999999194A40\''
\set moscow '\'0101000020E6100000CDCCCCCCCCCC42409A99999999D94B40\''
\set tokyo '\'0101000020E61000006666666666766140CDCCCCCCCCCC4140\''

--GeoKrety
INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (1, 1, 'ABC123', 'test 1', 0);
INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type") VALUES (2, 2, 'DEF456', 'test 2', 0);

-- Moves
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (1, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (2, 1, :paris, '2020-04-07 01:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (3, 1, :berlin, '2020-04-07 02:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (4, 1, :warsaw, '2020-04-07 03:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (5, 1, :moscow, '2020-04-07 04:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "position", "moved_on_datetime", "move_type") VALUES (6, 1, :tokyo, '2020-04-07 05:00:00+00', 0);

-- test gk change
UPDATE "gk_moves" SET geokret=2 WHERE id=2;
SELECT is(distance, 0, 'first move has 0 km') from gk_moves WHERE id = 2::bigint; -- paris

SELECT is(distance, 0, 'first move has 0 km') from gk_moves WHERE id = 1::bigint;
SELECT is(distance, 1075, 'Nice/Berlin') from gk_moves WHERE id = 3::bigint;
SELECT is(distance, 524, 'Berlin/Warsaw') from gk_moves WHERE id = 4::bigint;
SELECT is(distance, 1150, 'Warsaw/Moscow') from gk_moves WHERE id = 5::bigint;
SELECT is(distance, 7491, 'Moscow/Tokyo') from gk_moves WHERE id = 6::bigint;

UPDATE "gk_moves" SET geokret=2 WHERE id IN (4, 5);
SELECT is(distance, 0, 'first move has 0 km') from gk_moves WHERE id = 2::bigint; -- paris
SELECT is(distance, 1371, 'Paris/Warsaw') from gk_moves WHERE id = 4::bigint;
SELECT is(distance, 1150, 'Warsaw/Moscow') from gk_moves WHERE id = 5::bigint;

SELECT is(distance, 0, 'first move has 0 km') from gk_moves WHERE id = 1::bigint; -- nice
SELECT is(distance, 1075, 'Nice/Berlin') from gk_moves WHERE id = 3::bigint;
SELECT is(distance, 8932, 'Berlin/Tokyo') from gk_moves WHERE id = 6::bigint;


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;