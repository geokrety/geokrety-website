-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(19);


-- Valid
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_email") VALUES (1, 'test 1', '127.0.0.1', 'test@geokrety.org');
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip") VALUES (1, 1, 'test+1@geokrety.org', '127.0.0.1');
SELECT is(COUNT(*), 1::bigint, 'Email is inserted') FROM gk_email_activation WHERE id = 1::bigint;

-- one active token per user
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip", "used") VALUES (2, 2, 'test+2@geokrety.org', '127.0.0.1', 0);
SELECT is(used, 0::smallint, 'used=0') FROM gk_email_activation WHERE id = 2::bigint;
SELECT lives_ok($$INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip", "used") VALUES (3, 2, 'test+3@geokrety.org', '127.0.0.1', 0)$$, 'An email activation code already exists for this user');
SELECT is(used, 4::smallint, 'used=4') FROM gk_email_activation WHERE id = 2::bigint;
SELECT is(used, 0::smallint, 'used=0') FROM gk_email_activation WHERE id = 3::bigint;
SELECT lives_ok($$INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip", "used") VALUES (4, 2, 'test+4@geokrety.org', '127.0.0.1', 0)$$, 'An email activation code already exists for this user');
SELECT is(used, 4::smallint, 'used=4') FROM gk_email_activation WHERE id = 2::bigint;
SELECT is(used, 4::smallint, 'used=4') FROM gk_email_activation WHERE id = 3::bigint;
SELECT is(used, 0::smallint, 'used=0') FROM gk_email_activation WHERE id = 4::bigint;

-- Inserting an already used token doesn't change active token
SELECT lives_ok($$
    INSERT INTO "gk_email_activation" ("id", "user", "_email", "used", "requesting_ip", "updating_ip", "used_on_datetime", "reverting_ip", "reverted_on_datetime")
    VALUES (5, 2, 'test+5@geokrety.org', 1, '127.0.0.1', '127.0.0.1', '2020-04-07 00:00:00+00', NULL, NULL)
    $$);
SELECT is(used, 4::smallint, 'used=4') FROM gk_email_activation WHERE id = 2::bigint;
SELECT is(used, 4::smallint, 'used=4') FROM gk_email_activation WHERE id = 3::bigint;
SELECT is(used, 0::smallint, 'used=0') FROM gk_email_activation WHERE id = 4::bigint;
SELECT is(used, 1::smallint, 'used=1') FROM gk_email_activation WHERE id = 5::bigint;


INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1');
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip", "used") VALUES (6, 3, 'test+6@geokrety.org', '127.0.0.1', 0);
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip", "used") VALUES (7, 3, 'test+7@geokrety.org', '127.0.0.1', 0);
SELECT is(used, 4::smallint, 'used=4') FROM gk_email_activation WHERE id = 6::bigint;
SELECT is(used, 0::smallint, 'used=0') FROM gk_email_activation WHERE id = 7::bigint;
SELECT lives_ok($$UPDATE "gk_email_activation" SET "user"=3 WHERE id=6$$, 'Updating to same user - fight against: tuple to be updated was already modified by an operation triggered by the current comman ');
SELECT is(used, 0::smallint, 'used=0') FROM gk_email_activation WHERE id = 7::bigint;

-- email already used in email_tokens or users
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_email") VALUES (4, 'test 4', '127.0.0.1', 'test2@geokrety.org');
SELECT throws_ok($$INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip") VALUES (8, 4, 'test2@geokrety.org', '127.0.0.1')$$, 'Email address already used');

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
