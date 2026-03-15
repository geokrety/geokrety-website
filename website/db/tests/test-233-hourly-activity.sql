BEGIN;
SELECT plan(12);

SELECT has_table('stats', 'hourly_activity', 'stats.hourly_activity table exists');
SELECT col_is_pk('stats', 'hourly_activity', ARRAY['activity_date', 'hour_utc', 'move_type'], 'hourly_activity primary key is (activity_date, hour_utc, move_type)');
SELECT col_type_is('stats', 'hourly_activity', 'activity_date', 'date', 'activity_date column is date');
SELECT col_type_is('stats', 'hourly_activity', 'hour_utc', 'smallint', 'hour_utc column is smallint');
SELECT col_type_is('stats', 'hourly_activity', 'move_type', 'smallint', 'move_type column is smallint');
SELECT col_type_is('stats', 'hourly_activity', 'move_count', 'bigint', 'move_count column is bigint');
SELECT col_default_is('stats', 'hourly_activity', 'move_count', '0', 'move_count defaults to 0');
SELECT is((SELECT COUNT(*)::bigint FROM stats.hourly_activity), 0::bigint, 'hourly_activity starts empty');
SELECT throws_ok(
  $$
  INSERT INTO stats.hourly_activity (activity_date, hour_utc, move_type)
  VALUES ('2026-01-01', 24, 0)
  $$,
  '23514',
  NULL,
  'hour_utc values above 23 are rejected'
);
SELECT throws_ok(
  $$
  INSERT INTO stats.hourly_activity (activity_date, hour_utc, move_type)
  VALUES ('2026-01-01', 23, 6)
  $$,
  '23514',
  NULL,
  'move_type values above 5 are rejected'
);
SELECT lives_ok(
  $$
  INSERT INTO stats.hourly_activity (activity_date, hour_utc, move_type)
  VALUES ('2026-01-01', 23, 5)
  $$,
  'valid hourly bucket inserts successfully'
);
SELECT is((SELECT move_count FROM stats.hourly_activity WHERE activity_date = '2026-01-01' AND hour_utc = 23 AND move_type = 5), 0::bigint, 'move_count default is applied on insert');

SELECT * FROM finish();
ROLLBACK;
