-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(4);

-- token is automatically added
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_email_revalidate" ("id", "user", "_email") VALUES (1, 1, 'test+1@geokrety.org');
SELECT is(null, _email, '_email is back to null') FROM gk_email_revalidate WHERE id = 1::bigint;
SELECT isnt(null, _email_hash, 'email is hashed') FROM gk_email_revalidate WHERE id = 1::bigint;
SELECT isnt(null, _email_crypt, 'email is crypted') FROM gk_email_revalidate WHERE id = 1::bigint;
SELECT is(public.digest('test+1@geokrety.org', 'sha256'), _email_hash, 'email has hash') FROM gk_email_revalidate WHERE id = 1::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
