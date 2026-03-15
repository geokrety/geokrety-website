BEGIN;
SELECT plan(15);

SELECT has_table('stats', 'first_finder_events', 'stats.first_finder_events table exists');
SELECT col_is_pk('stats', 'first_finder_events', 'gk_id', 'first_finder_events primary key is gk_id');
SELECT col_type_is('stats', 'first_finder_events', 'move_id', 'bigint', 'move_id column is bigint');
SELECT col_type_is('stats', 'first_finder_events', 'move_type', 'smallint', 'move_type column is smallint');
SELECT col_type_is('stats', 'first_finder_events', 'hours_since_creation', 'smallint', 'hours_since_creation column is smallint');
SELECT has_index('stats', 'first_finder_events', 'idx_first_finder_events_user', 'idx_first_finder_events_user exists');
SELECT has_index('stats', 'first_finder_events', 'idx_first_finder_events_hours', 'idx_first_finder_events_hours exists');
SELECT ok(
  (
    SELECT indexdef LIKE '%hours_since_creation%'
       AND indexdef LIKE '%168%'
    FROM pg_indexes
    WHERE schemaname = 'stats'
      AND indexname = 'idx_first_finder_events_hours'
  ),
  'idx_first_finder_events_hours is a partial index capped at 168 hours'
);
SELECT is((SELECT COUNT(*)::bigint FROM stats.first_finder_events), 0::bigint, 'first_finder_events starts empty');
SELECT throws_ok(
  $$
  INSERT INTO stats.first_finder_events (gk_id, finder_user_id, move_id, move_type, hours_since_creation, found_at, gk_created_at)
  VALUES (23601, 23602, 23603, 2, 1, '2026-01-01 01:00:00+00', '2026-01-01 00:00:00+00')
  $$,
  '23514',
  NULL,
  'invalid move_type is rejected'
);
SELECT throws_ok(
  $$
  INSERT INTO stats.first_finder_events (gk_id, finder_user_id, move_id, move_type, hours_since_creation, found_at, gk_created_at)
  VALUES (23601, 23602, 23603, 0, -1, '2026-01-01 01:00:00+00', '2026-01-01 00:00:00+00')
  $$,
  '23514',
  NULL,
  'negative hours_since_creation is rejected'
);
SELECT throws_ok(
  $$
  INSERT INTO stats.first_finder_events (gk_id, finder_user_id, move_type, hours_since_creation, found_at, gk_created_at)
  VALUES (23601, 23602, 0, 1, '2026-01-01 01:00:00+00', '2026-01-01 00:00:00+00')
  $$,
  '23502',
  NULL,
  'move_id is required'
);
SELECT throws_ok(
  $$
  INSERT INTO stats.first_finder_events (gk_id, finder_user_id, move_id, move_type, hours_since_creation, found_at)
  VALUES (23601, 23602, 23603, 0, 1, '2026-01-01 01:00:00+00')
  $$,
  '23502',
  NULL,
  'gk_created_at is required'
);
SELECT lives_ok(
  $$
  INSERT INTO stats.first_finder_events (gk_id, finder_user_id, move_id, move_type, hours_since_creation, found_at, gk_created_at)
  VALUES (23601, 23602, 23603, 0, 1, '2026-01-01 01:00:00+00', '2026-01-01 00:00:00+00')
  $$,
  'valid first_finder_events row inserts successfully'
);
SELECT throws_ok(
  $$
  INSERT INTO stats.first_finder_events (gk_id, finder_user_id, move_id, move_type, hours_since_creation, found_at, gk_created_at)
  VALUES (23601, 23604, 23605, 1, 2, '2026-01-01 02:00:00+00', '2026-01-01 00:00:00+00')
  $$,
  '23505',
  NULL,
  'only one first-finder row is allowed per GeoKret'
);

SELECT * FROM finish();
ROLLBACK;
