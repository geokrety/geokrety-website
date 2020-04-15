-- Start transaction and plan the tests.

BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(2);

\set nice '\'0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540\''
\set paris '\'0101000020E610000066666666666602406666666666664840\''

-- no waypoint duplicate
INSERT INTO "gk_waypoints_gc" ("id", "waypoint", "lat", "lon") VALUES (1, 'GCABC', 40.10, 7.1);
SELECT throws_ok($$INSERT INTO "gk_waypoints_gc" ("id", "waypoint", "lat", "lon") VALUES (2, 'GCABC', 40.1, 7.1)$$);

-- no coordinates duplicate
INSERT INTO "gk_waypoints_gc" ("id", "waypoint", "lat", "lon") VALUES (3, 'GCABCDEF', 40.3, 7.3);
SELECT throws_ok($$INSERT INTO "gk_waypoints_gc" ("id", "waypoint", "lat", "lon") VALUES (4, 'GCABCFGH', 40.3, 7.3)$$);


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
