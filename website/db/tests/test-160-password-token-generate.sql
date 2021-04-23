-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(5);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- token is automatically added
INSERT INTO "gk_password_tokens" ("id", "user", "requesting_ip") VALUES (1, 1, '127.0.0.1');
SELECT is(LENGTH(token), 42, 'token is automatically added') FROM gk_password_tokens WHERE id = 1::bigint;

-- cannot be overridden
INSERT INTO "gk_password_tokens" ("id", "user", "requesting_ip", "token") VALUES (2, 1, '127.0.0.1', 'TOKEN1');
SELECT isnt(token, 'TOKEN1', 'Token can be manually inserted') FROM gk_password_tokens WHERE id = 2::bigint;

-- cannot be updated
INSERT INTO "gk_password_tokens" ("id", "user", "requesting_ip", "token") VALUES (3, 1, '127.0.0.1', 'TOKEN2');
SELECT throws_ok($$UPDATE "gk_password_tokens" SET token = NULL WHERE id = 3::bigint$$);
SELECT throws_ok($$UPDATE "gk_password_tokens" SET token = 'TOKEN3' WHERE id = 3::bigint$$);

-- tokens are unique
INSERT INTO "gk_password_tokens" ("id", "user", "requesting_ip", "token") VALUES (4, 1, '127.0.0.1', 'TOKEN3');
SELECT throws_ok($$INSERT INTO "gk_password_tokens" ("id", "geokret", "requesting_ip", "token") VALUES (5, 1, '127.0.0.1', 'TOKEN3')$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
