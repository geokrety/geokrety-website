-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(10);

-- Automatic
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
SELECT lives_ok($$INSERT INTO "gk_email_activation" ("id", "user", "_email", "used", "requesting_ip", "updating_ip", "used_on_datetime", "_previous_email")
    VALUES (1, 1, 'test+1@geokrety.org', 1, '127.0.0.1', '127.0.0.1', '2020-04-07 00:00:00+00', 'test+0@geokrety.org')$$);

SELECT is(_previous_email, NULL, 'Previous email is NULL') FROM gk_email_activation WHERE id = 1::bigint;
SELECT is(_previous_email_hash, public.digest('test+0@geokrety.org'::text, 'sha256'::text), 'Previous email is hashed') FROM gk_email_activation WHERE id = 1::bigint;
SELECT is(gkdecrypt(_previous_email_crypt, 'secretkey'::character varying, 'geokrety'::character varying), 'test+0@geokrety.org', 'Previous email is crypted') FROM gk_email_activation WHERE id = 1::bigint;

-- Update other untouched
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1');
SELECT lives_ok($$INSERT INTO "gk_email_activation" ("id", "user", "_email", "used", "requesting_ip", "updating_ip", "used_on_datetime", "_previous_email")
    VALUES (2, 1, 'test+2@geokrety.org', 1, '127.0.0.1', '127.0.0.1', '2020-04-07 00:00:00+00', 'test+0@geokrety.org')$$);
SELECT lives_ok($$UPDATE "gk_email_activation" SET "user"=3 WHERE id=2$$);
SELECT is(_previous_email, NULL, 'Previous email stay NULL') FROM gk_email_activation WHERE id = 4::bigint;
SELECT is(_previous_email_hash, public.digest('test+0@geokrety.org'::text, 'sha256'::text), 'Previous email stay hashed') FROM gk_email_activation WHERE id = 2::bigint;
SELECT is(gkdecrypt(_previous_email_crypt, 'secretkey'::character varying, 'geokrety'::character varying), 'test+0@geokrety.org', 'Previous email stay crypted') FROM gk_email_activation WHERE id = 2::bigint;

-- _previous_email_hash is readonly
-- _previous_email_crypt is readonly
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (5, 'test 5', '127.0.0.1');
SELECT throws_ok($$INSERT INTO "gk_email_activation" ("id", "user", "_email", "used", "requesting_ip", "updating_ip", "used_on_datetime", "_previous_email_hash")
    VALUES (1, 1, 'test+1@geokrety.org', 1, '127.0.0.1', '127.0.0.1', '2020-04-07 00:00:00+00', 'test+0@geokrety.org')$$, '_previous_email_hash must not be manually updated');
SELECT throws_ok($$INSERT INTO "gk_email_activation" ("id", "user", "_email", "used", "requesting_ip", "updating_ip", "used_on_datetime", "_previous_email_crypt")
    VALUES (1, 1, 'test+1@geokrety.org', 1, '127.0.0.1', '127.0.0.1', '2020-04-07 00:00:00+00', 'test+0@geokrety.org')$$, '_previous_email_crypt must not be manually updated');

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
