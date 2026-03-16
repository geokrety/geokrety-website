BEGIN;
SELECT plan(17);

DELETE FROM stats.gk_milestone_events;
DELETE FROM stats.first_finder_events;
DELETE FROM stats.job_log WHERE job_name IN ('fn_snapshot_first_finder_events', 'fn_snapshot_gk_milestone_events');

SELECT has_function('stats', 'fn_snapshot_gk_milestone_events', ARRAY[]::text[], 'fn_snapshot_gk_milestone_events function exists');
SELECT function_returns('stats', 'fn_snapshot_gk_milestone_events', ARRAY[]::text[], 'bigint', 'fn_snapshot_gk_milestone_events returns bigint');

INSERT INTO gk_users (id, username, registration_ip)
VALUES (25701, 'milestone-owner', '127.0.0.1');

INSERT INTO gk_users (id, username, registration_ip)
SELECT series_id, format('milestone-user-%s', series_id), '127.0.0.1'
FROM generate_series(25702, 25810) AS series_id;

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES
  (25710, 'Milestone GK KM', 0, 25701, 25701, '2026-03-01 00:00:00+00'),
  (25711, 'Milestone GK Users', 0, 25701, 25701, '2026-03-01 00:00:00+00');

SET LOCAL session_replication_role = replica;

INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES
  (25720, 25710, 25701, 'GC257A', 'pl', coords2position(52.22968, 21.01223), '2026-03-01 01:00:00+00', 0),
  (25721, 25710, 25702, NULL, 'de', coords2position(52.52000, 13.40500), '2026-03-01 02:00:00+00', 1),
  (25722, 25710, 25703, 'GC257C', 'fr', coords2position(48.85661, 2.35222), '2026-03-01 03:00:00+00', 3),
  (25723, 25710, 25704, 'GC257D', 'at', coords2position(48.20817, 16.37382), '2026-03-01 04:00:00+00', 5);

INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
SELECT
  25730 + row_number() OVER (ORDER BY series_id),
  25711,
  series_id,
  format('GC257%s', row_number() OVER (ORDER BY series_id)),
  'pl',
  coords2position(52.22968, 21.01223),
  '2026-03-02 00:00:00+00'::timestamptz + (row_number() OVER (ORDER BY series_id) * interval '1 hour'),
  0
FROM generate_series(25702, 25801) AS series_id;

UPDATE gk_moves
SET km_distance = CASE id
  WHEN 25720 THEN 60
  WHEN 25721 THEN 50
  WHEN 25722 THEN 900
  WHEN 25723 THEN 9000
  ELSE COALESCE(km_distance, 0)
END
WHERE id BETWEEN 25720 AND 25723;

SET LOCAL session_replication_role = origin;

SELECT is(stats.fn_snapshot_first_finder_events(), 2::bigint, 'milestone fixtures rebuild canonical first_finder_events before milestone replay');
SELECT is(stats.fn_snapshot_gk_milestone_events(), 8::bigint, 'full milestone snapshot writes the canonical milestone set');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 25710 AND event_type = 'km_100'), 1::bigint, 'km_100 milestone is present for the first threshold crossing');
SELECT is((SELECT additional_data->>'move_id' FROM stats.gk_milestone_events WHERE gk_id = 25710 AND event_type = 'km_100'), '25721', 'km_100 milestone stores the threshold-crossing move id');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 25710 AND event_type = 'km_1000'), 1::bigint, 'km_1000 milestone is present for the second threshold crossing');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 25710 AND event_type = 'km_10000'), 1::bigint, 'km_10000 milestone is present for the third threshold crossing');
SELECT is((SELECT event_value FROM stats.gk_milestone_events WHERE gk_id = 25710 AND event_type = 'first_find'), 2::numeric, 'first_find milestone reuses hours_since_creation as the event_value');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 25711 AND event_type = 'users_10'), 1::bigint, 'users_10 milestone is present at the tenth distinct author');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 25711 AND event_type = 'users_50'), 1::bigint, 'users_50 milestone is present at the fiftieth distinct author');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 25711 AND event_type = 'users_100'), 1::bigint, 'users_100 milestone is present at the hundredth distinct author');

UPDATE stats.gk_milestone_events
SET event_value = 999
WHERE gk_id = 25710
  AND event_type = 'km_100';

INSERT INTO stats.gk_milestone_events (gk_id, event_type, event_value, occurred_at)
VALUES (25710, 'users_50', 50, '2026-04-03 00:00:00+00');

SELECT is(stats.fn_snapshot_gk_milestone_events(), 17::bigint, 'rerunning milestone snapshot repairs stale rows and removes ghosts');
SELECT is((SELECT event_value FROM stats.gk_milestone_events WHERE gk_id = 25710 AND event_type = 'km_100'), 100::numeric, 'rerun repairs the canonical threshold value');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 25710 AND event_type = 'users_50'), 0::bigint, 'rerun removes ghost milestone rows that are not canonically earned');
SELECT ok((SELECT COUNT(*) = 2 FROM stats.job_log WHERE job_name = 'fn_snapshot_gk_milestone_events' AND status = 'ok'), 'milestone snapshot logs one ok job_log row per execution');
SELECT ok((SELECT metadata ? 'timing_ms' FROM stats.job_log WHERE job_name = 'fn_snapshot_gk_milestone_events' ORDER BY id DESC LIMIT 1), 'milestone snapshot logs timing metadata');

SELECT * FROM finish();
ROLLBACK;
