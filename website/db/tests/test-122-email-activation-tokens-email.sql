-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(11);


-- Automatic
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip") VALUES (1, 1, 'test@geokrety.org', '127.0.0.1');
SELECT is(_email, NULL, 'Email is NULL') FROM gk_email_activation WHERE id = 1::bigint;
SELECT is(_email_hash, public.digest('test@geokrety.org'::text, 'sha256'::text), 'Email is hashed') FROM gk_email_activation WHERE id = 1::bigint;
SELECT is(gkdecrypt(_email_crypt, 'secretkey'::character varying, 'geokrety'::character varying), 'test@geokrety.org', 'Email is crypted') FROM gk_email_activation WHERE id = 1::bigint;

-- Cannot be null
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
SELECT throws_ok($$INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip") VALUES (2, 2, '', '127.0.0.1')$$, 23502, 'null value in column "_email_crypt" of relation "gk_email_activation" violates not-null constraint');
SELECT throws_ok($$INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip") VALUES (3, 2, NULL, '127.0.0.1')$$, 23502, 'null value in column "_email_crypt" of relation "gk_email_activation" violates not-null constraint');

-- Update other untouched
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1');
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (4, 'test 4', '127.0.0.1');
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip") VALUES (4, 3, 'test+1@geokrety.org', '127.0.0.1');
SELECT lives_ok($$UPDATE "gk_email_activation" SET "user"=4 WHERE id=4$$);
SELECT is(_email, NULL, 'Email stay NULL') FROM gk_email_activation WHERE id = 4::bigint;
SELECT is(_email_hash, public.digest('test+1@geokrety.org'::text, 'sha256'::text), 'Email stay hashed') FROM gk_email_activation WHERE id = 4::bigint;
SELECT is(gkdecrypt(_email_crypt, 'secretkey'::character varying, 'geokrety'::character varying), 'test+1@geokrety.org', 'Email stay crypted') FROM gk_email_activation WHERE id = 4::bigint;


-- _email_hash is readonly
-- _email_crypt is readonly
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (5, 'test 5', '127.0.0.1');
INSERT INTO "gk_email_activation" ("id", "user", "_email", "requesting_ip") VALUES (5, 5, 'test+2@geokrety.org', '127.0.0.1');
SELECT throws_ok($$UPDATE "gk_email_activation" SET _email_hash='Something' WHERE id=5$$, '_email_hash must not be manually updated');
SELECT throws_ok($$UPDATE "gk_email_activation" SET _email_crypt='Something' WHERE id=5$$, '_email_crypt must not be manually updated');

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
