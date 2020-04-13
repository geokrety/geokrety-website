-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(5);

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');

-- token is automatically added
INSERT INTO "gk_mails" ("id", "subject", "content", "ip") VALUES (1, 'subject', 'content', '127.0.0.1');
SELECT is(LENGTH(token), 10, 'token is automatically added') FROM gk_mails WHERE id = 1::bigint;

-- Manual
INSERT INTO "gk_mails" ("id", "subject", "content", "ip", "token") VALUES (2, 'subject', 'content', '127.0.0.1', 'TOKEN1');
SELECT is(token, 'TOKEN1', 'Token can be manually inserted') FROM gk_mails WHERE id = 2::bigint;

-- cannot be updated
INSERT INTO "gk_mails" ("id", "subject", "content", "ip", "token") VALUES (3, 'subject', 'content', '127.0.0.1', 'TOKEN2');
SELECT throws_ok($$UPDATE "gk_mails" SET token = NULL WHERE id = 3::bigint$$);
SELECT throws_ok($$UPDATE "gk_mails" SET token = 'TOKEN3' WHERE id = 3::bigint$$);

-- tokens are unique
INSERT INTO "gk_mails" ("id", "subject", "content", "ip", "token") VALUES (4, 'subject', 'content', '127.0.0.1', 'TOKEN3');
SELECT throws_ok($$INSERT INTO "gk_mails" ("id", "subject", "content", "ip", "token") VALUES (5, 'subject', 'content', '127.0.0.1', 'TOKEN3')$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
