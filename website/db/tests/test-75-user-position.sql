-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(48);

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''
\set berlin '\'0101000020E61000009A99999999992A400000000000404A40\''
\set warsaw '\'0101000020E610000000000000000035409A99999999194A40\''
\set moscow '\'0101000020E6100000CDCCCCCCCCCC42409A99999999D94B40\''
\set tokyo '\'0101000020E61000006666666666766140CDCCCCCCCCCC4140\''

INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'username1', '127.0.0.1');

-- New users don't have home coordinates
SELECT is(gk_users.home_latitude, null) FROM gk_users WHERE id = 1::bigint;
SELECT is(gk_users.home_longitude, null) FROM gk_users WHERE id = 1::bigint;
SELECT is(gk_users.home_country, null) FROM gk_users WHERE id = 1::bigint;
SELECT is(gk_users.home_position, null) FROM gk_users WHERE id = 1::bigint;
SELECT is(gk_users.observation_area, null) FROM gk_users WHERE id = 1::bigint;

-- Set coordinates fill other fields on update
UPDATE "gk_users" SET home_latitude=48.8, home_longitude=2.3 WHERE id = 1::bigint;
SELECT is(
    home_position,
    :paris,
    'Position is automatically synced'
) from gk_users WHERE id = 1::bigint;
SELECT is(gk_users.home_latitude, 48.8::double precision) FROM gk_users WHERE id = 1::bigint;
SELECT is(gk_users.home_longitude, 2.3::double precision) FROM gk_users WHERE id = 1::bigint;
SELECT is(gk_users.home_country, 'fr') FROM gk_users WHERE id = 1::bigint;
SELECT is(gk_users.home_position, :paris) FROM gk_users WHERE id = 1::bigint;
SELECT is(gk_users.observation_area, null) FROM gk_users WHERE id = 1::bigint;

-- Set coordinates fill other fields on insert
INSERT INTO "gk_users" ("id", "username", "registration_ip", "home_latitude", "home_longitude") VALUES (2, 'username2', '127.0.0.1', 48.8, 2.3);
SELECT is(
    home_position,
    :paris,
    'Position is automatically synced'
) from gk_users WHERE id = 2::bigint;
SELECT is(gk_users.home_latitude, 48.8::double precision) FROM gk_users WHERE id = 2::bigint;
SELECT is(gk_users.home_longitude, 2.3::double precision) FROM gk_users WHERE id = 2::bigint;
SELECT is(gk_users.home_country, 'fr') FROM gk_users WHERE id = 2::bigint;
SELECT is(gk_users.home_position, :paris) FROM gk_users WHERE id = 1::bigint;
SELECT is(gk_users.observation_area, null) FROM gk_users WHERE id = 2::bigint;

-- set position update all other
INSERT INTO "gk_users" ("id", "username", "registration_ip", "home_position") VALUES (3, 'username3', '127.0.0.1', :berlin);
SELECT is(gk_users.home_latitude, 52.5::double precision) FROM gk_users WHERE id = 3::bigint;
SELECT is(gk_users.home_longitude, 13.3::double precision) FROM gk_users WHERE id = 3::bigint;
SELECT is(gk_users.home_country, 'de') FROM gk_users WHERE id = 3::bigint;
SELECT is(gk_users.home_position, :berlin) FROM gk_users WHERE id = 3::bigint;

-- set lat to null empty all others
INSERT INTO "gk_users" ("id", "username", "registration_ip", "home_position") VALUES (4, 'username4', '127.0.0.1', :warsaw);
UPDATE "gk_users" SET home_latitude=NULL WHERE id = 4::bigint;
SELECT is(gk_users.home_latitude, null) FROM gk_users WHERE id = 4::bigint;
SELECT is(gk_users.home_longitude, null) FROM gk_users WHERE id = 4::bigint;
SELECT is(gk_users.home_country, null) FROM gk_users WHERE id = 4::bigint;
SELECT is(gk_users.home_position, null) FROM gk_users WHERE id = 4::bigint;
SELECT is(gk_users.observation_area, null) FROM gk_users WHERE id = 4::bigint;

