BEGIN;
SELECT plan(15);

DELETE FROM stats.gk_country_history;
DELETE FROM stats.job_log WHERE job_name = 'fn_snapshot_gk_country_history';

SELECT has_function('stats', 'fn_snapshot_gk_country_history', ARRAY[]::text[], 'fn_snapshot_gk_country_history function exists');
SELECT function_returns('stats', 'fn_snapshot_gk_country_history', ARRAY[]::text[], 'bigint', 'fn_snapshot_gk_country_history returns bigint');

INSERT INTO gk_users (id, username, registration_ip)
VALUES (25501, 'country-history-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES
  (25510, 'Country History GK 1', 0, 25501, 25501, '2026-02-01 00:00:00+00'),
  (25511, 'Country History GK 2', 0, 25501, 25501, '2026-02-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES
  (25520, 25510, 25501, 'GC255A', 'pl', coords2position(52.22968, 21.01223), '2026-02-01 10:00:00+00', 0),
  (25521, 25510, 25501, NULL, 'pl', coords2position(52.30000, 21.00000), '2026-02-02 10:00:00+00', 3),
  (25522, 25510, 25501, 'GC255C', 'de', coords2position(52.52000, 13.40500), '2026-02-03 10:00:00+00', 5),
  (25523, 25510, 25501, NULL, 'fr', NULL, '2026-02-04 10:00:00+00', 2),
  (25524, 25510, 25501, 'GC255D', 'at', coords2position(48.20817, 16.37382), '2026-02-05 10:00:00+00', 3),
  (25525, 25511, 25501, 'GC255E', 'pl', coords2position(52.22968, 21.01223), '2026-02-03 11:00:00+00', 0);

SELECT is(stats.fn_snapshot_gk_country_history(), 4::bigint, 'full country-history snapshot writes one interval per canonical transition');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 25510), 3::bigint, 'same-country repeats do not create extra intervals');
SELECT is((SELECT arrived_at FROM stats.gk_country_history WHERE geokrety_id = 25510 AND country_code = 'PL'), '2026-02-01 10:00:00+00'::timestamptz, 'PL interval starts at the first PL move');
SELECT is((SELECT departed_at FROM stats.gk_country_history WHERE geokrety_id = 25510 AND country_code = 'PL'), '2026-02-03 10:00:00+00'::timestamptz, 'PL interval closes at the first DE transition');
SELECT is((SELECT departed_at FROM stats.gk_country_history WHERE geokrety_id = 25510 AND country_code = 'DE'), '2026-02-05 10:00:00+00'::timestamptz, 'DE interval closes at the first AT transition');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 25510 AND country_code = 'AT' AND departed_at IS NULL), 1::bigint, 'last canonical country interval stays open');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 25511 AND country_code = 'PL' AND departed_at IS NULL), 1::bigint, 'single-country GeoKret keeps one open interval');

INSERT INTO stats.gk_country_history (id, geokrety_id, country_code, arrived_at, departed_at, move_id)
VALUES (999999, 25510, 'FR', '2026-01-31 00:00:00+00', '2026-02-01 10:00:00+00', 25524);

UPDATE stats.gk_country_history
SET departed_at = '2026-02-02 00:00:00+00'
WHERE geokrety_id = 25510
  AND country_code = 'PL';

SELECT is(stats.fn_snapshot_gk_country_history(), 4::bigint, 'rerunning country-history snapshot repairs stale rows and removes ghosts');
SELECT is((SELECT departed_at FROM stats.gk_country_history WHERE geokrety_id = 25510 AND country_code = 'PL'), '2026-02-03 10:00:00+00'::timestamptz, 'rerun repairs the PL interval close timestamp');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 25510 AND country_code = 'FR'), 0::bigint, 'rerun removes ghost country-history rows');
SELECT ok((SELECT COUNT(*) = 0 FROM stats.gk_country_history a JOIN stats.gk_country_history b ON a.geokrety_id = b.geokrety_id AND a.id < b.id AND tstzrange(a.arrived_at, COALESCE(a.departed_at, 'infinity'::timestamptz), '[)') && tstzrange(b.arrived_at, COALESCE(b.departed_at, 'infinity'::timestamptz), '[)')), 'country-history snapshot preserves the no-overlap invariant');
SELECT ok((SELECT COUNT(*) = 2 FROM stats.job_log WHERE job_name = 'fn_snapshot_gk_country_history' AND status = 'ok'), 'country-history snapshot logs one ok job_log row per execution');
SELECT ok((SELECT metadata ? 'timing_ms' FROM stats.job_log WHERE job_name = 'fn_snapshot_gk_country_history' ORDER BY id DESC LIMIT 1), 'country-history snapshot logs timing metadata');

SELECT * FROM finish();
ROLLBACK;
