-- Start transaction and plan the tests.

BEGIN;
SELECT plan(9);
-- SELECT * FROM no_plan();

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''
\set berlin '\'0101000020E61000009A99999999992A400000000000404A40\''
\set warsaw '\'0101000020E610000000000000000035409A99999999194A40\''
\set moscow '\'0101000020E6100000CDCCCCCCCCCC42409A99999999D94B40\''
\set tokyo '\'0101000020E61000006666666666766140CDCCCCCCCCCC4140\''

--GeoKrety
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test 1', 0, '2020-04-07 00:00:00+00');

-- Moves
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
SELECT is(elevation, 186, 'nice elevation') from gk_moves WHERE id = 1::bigint;

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (2, 1, 1, :paris, '2020-04-07 01:00:00+00', 0);
SELECT is(elevation, 91, 'paris elevation') from gk_moves WHERE id = 2::bigint;

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (3, 1, 1, :berlin, '2020-04-07 02:00:00+00', 0);
SELECT is(elevation, 44, 'berlin elevation') from gk_moves WHERE id = 3::bigint;

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (4, 1, 1, :warsaw, '2020-04-07 03:00:00+00', 0);
SELECT is(elevation, 108, 'warsaw elevation') from gk_moves WHERE id = 4::bigint;

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (5, 1, 1, :moscow, '2020-04-07 04:00:00+00', 0);
SELECT is(elevation, 147, 'moscow elevation') from gk_moves WHERE id = 5::bigint;

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (6, 1, 1, :tokyo, '2020-04-07 05:00:00+00', 0);
SELECT is(elevation, 37 , 'tokyo elevation') from gk_moves WHERE id = 6::bigint;

UPDATE "gk_moves" SET position=:paris WHERE id=1;
SELECT is(elevation, 91, 'paris elevation') from gk_moves WHERE id = 1::bigint;

UPDATE "gk_moves" SET position=:berlin WHERE id=1;
SELECT is(elevation, 44, 'berlin elevation') from gk_moves WHERE id = 1::bigint;

UPDATE "gk_moves" SET move_type=2, position=null WHERE id=1;
SELECT is(elevation, NULL, 'comments has no elevation') from gk_moves WHERE id = 1::bigint;


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
