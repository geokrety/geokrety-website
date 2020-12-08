-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(9);

INSERT INTO "gk_geokrety" ("id", "name", "type") VALUES (1, 'test', 0);
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');

-- require uploaded_on_datetime
INSERT INTO "gk_pictures" ("id",  "author", "user", "type") VALUES (1, 1, 1, 2);
SELECT is(pictures_count, 0::integer , 'uploaded_date is necessary for counting') from gk_users WHERE id = 1::bigint;
UPDATE "gk_pictures" set uploaded_on_datetime = NOW() WHERE id = 1::bigint;
SELECT is(pictures_count, 1, 'uploaded_date counting') from gk_users WHERE id = 1::bigint;
UPDATE "gk_pictures" set uploaded_on_datetime = NULL WHERE id = 1::bigint;
SELECT is(pictures_count, 0, 'uploaded_date back to 0') from gk_users WHERE id = 1::bigint;

-- Increment/Decrement
INSERT INTO "gk_pictures" ("id",  "author", "user", "type", "uploaded_on_datetime") VALUES (2, 1, 1, 2, NOW());
SELECT is(pictures_count, 1, 'Pictures count is incremented 1') from gk_users WHERE id = 1::bigint;
INSERT INTO "gk_pictures" ("id",  "author", "user", "type", "uploaded_on_datetime") VALUES (3, 1, 1, 2, NOW());
SELECT is(pictures_count, 2, 'Pictures count is incremented 2') from gk_users WHERE id = 1::bigint;

-- Delete
DELETE FROM "gk_pictures"  WHERE id = 3::bigint;
SELECT is(pictures_count, 1, 'Pictures count is decremented') from gk_users WHERE id = 1::bigint;

-- Update
UPDATE "gk_pictures" set "user"=2 WHERE id = 2::bigint;
SELECT is(pictures_count, 0, 'No pictures left') from gk_users WHERE id = 1::bigint;
SELECT is(pictures_count, 1, 'User 2 has now 1 picture') from gk_users WHERE id = 2::bigint;

-- Update to another type
UPDATE "gk_pictures" set geokret=1, "user"=NULL, type=0 WHERE id = 2::bigint;
SELECT is(pictures_count, 0, 'User 2 has no pictures left') from gk_users WHERE id = 2::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
