-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(5);

-- token is automatically added
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_email_revalidate" ("id", "user", "_email") VALUES (1, 1, 'test+1@geokrety.org');
SELECT is(LENGTH(token), 42, 'token is automatically added') FROM gk_email_revalidate WHERE id = 1::bigint;

-- Manual
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "token") VALUES (2, 2, 'test+2@geokrety.org', 'TOKEN1');
SELECT is(token, 'TOKEN1', 'Token can be manually inserted') FROM gk_email_revalidate WHERE id = 2::bigint;

-- can be updated
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1');
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "token") VALUES (3, 3, 'test+3@geokrety.org', 'TOKEN2');
SELECT lives_ok($$UPDATE "gk_email_revalidate" SET token = NULL WHERE id = 3::bigint$$);
SELECT lives_ok($$UPDATE "gk_email_revalidate" SET token = 'TOKEN3' WHERE id = 3::bigint$$);

-- tokens are unique
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (4, 'test 4', '127.0.0.1');
INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "token") VALUES (4, 4, 'test+4@geokrety.org', 'TOKEN4');
SELECT throws_ok($$INSERT INTO "gk_email_revalidate" ("id", "user", "_email", "token") VALUES (5, 4, 'test+5@geokrety.org', 'TOKEN4')$$);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
