-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(6);


-- Valid
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_email") VALUES (1, 'test 1', '127.0.0.1', 'test@geokrety.org');
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip") VALUES (1, 1, 'test+1@geokrety.org', '127.0.0.1');
SELECT is(COUNT(*), 1::bigint, 'Email is inserted') FROM gk_email_activation WHERE id = 1::bigint;

-- one active token per user
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip", "used") VALUES (2, 2, 'test+2@geokrety.org', '127.0.0.1', 0);
SELECT throws_ok($$INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip", "used") VALUES (3, 2, 'test+3@geokrety.org', '127.0.0.1', 0)$$, 'An email activation code already exists for this user');
SELECT throws_ok($$INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip", "used") VALUES (4, 2, 'test+4@geokrety.org', '127.0.0.1', 0)$$, 'An email activation code already exists for this user');

SELECT lives_ok($$
    INSERT INTO "gk_email_activation" ("id", "user", "_email", "used", "requesting_ip", "updating_ip", "used_on_datetime", "reverting_ip", "reverted_on_datetime")
    VALUES (5, 2, 'test+5@geokrety.org', 1, '127.0.0.1', '127.0.0.1', '2020-04-07 00:00:00+00', NULL, NULL)
    $$);
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1');
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip", "used") VALUES (6, 3, 'test+6@geokrety.org', '127.0.0.1', 0);
SELECT throws_ok($$UPDATE "gk_email_activation" SET "user"=3 WHERE id=6$$, 'An email activation code already exists for this user');

-- email already used in email_tokens or users
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_email") VALUES (4, 'test 4', '127.0.0.1', 'test2@geokrety.org');
SELECT throws_ok($$INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip") VALUES (7, 4, 'test2@geokrety.org', '127.0.0.1')$$, 'Email address already used');

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
