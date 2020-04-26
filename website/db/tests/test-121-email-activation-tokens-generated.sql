-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(8);

-- token is automatically added
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip") VALUES (1, 1, 'test+1@geokrety.org', '127.0.0.1');
SELECT is(LENGTH(token), 42, 'token is automatically added') FROM gk_email_activation WHERE id = 1::bigint;
SELECT is(LENGTH(revert_token), 42, 'token is automatically added') FROM gk_email_activation WHERE id = 1::bigint;

-- Manual
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip", "token", "revert_token") VALUES (2, 2, 'test+2@geokrety.org', '127.0.0.1', 'TOKEN1', 'TOKEN2');
SELECT is(token, 'TOKEN1', 'Token can be manually inserted') FROM gk_email_activation WHERE id = 2::bigint;
SELECT is(revert_token, 'TOKEN2', 'Token can be manually inserted') FROM gk_email_activation WHERE id = 2::bigint;

-- Reset
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1');
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip", "token", "revert_token") VALUES (3, 3, 'test+3@geokrety.org', '127.0.0.1', 'TOKEN1', 'TOKEN2');
UPDATE "gk_email_activation" SET token=NULL, revert_token=NULL WHERE id=3;
SELECT isnt(token, 'TOKEN1', 'Reset') FROM gk_email_activation WHERE id = 3::bigint;
SELECT isnt(revert_token, 'TOKEN2', 'Reset') FROM gk_email_activation WHERE id = 3::bigint;
SELECT isnt(token, NULL, 'Not null') FROM gk_email_activation WHERE id = 3::bigint;
SELECT isnt(revert_token, NULL, 'Not null') FROM gk_email_activation WHERE id = 3::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
