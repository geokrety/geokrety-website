-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(3);
\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''

INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'test', 0, '2020-04-07 00:00:00+00');
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_secid") VALUES (1, 'test 1', '127.0.0.1', 'qwertyuiop');
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :nice, '2020-04-07 00:00:00+00', 0);

-- require uploaded_on_datetime
INSERT INTO "gk_pictures" ("id",  "author", "user", "type") VALUES (1, 1, 1, 2);
SELECT is(pictures_count, 0::integer , 'uploaded_date is necessary for counting') from gk_users WHERE id = 1::bigint;

UPDATE "gk_pictures" set uploaded_on_datetime = NOW() WHERE id = 1::bigint;
SELECT is(pictures_count, 1, 'uploaded_date counting') from gk_users WHERE id = 1::bigint;

UPDATE "gk_pictures" set uploaded_on_datetime = NULL WHERE id = 1::bigint;
SELECT is(pictures_count, 0, 'uploaded_date back to 0') from gk_users WHERE id = 1::bigint;


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