-- set lon to null empty all others
INSERT INTO "gk_users" ("id", "username", "registration_ip", "home_position") VALUES (5, 'username5', '127.0.0.1', :warsaw);
UPDATE "gk_users" SET home_longitude=NULL WHERE id = 5::bigint;
SELECT is(gk_users.home_latitude, null) FROM gk_users WHERE id = 5::bigint;
SELECT is(gk_users.home_longitude, null) FROM gk_users WHERE id = 5::bigint;
SELECT is(gk_users.home_country, null) FROM gk_users WHERE id = 5::bigint;
SELECT is(gk_users.home_position, null) FROM gk_users WHERE id = 5::bigint;
SELECT is(gk_users.observation_area, null) FROM gk_users WHERE id = 5::bigint;

-- set lat/lon has precedence over position
INSERT INTO "gk_users" ("id", "username", "registration_ip", "home_latitude", "home_longitude", "home_position") VALUES (6, 'username6', '127.0.0.1', 55.7, 37.6, :warsaw);
SELECT is(gk_users.home_latitude, 55.7::double precision) FROM gk_users WHERE id = 6::bigint;
SELECT is(gk_users.home_longitude, 37.6::double precision) FROM gk_users WHERE id = 6::bigint;
SELECT is(gk_users.home_country, 'ru') FROM gk_users WHERE id = 6::bigint;
SELECT is(gk_users.home_position, :moscow) FROM gk_users WHERE id = 6::bigint;
SELECT is(gk_users.observation_area, null) FROM gk_users WHERE id = 6::bigint;

-- home_country is not manually updatable
INSERT INTO "gk_users" ("id", "username", "registration_ip", "home_country") VALUES (7, 'username7', '127.0.0.1', 'pl');
SELECT is(gk_users.home_latitude, null) FROM gk_users WHERE id = 7::bigint;
SELECT is(gk_users.home_longitude, null) FROM gk_users WHERE id = 7::bigint;
SELECT is(gk_users.home_country, null) FROM gk_users WHERE id = 7::bigint;
SELECT is(gk_users.home_position, null) FROM gk_users WHERE id = 7::bigint;
SELECT is(gk_users.observation_area, null) FROM gk_users WHERE id = 7::bigint;

INSERT INTO "gk_users" ("id", "username", "registration_ip", "home_position") VALUES (8, 'username8', '127.0.0.1', :tokyo);
SELECT is(gk_users.home_country, 'jp') FROM gk_users WHERE id = 8::bigint;
UPDATE "gk_users" SET home_country=NULL WHERE id = 8::bigint;
SELECT is(gk_users.home_country, 'jp') FROM gk_users WHERE id = 8::bigint;
UPDATE "gk_users" SET home_country='fr' WHERE id = 8::bigint;
SELECT is(gk_users.home_country, 'jp') FROM gk_users WHERE id = 8::bigint;

-- observation area is null when <= 0
INSERT INTO "gk_users" ("id", "username", "registration_ip", "observation_area") VALUES (9, 'username9', '127.0.0.1', 0);
SELECT is(gk_users.observation_area, NULL) FROM gk_users WHERE id = 9::bigint;
UPDATE "gk_users" SET observation_area=-1 WHERE id = 9::bigint;
SELECT is(gk_users.observation_area, NULL) FROM gk_users WHERE id = 9::bigint;

-- observation area can be set > 0
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (10, 'username10', '127.0.0.1');
UPDATE "gk_users" SET observation_area=1 WHERE id = 10::bigint;
SELECT is(gk_users.observation_area, 1::smallint) FROM gk_users WHERE id = 10::bigint;
UPDATE "gk_users" SET observation_area=NULL WHERE id = 10::bigint;
SELECT is(gk_users.observation_area, NULL) FROM gk_users WHERE id = 10::bigint;

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
;
