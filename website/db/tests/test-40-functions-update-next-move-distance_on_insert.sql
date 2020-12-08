-- Start transaction and plan the tests.

BEGIN;

\set Vancouver '\'0101000020E61000009A999999999948403433333333734CC0\''

SELECT plan(28);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- id 1
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "gkid", "tracking_code", "name", "type", "created_on_datetime")
VALUES (1, 1, 'ABC123', 'test 1', 0, '2020-04-07 00:00:00+00')$$);
SELECT is(distance, 0::bigint, 'GK distance is 0') from gk_geokrety WHERE id = 1::bigint;

-- id 1 -- Nice
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (1, 1, 1, 43.7, 7.26, '2020-04-07 00:00:00+00', 0)$$);
SELECT is(distance, 0, 'first move has 0 km') from gk_moves WHERE id = 1::bigint;

-- id 2 -- Paris
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (2, 1, 1, 48.8, 2.3, '2020-04-07 01:00:00+00', 0)$$);
SELECT is(distance, 683, 'Nice/Paris') from gk_moves WHERE id = 2::bigint;

-- id 3 - Berlin
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (3, 1, 1, 52.5, 13.3, '2020-04-07 02:00:00+00', 0)$$);
SELECT is(distance, 877, 'Paris/Berlin') from gk_moves WHERE id = 3::bigint;

-- id 4 - Warsaw
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (4, 1, 1, 52.2, 21.0, '2020-04-07 03:00:00+00', 0)$$);
SELECT is(distance, 524, 'Berlin/Warsaw') from gk_moves WHERE id = 4::bigint;

-- id 5 - Moscow
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (5, 1, 1, 55.7, 37.6, '2020-04-07 04:00:00+00', 0)$$);
SELECT is(distance, 1150, 'Warsaw/Moscow') from gk_moves WHERE id = 5::bigint;

-- id 6 - Tokyo
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (6, 1, 1, 35.6, 139.7, '2020-04-07 05:00:00+00', 0)$$);
SELECT is(distance, 7491, 'Moscow/Tokyo') from gk_moves WHERE id = 6::bigint;

-- id 7 - Canberra
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (7, 1, 1, -35.3, 149.1, '2020-04-07 06:00:00+00', 0)$$);
SELECT is(distance, 7944, 'Tokyo/Canberra') from gk_moves WHERE id = 7::bigint;

-- id 8 - Cape Town
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (8, 1, 1, -33.9, 18.4, '2020-04-07 07:00:00+00', 0)$$);
SELECT is(distance, 10770, 'Canberra/Cape Town') from gk_moves WHERE id = 8::bigint;

-- id 9 - Ushuaia
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (9, 1, 1, -54.8, -68.3, '2020-04-07 08:00:00+00', 0)$$);
SELECT is(distance, 6794, 'Cape Town/Ushuaia') from gk_moves WHERE id = 9::bigint;

-- id 10 - Panama
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (10, 1, 1, 9.0, -79.5, '2020-04-07 09:00:00+00', 0)$$);
SELECT is(distance, 7171, 'Ushuaia/Panama') from gk_moves WHERE id = 10::bigint;

-- id 11 - Los Angeles
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (11, 1, 1, 34.0, -118.2, '2020-04-07 10:00:00+00', 0)$$);
SELECT is(distance, 4826, 'Panama/Los Angeles') from gk_moves WHERE id = 11::bigint;

-- id 12 - Vancouver
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (12, 1, 1, 49.2, -123.1, '2020-04-07 11:00:00+00', 0)$$);
SELECT is(distance, 1738, 'Los Angeles/Vancouver') from gk_moves WHERE id = 12::bigint;

-- id 13 - Pula
SELECT lives_ok($$INSERT INTO "gk_moves" ("id", "geokret", "author", "lat", "lon", "moved_on_datetime", "move_type")
VALUES (13, 1, 1, 44.8, 13.8, '2020-04-07 12:00:00+00', 0)$$);
SELECT is(distance, 8758, 'Vancouver/Pula') from gk_moves WHERE id = 13::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
