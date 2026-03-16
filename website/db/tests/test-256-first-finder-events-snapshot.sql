BEGIN;
SELECT plan(15);

DELETE FROM stats.first_finder_events;
DELETE FROM stats.job_log WHERE job_name = 'fn_snapshot_first_finder_events';

SELECT has_function('stats', 'fn_snapshot_first_finder_events', ARRAY[]::text[], 'fn_snapshot_first_finder_events function exists');
SELECT function_returns('stats', 'fn_snapshot_first_finder_events', ARRAY[]::text[], 'bigint', 'fn_snapshot_first_finder_events returns bigint');

INSERT INTO gk_users (id, username, registration_ip)
VALUES
  (25601, 'first-finder-owner', '127.0.0.1'),
  (25602, 'first-finder-user-1', '127.0.0.1'),
  (25603, 'first-finder-user-2', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES
  (25610, 'First Finder GK 1', 0, 25601, 25601, '2026-03-01 00:00:00+00'),
  (25611, 'First Finder GK 2', 0, 25601, 25601, '2026-03-01 00:00:00+00'),
  (25612, 'First Finder GK 3', 0, 25601, 25601, '2026-03-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES
  (25620, 25610, 25601, 'GC256A', 'pl', coords2position(52.22968, 21.01223), '2026-03-01 01:00:00+00', 0),
  (25622, 25610, 25602, NULL, 'de', coords2position(52.52000, 13.40500), '2026-03-02 01:00:00+00', 1),
  (25623, 25610, 25603, 'GC256D', 'de', coords2position(52.53000, 13.41000), '2026-03-02 02:00:00+00', 5),
  (25624, 25611, 25602, 'GC256E', 'fr', coords2position(48.85661, 2.35222), '2026-03-08 01:00:01+00', 0),
  (25625, 25612, 25602, NULL, NULL, NULL, '2026-03-01 03:00:00+00', 2),
  (25626, 25612, 25603, 'GC256F', 'at', coords2position(48.20817, 16.37382), '2026-03-01 05:00:00+00', 0);

SELECT is(stats.fn_snapshot_first_finder_events(), 2::bigint, 'full first-finder snapshot writes one canonical row per qualifying GeoKret');
SELECT is((SELECT move_id FROM stats.first_finder_events WHERE gk_id = 25610), 25622::bigint, 'first-finder snapshot chooses the earliest qualifying non-owner move');
SELECT is((SELECT hours_since_creation FROM stats.first_finder_events WHERE gk_id = 25610), 25::smallint, 'first-finder snapshot stores whole hours since creation');
SELECT is((SELECT COUNT(*)::bigint FROM stats.first_finder_events WHERE gk_id = 25611), 0::bigint, 'moves after the 168-hour cutoff are excluded');
SELECT is((SELECT move_id FROM stats.first_finder_events WHERE gk_id = 25612), 25626::bigint, 'non-qualifying comment moves do not win first-finder detection');

UPDATE stats.first_finder_events
SET finder_user_id = 25603
WHERE gk_id = 25610;

INSERT INTO stats.first_finder_events (gk_id, finder_user_id, move_id, move_type, hours_since_creation, found_at, gk_created_at)
VALUES (25611, 25602, 25624, 0, 169, '2026-03-08 01:00:01+00', '2026-03-01 00:00:00+00');

SELECT is(stats.fn_snapshot_first_finder_events(), 2::bigint, 'rerunning first-finder snapshot repairs stale rows and removes ghosts');
SELECT is((SELECT finder_user_id FROM stats.first_finder_events WHERE gk_id = 25610), 25602::bigint, 'rerun repairs the canonical finder_user_id');
SELECT is((SELECT COUNT(*)::bigint FROM stats.first_finder_events WHERE gk_id = 25611), 0::bigint, 'rerun removes ghost rows outside the eligibility window');
SELECT ok((SELECT COUNT(*) = 2 FROM stats.job_log WHERE job_name = 'fn_snapshot_first_finder_events' AND status = 'ok'), 'first-finder snapshot logs one ok job_log row per execution');
SELECT ok((SELECT metadata ? 'timing_ms' FROM stats.job_log WHERE job_name = 'fn_snapshot_first_finder_events' ORDER BY id DESC LIMIT 1), 'first-finder snapshot logs timing metadata');

SELECT * FROM finish();
ROLLBACK;
