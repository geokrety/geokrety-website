BEGIN;
SELECT plan(14);

SELECT has_table('stats', 'gk_milestone_events', 'stats.gk_milestone_events table exists');
SELECT col_is_pk('stats', 'gk_milestone_events', 'id', 'gk_milestone_events primary key is id');
SELECT col_type_is('stats', 'gk_milestone_events', 'gk_id', 'integer', 'gk_id column is integer');
SELECT col_type_is('stats', 'gk_milestone_events', 'event_type', 'text', 'event_type column is text');
SELECT col_type_is('stats', 'gk_milestone_events', 'additional_data', 'jsonb', 'additional_data column is jsonb');
SELECT col_type_is('stats', 'gk_milestone_events', 'occurred_at', 'timestamp with time zone', 'occurred_at column is timestamptz');
SELECT has_index('stats', 'gk_milestone_events', 'idx_gk_milestone_events_gk', 'idx_gk_milestone_events_gk exists');
SELECT has_index('stats', 'gk_milestone_events', 'idx_gk_milestone_events_type', 'idx_gk_milestone_events_type exists');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events), 0::bigint, 'gk_milestone_events starts empty');
SELECT throws_ok(
  $$
  INSERT INTO stats.gk_milestone_events (gk_id, event_type, occurred_at)
  VALUES (23501, 'fake_type', '2026-01-01 00:00:00+00')
  $$,
  '23514',
  NULL,
  'unknown event_type is rejected'
);
SELECT lives_ok(
  $$
  INSERT INTO stats.gk_milestone_events (gk_id, event_type, event_value, additional_data, occurred_at)
  VALUES (23501, 'km_100', 100, '{"country":"PL"}', '2026-01-01 00:00:00+00')
  $$,
  'valid milestone row inserts successfully'
);
SELECT is((SELECT additional_data->>'country' FROM stats.gk_milestone_events WHERE gk_id = 23501 AND event_type = 'km_100'), 'PL', 'additional_data stores JSONB payloads');
SELECT throws_ok(
  $$
  INSERT INTO stats.gk_milestone_events (gk_id, event_type, occurred_at)
  VALUES (23501, 'km_100', '2026-01-02 00:00:00+00')
  $$,
  '23505',
  NULL,
  'duplicate (gk_id, event_type) rows are rejected'
);
SELECT ok((SELECT recorded_at IS NOT NULL FROM stats.gk_milestone_events WHERE gk_id = 23501 AND event_type = 'km_100'), 'recorded_at default is applied');

SELECT * FROM finish();
ROLLBACK;
