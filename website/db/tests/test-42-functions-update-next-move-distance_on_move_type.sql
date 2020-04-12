-- Start transaction and plan the tests.

BEGIN;

SELECT plan(3);

\set nice '\'0101000020E61000000AD7A3703D0A1D409A99999999D94540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''
\set berlin '\'0101000020E61000009A99999999992A400000000000404A40\''

-- id 1
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type")
VALUES (1, 1, 'ABC123', 'test 1', 0);

-- Moves
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (2, 1, 1, :paris, '2020-04-07 01:00:00+00', 0);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (3, 1, 1, :berlin, '2020-04-07 02:00:00+00', 0);

-- test move-type (comment)
UPDATE "gk_moves" SET move_type=2, position=NULL WHERE id=2;
SELECT is(distance, 0, 'first move has 0 km') from gk_moves WHERE id = 1::bigint;
SELECT is(distance, NULL, 'No distance count for comments') from gk_moves WHERE id = 2::bigint;
SELECT is(distance, 1075, 'Nice/Berlin') from gk_moves WHERE id = 3::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;