-- Start transaction and plan the tests.

BEGIN;
SELECT plan(49);
-- SELECT * FROM no_plan();

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''
\set berlin '\'0101000020E61000009A99999999992A400000000000404A40\''
\set warsaw '\'0101000020E610000000000000000035409A99999999194A40\''
\set moscow '\'0101000020E6100000CDCCCCCCCCCC42409A99999999D94B40\''
\set tokyo '\'0101000020E61000006666666666766140CDCCCCCCCCCC4140\''

-- Play deferred triggers immediately (moves stats update)
SET CONSTRAINTS ALL IMMEDIATE;

SELECT is(count(*), 0::bigint, 'Stat empty at begining') FROM gk_statistics_counters;

-- Checking users stats
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (1, 'test 1', '127.0.0.1');
SELECT moves_stats_updater();
SELECT is(value, 1::double precision, 'User added') FROM gk_statistics_counters WHERE name='stat_userow';
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (2, 'test 2', '127.0.0.1');
SELECT moves_stats_updater();
SELECT is(value, 2::double precision, 'User added') FROM gk_statistics_counters WHERE name='stat_userow';
INSERT INTO "gk_users" ("id", "username", "registration_ip") VALUES (3, 'test 3', '127.0.0.1');
SELECT moves_stats_updater();
SELECT is(value, 3::double precision, 'User added') FROM gk_statistics_counters WHERE name='stat_userow';
DELETE FROM "gk_users" WHERE id=3::bigint;
SELECT moves_stats_updater();
SELECT is(value, 2::double precision, 'User added') FROM gk_statistics_counters WHERE name='stat_userow';

-- Checking geokrety count stats
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (1, 'gk 1', 0, '2020-08-11 00:00:00+00');
SELECT moves_stats_updater();
SELECT is(value, 1::double precision, 'GeoKrety count') FROM gk_statistics_counters WHERE name='stat_geokretow';
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime", "owner") VALUES (2, 'gk 2', 0, '2020-08-11 00:00:00+00', 1);
SELECT moves_stats_updater();
SELECT is(value, 2::double precision, 'GeoKrety count') FROM gk_statistics_counters WHERE name='stat_geokretow';
INSERT INTO "gk_geokrety" ("id", "name", "type", "created_on_datetime") VALUES (3, 'gk 3', 0, '2020-08-11 00:00:00+00');
SELECT moves_stats_updater();
SELECT is(value, 3::double precision, 'GeoKrety count') FROM gk_statistics_counters WHERE name='stat_geokretow';
DELETE FROM "gk_geokrety" WHERE id=3::bigint;
SELECT moves_stats_updater();
SELECT is(value, 2::double precision, 'GeoKrety count') FROM gk_statistics_counters WHERE name='stat_geokretow';

