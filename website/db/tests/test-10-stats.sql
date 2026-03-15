BEGIN;
SELECT plan(8);

SELECT has_schema('stats', 'stats schema exists');
SELECT has_table('stats', 'backfill_progress', 'stats.backfill_progress exists');
SELECT has_table('stats', 'job_log', 'stats.job_log exists');
SELECT has_table('stats', 'continent_reference', 'stats.continent_reference exists');
SELECT has_table('stats', 'entity_counters_shard', 'stats.entity_counters_shard exists');
SELECT has_table('stats', 'daily_activity', 'stats.daily_activity exists');
SELECT has_table('stats', 'daily_active_users', 'stats.daily_active_users exists');
SELECT has_table('stats', 'daily_entity_counts', 'stats.daily_entity_counts exists');

SELECT * FROM finish();
ROLLBACK;
