-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(1);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 1', '127.0.0.1')$$, 23505, 'duplicate key value violates unique constraint "gk_users_uniq_username"');

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;