-- Checking moves count stats
-- drop
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (1, 1, 1, :nice, '2020-08-11 00:00:00+00', 0);
SELECT moves_stats_updater();
SELECT is(value, 1::double precision, 'Moves count') FROM gk_statistics_counters WHERE name='stat_ruchow';
SELECT is(value, 0::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';
SELECT is(value, 0::double precision, 'Moves average') FROM gk_statistics_counters WHERE name='droga_srednia';
SELECT is(value, 0::double precision, 'Moves median') FROM gk_statistics_counters WHERE name='droga_mediana';

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (2, 2, 1, :nice, '2020-08-11 00:00:00+00', 0);
SELECT moves_stats_updater();
SELECT is(value, 2::double precision, 'Moves count') FROM gk_statistics_counters WHERE name='stat_ruchow';
SELECT is(value, 0::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';
SELECT is(value, 0::double precision, 'Moves average') FROM gk_statistics_counters WHERE name='droga_srednia';
SELECT is(value, 0::double precision, 'Moves median') FROM gk_statistics_counters WHERE name='droga_mediana';

-- grab
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (3, 2, 1, NULL, '2020-08-11 01:00:00+00', 1);
SELECT moves_stats_updater();
SELECT is(value, 3::double precision, 'Moves count') FROM gk_statistics_counters WHERE name='stat_ruchow';
SELECT is(value, 0::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';
SELECT is(value, 0::double precision, 'Moves average') FROM gk_statistics_counters WHERE name='droga_srednia';
SELECT is(value, 0::double precision, 'Moves median') FROM gk_statistics_counters WHERE name='droga_mediana';

-- comment
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (4, 2, 1, NULL, '2020-08-11 02:00:00+00', 4);
SELECT moves_stats_updater();
SELECT is(value, 4::double precision, 'Moves count') FROM gk_statistics_counters WHERE name='stat_ruchow';
SELECT is(value, 0::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';
SELECT is(value, 0::double precision, 'Moves average') FROM gk_statistics_counters WHERE name='droga_srednia';
SELECT is(value, 0::double precision, 'Moves median') FROM gk_statistics_counters WHERE name='droga_mediana';

-- delete move
DELETE FROM "gk_moves" WHERE id=4::bigint;
SELECT moves_stats_updater();
SELECT is(value, 3::double precision, 'Moves count') FROM gk_statistics_counters WHERE name='stat_ruchow';
SELECT is(value, 0::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';
SELECT is(value, 0::double precision, 'Moves average') FROM gk_statistics_counters WHERE name='droga_srednia';
SELECT is(value, 0::double precision, 'Moves median') FROM gk_statistics_counters WHERE name='droga_mediana';

-- Checking moves distance stats
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (5, 1, 1, :paris, '2020-08-11 01:00:00+00', 0);
SELECT moves_stats_updater();
SELECT is(value, (680)::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';
SELECT is(value, (SELECT sum(gk_moves.distance) FROM "gk_moves")::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';
-- We already have 3 counting moves
SELECT is(value, 680::double precision/3, 'Moves average') FROM gk_statistics_counters WHERE name='droga_srednia';
SELECT is(value, 0::double precision, 'Moves median') FROM gk_statistics_counters WHERE name='droga_mediana';

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (6, 1, 1, :berlin, '2020-08-11 02:00:00+00', 5);
SELECT moves_stats_updater();
SELECT is(value, (680+877)::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';
SELECT is(value, (SELECT sum(gk_moves.distance) FROM "gk_moves")::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';

INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (7, 1, 1, :warsaw, '2020-08-11 03:00:00+00', 5);
SELECT moves_stats_updater();
SELECT is(value, (680+877+524)::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';
SELECT is(value, (SELECT sum(gk_moves.distance) FROM "gk_moves")::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';

-- update move
UPDATE "gk_moves" SET position=NULL, move_type=2 WHERE id=7::bigint;
SELECT moves_stats_updater();
SELECT is(value, (680+877)::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';
SELECT is(value, (SELECT sum(gk_moves.distance) FROM "gk_moves")::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';

UPDATE "gk_moves" SET position=:warsaw, move_type=0 WHERE id=7::bigint;
SELECT moves_stats_updater();
SELECT is(value, (680+877+524)::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';
SELECT is(value, (SELECT sum(gk_moves.distance) FROM "gk_moves")::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';

UPDATE "gk_moves" SET position=:moscow WHERE id=7::bigint;
SELECT moves_stats_updater();
SELECT is(value, (SELECT sum(gk_moves.distance) FROM "gk_moves")::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';

UPDATE "gk_moves" SET moved_on_datetime='2020-08-11 01:30:00+00' WHERE id=7::bigint;
SELECT moves_stats_updater();
SELECT is(value, (SELECT sum(gk_moves.distance) FROM "gk_moves")::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';

-- GeoKrety in caches
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (8, 1, 1, :nice, '2020-08-11 03:00:00+00', 0);
SELECT moves_stats_updater();
SELECT is(value, 1::double precision, 'GeoKrety in caches') FROM gk_statistics_counters WHERE name='stat_geokretow_zakopanych';
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (9, 2, 1, :paris, '2020-08-11 03:00:00+00', 0);
SELECT moves_stats_updater();
SELECT is(value, 2::double precision, 'GeoKrety in caches') FROM gk_statistics_counters WHERE name='stat_geokretow_zakopanych';

-- Checking moves distance stats relative to natural elements:
DELETE FROM "gk_moves";
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (20, 1, 1, :nice, '2020-08-11 04:00:00+00', 5);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (21, 1, 1, :tokyo, '2020-08-11 05:00:00+00', 5);
INSERT INTO "gk_moves" ("id", "geokret", "author", "position", "moved_on_datetime", "move_type") VALUES (22, 1, 1, :berlin, '2020-08-11 06:00:00+00', 5);
UPDATE "gk_moves" SET distance=149597871 WHERE id=20::bigint;
SELECT moves_stats_updater();
SELECT is(value, 149616775::double precision, 'Moves distance') FROM gk_statistics_counters WHERE name='stat_droga';
-- equator: 40075 km // 3732.947498440424
SELECT is(value, 3733.42::double precision, 'distance equator') FROM gk_statistics_counters WHERE name='stat_droga_obwod';
-- moon: 384400 km // 389.172401144641
SELECT is(value, 389.222::double precision, 'distance moon') FROM gk_statistics_counters WHERE name='stat_droga_ksiezyc';
-- sun: 149597870,700 km
SELECT is(value, 1.00013::double precision, 'distance sun') FROM gk_statistics_counters WHERE name='stat_droga_slonce';


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
