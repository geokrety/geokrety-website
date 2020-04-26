-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(19);

-- Automatic
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
SELECT isnt(_secid_hash, NULL, 'Automatic add hash') FROM gk_users WHERE id = 1::bigint;
SELECT isnt(_secid_crypt, NULL, 'Automatic add crypt') FROM gk_users WHERE id = 1::bigint;

-- Manual
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_secid") VALUES (2, 'test 2', '127.0.0.1', 'SECRETID');
SELECT is(_secid_hash, public.digest('SECRETID'::text, 'sha256'::text), 'Code can be manually inserted - hash') FROM gk_users WHERE id = 2::bigint;
SELECT is(gkdecrypt(_secid_crypt, 'secretkey'::character varying, 'geokrety'::character varying), 'SECRETID', 'Code can be manually inserted - crypt') FROM gk_users WHERE id = 2::bigint;

-- Uniq
SELECT throws_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip", "_secid") VALUES (3, 'test 3', '127.0.0.1', 'SECRETID')$$, 23505, 'duplicate key value violates unique constraint "gk_users_secid_uniq"');

-- Reset with NULL
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_secid") VALUES (4, 'test 4', '127.0.0.1', 'SECRETID2');
UPDATE "gk_users" SET _secid=NULL WHERE id=4;
SELECT is(_secid, NULL, 'Set to NULL to generate a new one - 0') from gk_users WHERE id = 4::bigint;
SELECT isnt(_secid_hash, public.digest('SECRETID2'::text, 'sha256'::text), 'Set to NULL to generate a new one - 1') FROM gk_users WHERE id = 4::bigint;
SELECT isnt(_secid_hash, NULL, 'Set to NULL to generate a new one - 2') from gk_users WHERE id = 4::bigint;
SELECT isnt(gkdecrypt(_secid_crypt, 'secretkey'::character varying, 'geokrety'::character varying), 'SECRETID2', 'Set to NULL to generate a new one - 3') FROM gk_users WHERE id = 4::bigint;
SELECT isnt(_secid_crypt, NULL, 'Set to NULL to generate a new one - 4') from gk_users WHERE id = 4::bigint;

-- Reset with EMPTY
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_secid") VALUES (5, 'test 5', '127.0.0.1', 'SECRETID3');
UPDATE "gk_users" SET _secid='' WHERE id=5;
SELECT is(_secid, NULL, 'Set to NULL to generate a new one - 0') from gk_users WHERE id = 5::bigint;
SELECT isnt(_secid_hash, public.digest('SECRETID3'::text, 'sha256'::text), 'Set to NULL to generate a new one - 1') FROM gk_users WHERE id = 5::bigint;
SELECT isnt(_secid_hash, NULL, 'Set to NULL to generate a new one - 2') from gk_users WHERE id = 5::bigint;
SELECT isnt(gkdecrypt(_secid_crypt, 'secretkey'::character varying, 'geokrety'::character varying), 'SECRETID3', 'Set to NULL to generate a new one - 3') FROM gk_users WHERE id = 5::bigint;
SELECT isnt(_secid_crypt, NULL, 'Set to NULL to generate a new one - 4') from gk_users WHERE id = 5::bigint;

-- Update other untouched
INSERT INTO "gk_users" ("id", "username", "registration_ip", "_secid") VALUES (6, 'test 6', '127.0.0.1', 'SECRETID6');
UPDATE "gk_users" SET username='My Name' WHERE id=6;
SELECT is(_secid_hash, public.digest('SECRETID6'::text, 'sha256'::text), 'Secid is unmodified - hash') from gk_users WHERE id = 6::bigint;
SELECT is(gkdecrypt(_secid_crypt, 'secretkey'::character varying, 'geokrety'::character varying), 'SECRETID6', 'Secid is unmodified - crypt') FROM gk_users WHERE id = 6::bigint;

-- _secid_hash is readonly
-- _secid_crypt is readonly
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (7, 'test 7', '127.0.0.1');
SELECT throws_ok($$UPDATE "gk_users" SET _secid_hash='Something' WHERE id=7$$, '_secid_hash must not be manually updated');
SELECT throws_ok($$UPDATE "gk_users" SET _secid_crypt='Something' WHERE id=7$$, '_secid_crypt must not be manually updated');


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
