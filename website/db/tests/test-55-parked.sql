-- Start transaction and plan the tests.

BEGIN;
SELECT * FROM no_plan();
-- SELECT plan(16);

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''
\set move_type_dropped 0
\set move_type_grabbed 1
\set move_type_comment 2
\set move_type_seen 3
\set move_type_archived 4
\set move_type_dipped 5

SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1')$$);
SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1')$$);
SELECT lives_ok($$INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1')$$);

-- parked can be set
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (1, 'test', 0, '2024-05-24 00:00:00+00', 1)$$);
SELECT is(owner, 1::bigint) FROM "gk_geokrety" WHERE id = 1::bigint;
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 1::bigint;
SELECT is(parked, NULL, 'Is null by default') FROM gk_geokrety WHERE id = 1::bigint;
SELECT is(COUNT(*), 0::bigint) FROM gk_moves WHERE geokret = 1::bigint;
SELECT lives_ok($$UPDATE gk_geokrety SET parked='2024-05-24 00:00:01+00' WHERE id = 1::bigint$$);
SELECT is(parked, '2024-05-24 00:00:01+00') FROM gk_geokrety WHERE id = 1::bigint;
-- non_collectible flag is automatically set
SELECT is(non_collectible, '2024-05-24 00:00:01+00') FROM gk_geokrety WHERE id = 1::bigint;
SELECT is(owner, 1::bigint) FROM "gk_geokrety" WHERE id = 1::bigint;
SELECT is(holder, 1::bigint) FROM "gk_geokrety" WHERE id = 1::bigint;
SELECT is(COUNT(*), 1::bigint) FROM gk_moves WHERE geokret = 1::bigint;

-- parked can be removed
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner", "parked") VALUES (2, 'test', 0, '2024-05-24 00:00:00+00', 1, '2024-05-24 00:00:01+00')$$);
SELECT is(parked, '2024-05-24 00:00:01+00') FROM gk_geokrety WHERE id = 2::bigint;
SELECT is(non_collectible, '2024-05-24 00:00:01+00') FROM gk_geokrety WHERE id = 2::bigint;
SELECT is(COUNT(*), 1::bigint) FROM gk_moves WHERE geokret = 2::bigint;
UPDATE gk_geokrety SET parked=NULL WHERE id = 2::bigint;
SELECT is(parked, NULL) FROM gk_geokrety WHERE id = 2::bigint;
-- Non collectible is automatically removed
SELECT is(non_collectible, NULL) FROM gk_geokrety WHERE id = 2::bigint;
SELECT is(COUNT(*), 2::bigint) FROM gk_moves WHERE geokret = 2::bigint;

-- non_collectible cannot be removed alone
SELECT lives_ok($$INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner", "parked") VALUES (3, 'test', 0, '2024-05-24 00:00:00+00', 1, '2024-05-24 00:00:01+00')$$);
SELECT is(parked, '2024-05-24 00:00:01+00') FROM gk_geokrety WHERE id = 3::bigint;
SELECT is(COUNT(*), 1::bigint) FROM gk_moves WHERE geokret = 3::bigint;
UPDATE gk_geokrety SET non_collectible=NULL WHERE id = 3::bigint;
SELECT is(parked, '2024-05-24 00:00:01+00') FROM gk_geokrety WHERE id = 3::bigint;
-- Non collectible is automatically reverted
SELECT is(non_collectible, '2024-05-24 00:00:01+00') FROM gk_geokrety WHERE id = 3::bigint;
SELECT is(COUNT(*), 1::bigint) FROM gk_moves WHERE geokret = 3::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
