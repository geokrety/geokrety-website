BEGIN;
SELECT plan(19);

DELETE FROM stats.hourly_activity;
DELETE FROM stats.country_pair_flows;
DELETE FROM stats.user_related_users;
DELETE FROM stats.gk_related_users;
DELETE FROM stats.user_cache_visits;
DELETE FROM stats.gk_cache_visits;
DELETE FROM stats.daily_active_users;
DELETE FROM stats.daily_activity;
DELETE FROM stats.user_countries;
DELETE FROM stats.gk_countries_visited;
DELETE FROM stats.country_daily_stats;
DELETE FROM stats.entity_counters_shard;
DELETE FROM stats.waypoints;
DELETE FROM stats.job_log WHERE job_name IN ('fn_run_all_snapshots', 'fn_reconcile_stats');

SELECT has_function('stats', 'fn_run_all_snapshots', ARRAY[]::text[], 'fn_run_all_snapshots function exists');
SELECT function_returns('stats', 'fn_run_all_snapshots', ARRAY[]::text[], 'text', 'fn_run_all_snapshots returns text');
SELECT has_function('stats', 'fn_reconcile_stats', ARRAY[]::text[], 'fn_reconcile_stats function exists');
SELECT has_function('stats', 'fn_run_all_snapshots', ARRAY['text[]', 'tstzrange', 'integer'], 'scoped fn_run_all_snapshots overload exists');
SELECT has_function('stats', 'fn_reconcile_stats', ARRAY['text[]', 'tstzrange'], 'scoped fn_reconcile_stats overload exists');

INSERT INTO gk_users (id, username, registration_ip) VALUES (24801, 'snapshot-all-user-1', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (24802, 'snapshot-all-user-2', '127.0.0.1');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (24810, 'Snapshot all GK', 0, 24801, 24801, '2020-04-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES (24820, 24810, 24801, 'GC248A', 'pl', coords2position(52.22968, 21.01223), '2020-04-02 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES (24821, 24810, 24802, 'GC248B', 'de', coords2position(52.52000, 13.40500), '2020-04-05 08:00:00+00', 5);
INSERT INTO gk_pictures (id, geokret, move, "user", type, filename, uploaded_on_datetime)
VALUES (24830, 24810, 24820, NULL, 1, 'snapshot-all-picture.jpg', '2020-04-03 09:00:00+00');
INSERT INTO gk_loves (id, geokret, "user", created_on_datetime)
VALUES (24840, 24810, 24802, '2020-04-04 10:00:00+00');

SET CONSTRAINTS ALL IMMEDIATE;
ALTER TABLE geokrety.gk_moves DISABLE TRIGGER ALL;

UPDATE gk_moves
SET previous_move_id = NULL,
	previous_position_id = NULL,
	km_distance = NULL
WHERE id IN (24820, 24821);

ALTER TABLE geokrety.gk_moves ENABLE TRIGGER ALL;
SET CONSTRAINTS ALL IMMEDIATE;

SELECT lives_ok(
	$$SELECT stats.fn_run_all_snapshots(
		ARRAY[
			'fn_seed_daily_activity',
			'fn_snapshot_daily_country_stats',
			'fn_snapshot_hourly_activity',
			'fn_snapshot_country_pair_flows'
		],
		tstzrange('2020-04-01 00:00:00+00', '2020-05-01 00:00:00+00', '[)'),
		1000
	);$$,
	'scoped fn_run_all_snapshots executes successfully for a limited phase set and period'
);
SELECT ok(
	(
		SELECT COUNT(*) = 0
		FROM stats.fn_reconcile_stats(
			ARRAY[
				'stats.daily_activity',
				'stats.country_daily_stats',
				'stats.hourly_activity',
				'stats.country_pair_flows'
			],
			tstzrange('2020-04-01 00:00:00+00', '2020-05-01 00:00:00+00', '[)')
		)
		WHERE status <> 'OK'
	),
	'scoped fn_reconcile_stats reports zero mismatches for the selected checks'
);
SELECT ok(
	(
		SELECT COUNT(*) > 0
		FROM stats.job_log
		WHERE job_name = 'fn_run_all_snapshots'
			AND metadata->>'mode' = 'phase-split-scoped'
			AND metadata ? 'requested_period'
	),
	'scoped fn_run_all_snapshots logs the requested period metadata'
);

SELECT lives_ok($$SELECT stats.fn_run_all_snapshots();$$, 'fn_run_all_snapshots executes successfully');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 24821), 24820::bigint, 'fn_run_all_snapshots repopulates previous_position_id as part of the split-chain backfill');
SELECT is((SELECT COUNT(*)::bigint FROM stats.entity_counters_shard), 400::bigint, 'fn_run_all_snapshots refreshes entity_counters_shard');
SELECT ok((SELECT COUNT(*) > 0 FROM stats.daily_activity), 'fn_run_all_snapshots refreshes daily_activity');
SELECT ok((SELECT COUNT(*) > 0 FROM stats.hourly_activity), 'fn_run_all_snapshots refreshes hourly_activity');
SELECT ok((SELECT COUNT(*) > 0 FROM stats.country_pair_flows), 'fn_run_all_snapshots refreshes country_pair_flows');
SELECT ok((SELECT COUNT(*) = 0 FROM stats.fn_reconcile_stats() WHERE status <> 'OK'), 'fn_reconcile_stats reports zero mismatches on the canonical snapshot state');
SELECT ok((SELECT status = 'ok' FROM stats.job_log WHERE job_name = 'fn_run_all_snapshots' ORDER BY id DESC LIMIT 1), 'fn_run_all_snapshots logs ok status');
SELECT ok((SELECT jsonb_array_length(metadata->'phases') = 9 FROM stats.job_log WHERE job_name = 'fn_run_all_snapshots' ORDER BY id DESC LIMIT 1), 'fn_run_all_snapshots logs the canonical 9-phase list');
SELECT ok((SELECT status = 'ok' FROM stats.job_log WHERE job_name = 'fn_reconcile_stats' ORDER BY id DESC LIMIT 1), 'fn_reconcile_stats logs ok status');
SELECT ok((SELECT metadata->>'policy' = 'exact-zero-delta' FROM stats.job_log WHERE job_name = 'fn_reconcile_stats' ORDER BY id DESC LIMIT 1), 'fn_reconcile_stats logs the canonical zero-delta policy');

SELECT * FROM finish();
ROLLBACK;
