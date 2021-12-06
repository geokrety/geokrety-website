-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(7);
-- reset sequence
ALTER SEQUENCE gk_users_username_history_id_seq RESTART WITH 1;

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'username1', '127.0.0.1');

-- New users are not logged
SELECT is(count(*), 0::bigint) FROM gk_users_username_history WHERE "user" = 1::bigint;

-- username change record old and new password
UPDATE "gk_users" SET "username"='foo' WHERE id = 1::bigint;
SELECT is(count(*), 1::bigint) FROM gk_users_username_history WHERE "user" = 1::bigint;
SELECT is(username_old, 'username1') FROM gk_users_username_history WHERE id = 1::bigint;
SELECT is(username_new, 'foo') FROM gk_users_username_history WHERE id = 1::bigint;

UPDATE "gk_users" SET "username"='fooBar' WHERE id = 1::bigint;
SELECT is(count(*), 2::bigint) FROM gk_users_username_history WHERE "user" = 1::bigint;
SELECT is(username_old, 'foo') FROM gk_users_username_history WHERE id = 2::bigint;
SELECT is(username_new, 'fooBar') FROM gk_users_username_history WHERE id = 2::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
;
