-- Start transaction and plan the tests.

BEGIN;

SELECT * FROM no_plan();
-- SELECT plan(6);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test 1', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (1, 1, 1, '2020-04-07 01:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (2, 1, 1, '2020-04-07 02:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (3, 1, 1, '2020-04-07 03:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (4, 1, 1, '2020-04-07 04:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (5, 1, 1, '2020-04-07 05:00:00+00', 2);

INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (6, 1, 1, '2020-04-07 06:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (7, 1, 1, '2020-04-07 07:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (8, 1, 1, '2020-04-07 08:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (9, 1, 1, '2020-04-07 09:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (10, 1, 1, '2020-04-07 10:00:00+00', 2);

INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (11, 1, 1, '2020-04-07 11:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (12, 1, 1, '2020-04-07 12:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (13, 1, 1, '2020-04-07 13:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (14, 1, 1, '2020-04-07 14:00:00+00', 2);
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type") VALUES (15, 1, 1, '2020-04-07 15:00:00+00', 2);


SELECT is(moves_get_on_page(15, 5, 1), 1::bigint);
SELECT is(moves_get_on_page(11, 5, 1), 1::bigint); --
SELECT is(moves_get_on_page(10, 5, 1), 2::bigint);
SELECT is(moves_get_on_page(6, 5, 1), 2::bigint); --
SELECT is(moves_get_on_page(5, 5, 1), 3::bigint);
SELECT is(moves_get_on_page(1, 5, 1), 3::bigint); --


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
