-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(8);

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''
\set berlin '\'0101000020E61000009A99999999992A400000000000404A40\''
\set warsaw '\'0101000020E610000000000000000035409A99999999194A40\''
\set moscow '\'0101000020E6100000CDCCCCCCCCCC42409A99999999D94B40\''
\set tokyo '\'0101000020E61000006666666666766140CDCCCCCCCCCC4140\''
\set pula '\'0101000020E61000009A99999999992B406666666666664640\''

-- lat lon computed
INSERT INTO "gk_waypoints_gc" ("id", "waypoint", "position") VALUES (1, 'GC12345', :nice);
SELECT is(lat, 43.72::double precision, 'latitude is inserted') FROM gk_waypoints_gc WHERE id = 1::bigint;
SELECT is(lon, 7.24::double precision, 'longitude is inserted') FROM gk_waypoints_gc WHERE id = 1::bigint;

-- position computed
INSERT INTO "gk_waypoints_gc" ("id", "waypoint", "lat", "lon") VALUES (2, 'GC12346', 52.5, 13.3);
SELECT is(position, :berlin, 'latitude is inserted') FROM gk_waypoints_gc WHERE id = 2::bigint;

-- country and elevation added
INSERT INTO "gk_waypoints_gc" ("id", "waypoint", "position") VALUES (3, 'GC12347', :paris);
SELECT is(country, 'fr', 'latitude is inserted') FROM gk_waypoints_gc WHERE id = 3::bigint;
SELECT is(elevation, 91, 'longitude is inserted') FROM gk_waypoints_gc WHERE id = 3::bigint;

-- update lat lon compute position
INSERT INTO "gk_waypoints_gc" ("id", "waypoint", "position") VALUES (4, 'GC12348', :moscow);
UPDATE "gk_waypoints_gc" SET lat=52.2, lon=21 WHERE id = 4::bigint;
SELECT is(position, :warsaw, 'update lat lon compute position') FROM gk_waypoints_gc WHERE id = 4::bigint;


-- update position compute lat lon
INSERT INTO "gk_waypoints_gc" ("id", "waypoint", "position") VALUES (5, 'GC12349', :tokyo);
UPDATE "gk_waypoints_gc" SET position=:pula WHERE id = 5::bigint;
SELECT is(lat, 44.8::double precision, 'update position compute lat') FROM gk_waypoints_gc WHERE id = 5::bigint;
SELECT is(lon, 13.8::double precision, 'update position compute lon') FROM gk_waypoints_gc WHERE id = 5::bigint;



-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
