-- Start transaction and plan the tests.

BEGIN;

SELECT plan(9);

\set nice '\'0101000020E61000000AD7A3703D0A1D409A99999999D94540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''
\set berlin '\'0101000020E61000009A99999999992A400000000000404A40\''
\set warsaw '\'0101000020E610000000000000000035409A99999999194A40\''
\set moscow '\'0101000020E6100000CDCCCCCCCCCC42409A99999999D94B40\''
\set tokyo '\'0101000020E61000006666666666766140CDCCCCCCCCCC4140\''
\set pula '\'0101000020E61000009A99999999992B406666666666664640\''
\set philadelphia '\'0101000020E6100000CDCCCCCCCCCC41406866666666264440\''


-- id 1
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type", "created_on_datetime")
VALUES (1, 1, 'ABC123', 'test 1', 0, '2020-04-07 00:00:00+00');

-- Moves
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (2, 1, 1, :paris, '2020-04-07 01:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (3, 1, 1, :berlin, '2020-04-07 02:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (4, 1, 1, :warsaw, '2020-04-07 03:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (5, 1, 1, :moscow, '2020-04-07 04:00:00+00', 0);
-- INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (6, 1, 1, :tokyo, '2020-04-07 05:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (13, 1, 1, :pula, '2020-04-07 12:00:00+00', 0);


SELECT is(distance, 2065, 'Moscow/Pula') from gk_moves WHERE id = 13::bigint;

-- test update coordinates changed to Tokyo
UPDATE "gk_moves" SET position=:tokyo WHERE id=13;
SELECT is(distance, 7491, 'Moscow/Tokyo') from gk_moves WHERE id = 13::bigint;
-- revert to Pula
UPDATE "gk_moves" SET position=:pula WHERE id=13;
SELECT is(distance, 2065, 'Moscow/Pula') from gk_moves WHERE id = 13::bigint;

-- Test change date
UPDATE "gk_moves" SET moved_on_datetime='2020-04-07 01:30:00+00' WHERE id=13;
SELECT is(distance, 0, 'first move has 0 km') from gk_moves WHERE id = 1::bigint;
SELECT is(distance, 683, 'Nice/Paris') from gk_moves WHERE id = 2::bigint;
SELECT is(distance, 980, 'Paris/Pula') from gk_moves WHERE id = 13::bigint; --
SELECT is(distance, 857, 'Pula/Berlin') from gk_moves WHERE id = 3::bigint;
SELECT is(distance, 524, 'Berlin/Warsaw') from gk_moves WHERE id = 4::bigint;
SELECT is(distance, 1150, 'Warsaw/Moscow') from gk_moves WHERE id = 5::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;