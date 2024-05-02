-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(13);

-- Default to NULL
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
SELECT is(_email, NULL) FROM gk_users WHERE id = 1::bigint;
SELECT is(_email_hash, NULL) FROM gk_users WHERE id = 1::bigint;
SELECT is(_email_crypt, NULL, 'Automatic add crypt') FROM gk_users WHERE id = 1::bigint;

-- Set email via _email field
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_email") VALUES (2, 'test 2', '127.0.0.1', 'test2@geokrety.org');
SELECT is(_email, NULL) FROM gk_users WHERE id = 2::bigint;
SELECT isnt(_email_hash, NULL) FROM gk_users WHERE id = 2::bigint;
SELECT isnt(_email_crypt, NULL, 'Automatic add crypt') FROM gk_users WHERE id = 2::bigint;
SELECT is(gkdecrypt(_email_crypt, 'secretkey'::character varying, 'geokrety'::character varying), 'test2@geokrety.org') FROM gk_users WHERE id = 4::bigint;

-- Set email hash are case insensitive
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_email") VALUES (3, 'test 3', '127.0.0.1', 'TEST3@GeoKrety.OrG');
SELECT is(_email, NULL) FROM gk_users WHERE id = 3::bigint;
SELECT isnt(_email_hash, NULL) FROM gk_users WHERE id = 3::bigint;
SELECT is(gkdecrypt(_email_crypt, 'secretkey'::character varying, 'geokrety'::character varying), 'TEST3@GeoKrety.OrG') FROM gk_users WHERE id = 4::bigint;
SELECT is(_email_hash, public.digest('test3@geokrety.org', 'sha256')) FROM gk_users WHERE id = 3::bigint;

-- Reset with EMPTY
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (4, 'test 4', '127.0.0.1');
UPDATE "gk_users" SET _email='' WHERE id=4;
SELECT is(_email_hash, NULL) FROM gk_users WHERE id = 4::bigint;
SELECT is(_email_crypt, NULL, 'Automatic add crypt') FROM gk_users WHERE id = 4::bigint;

-- _secid_hash is readonly
-- _secid_crypt is readonly
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (5, 'test 5', '127.0.0.1');
SELECT throws_ok($$UPDATE "gk_users" SET _email_hash='Something' WHERE id=5$$, '_email_hash must not be manually updated');
SELECT throws_ok($$UPDATE "gk_users" SET _email_crypt='Something' WHERE id=5$$, '_email_crypt must not be manually updated');


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
