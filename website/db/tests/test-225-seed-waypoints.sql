BEGIN;
SELECT plan(10);

DELETE FROM stats.job_log WHERE job_name = 'fn_seed_waypoints';
DELETE FROM stats.waypoints WHERE waypoint_code LIKE 'S4T25%';
DELETE FROM gk_waypoints_gc WHERE id BETWEEN 22501 AND 22509;
DELETE FROM gk_waypoints_oc WHERE id BETWEEN 22511 AND 22519;

INSERT INTO gk_waypoints_gc (id, waypoint, lat, lon, country)
VALUES
  (22501, 's4t25dup', 52.22968, 21.01223, 'pl'),
  (22502, 's4t25gcs', 43.71017, 7.26195, 'fr');

INSERT INTO gk_waypoints_oc (id, waypoint, lat, lon, country)
VALUES
  (22511, 's4t25dup', 50.07550, 14.43780, 'cz'),
  (22512, 's4t25ocs', 52.52000, 13.40500, 'de');

SELECT has_function('stats', 'fn_seed_waypoints', ARRAY[]::text[], 'stats.fn_seed_waypoints function exists');
SELECT function_returns('stats', 'fn_seed_waypoints', ARRAY[]::text[], 'bigint', 'stats.fn_seed_waypoints returns bigint');
SELECT is((SELECT COUNT(*)::bigint FROM stats.waypoints WHERE waypoint_code LIKE 'S4T25%'), 0::bigint, 'test waypoint rows start absent');
SELECT is((SELECT stats.fn_seed_waypoints()), 3::bigint, 'seed helper inserts the deterministic fixture set once');
SELECT results_eq(
  $$SELECT waypoint_code, source::text, country::text FROM stats.waypoints WHERE waypoint_code LIKE 'S4T25%' ORDER BY waypoint_code$$,
  $$VALUES
    ('S4T25DUP'::varchar, 'GC'::text, 'PL'::text),
    ('S4T25GCS'::varchar, 'GC'::text, 'FR'::text),
    ('S4T25OCS'::varchar, 'OC'::text, 'DE'::text)$$,
  'seed helper uppercases rows, deduplicates by code, and prefers GC over OC duplicates'
);
SELECT is((SELECT COUNT(*)::bigint FROM stats.waypoints WHERE waypoint_code LIKE 'S4T25%' AND waypoint_code <> UPPER(waypoint_code)), 0::bigint, 'all seeded waypoint codes are uppercase');
SELECT is((SELECT COUNT(*)::bigint FROM stats.waypoints WHERE waypoint_code LIKE 'S4T25%' AND country IS NOT NULL AND country <> UPPER(country)), 0::bigint, 'all seeded countries are uppercase');
SELECT is((SELECT stats.fn_seed_waypoints()), 0::bigint, 're-running the seed function is idempotent for the deterministic fixture set');
SELECT cmp_ok((SELECT COUNT(*) FROM stats.job_log WHERE job_name = 'fn_seed_waypoints'), '>', 0::bigint, 'seed function writes to stats.job_log');
SELECT is((SELECT COUNT(*)::bigint FROM stats.waypoints WHERE waypoint_code LIKE 'S4T25%' AND waypoint_code IS NULL), 0::bigint, 'seeded table has no NULL waypoint_code values for the deterministic fixture set');

SELECT * FROM finish();
ROLLBACK;
