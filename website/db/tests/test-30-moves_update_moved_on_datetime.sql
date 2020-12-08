-- Start transaction and plan the tests.

BEGIN;
-- SELECT plan(9);
SELECT * FROM no_plan();

--GeoKrety
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test 1', 0, '2020-04-07 00:00:00+00');

-- Moves
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (1, 1, 1, '2020-04-07 00:00:00+00', 2);
SELECT is(moved_on_datetime, '2020-04-07 00:00:00+00'::timestamp with time zone, 'manual set') from gk_moves WHERE id = 1::bigint;

INSERT INTO "gk_moves" ("id", "geokret", "author", "move_type") VALUES (2, 1, 1, 2);
SELECT is(moved_on_datetime, created_on_datetime , 'automatic set') from gk_moves where id = 2::bigint;


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
