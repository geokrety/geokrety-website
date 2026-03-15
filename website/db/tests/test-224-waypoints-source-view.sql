BEGIN;
SELECT plan(12);

INSERT INTO gk_waypoints_gc (id, waypoint, lat, lon, country)
VALUES (22401, 'dupet1', 52.22968, 21.01223, 'pl');

INSERT INTO gk_waypoints_oc (id, waypoint, lat, lon, country)
VALUES (22402, 'dupet1', 50.07550, 14.43780, 'cz');

INSERT INTO gk_waypoints_oc (id, waypoint, lat, lon, country)
VALUES (22403, 'ocsolo1', 48.20820, 16.37380, 'at');

INSERT INTO gk_waypoints_gc (id, waypoint, lat, lon, country)
VALUES (22404, '   ', 43.71017, 7.26195, 'fr');

SELECT has_view('stats', 'v_waypoints_source_union', 'stats.v_waypoints_source_union view exists');
SELECT has_column('stats', 'v_waypoints_source_union', 'waypoint_code', 'view exposes waypoint_code');
SELECT has_column('stats', 'v_waypoints_source_union', 'source', 'view exposes source');
SELECT has_column('stats', 'v_waypoints_source_union', 'lat', 'view exposes lat');
SELECT has_column('stats', 'v_waypoints_source_union', 'lon', 'view exposes lon');
SELECT has_column('stats', 'v_waypoints_source_union', 'country', 'view exposes country');
SELECT is((SELECT COUNT(DISTINCT source)::bigint FROM stats.v_waypoints_source_union WHERE waypoint_code IN ('DUPET1', 'OCSOLO1')), 2::bigint, 'view exposes both GC and OC sources');
SELECT is((SELECT COUNT(*)::bigint FROM stats.v_waypoints_source_union WHERE waypoint_code = 'DUPET1'), 2::bigint, 'view preserves duplicates with UNION ALL');
SELECT is((SELECT COUNT(*)::bigint FROM stats.v_waypoints_source_union WHERE waypoint_code IN ('DUPET1', 'OCSOLO1') AND waypoint_code <> UPPER(waypoint_code)), 0::bigint, 'view uppercases waypoint_code');
SELECT is((SELECT COUNT(*)::bigint FROM stats.v_waypoints_source_union WHERE waypoint_code IN ('DUPET1', 'OCSOLO1') AND country IS NOT NULL AND country <> UPPER(country)), 0::bigint, 'view uppercases country');
SELECT is((SELECT COUNT(*)::bigint FROM stats.v_waypoints_source_union WHERE BTRIM(waypoint_code) = ''), 0::bigint, 'view excludes blank waypoint codes');
SELECT is((SELECT COUNT(*)::bigint FROM stats.v_waypoints_source_union WHERE waypoint_code IS NULL), 0::bigint, 'view excludes NULL waypoint codes');

SELECT * FROM finish();
ROLLBACK;
