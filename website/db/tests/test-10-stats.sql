BEGIN;
SELECT plan(23);

SELECT has_schema('stats', 'stats schema exists');
SELECT has_table('stats', 'backfill_progress', 'stats.backfill_progress exists');
SELECT has_table('stats', 'job_log', 'stats.job_log exists');
SELECT has_table('stats', 'continent_reference', 'stats.continent_reference exists');
SELECT has_table('stats', 'entity_counters_shard', 'stats.entity_counters_shard exists');
SELECT has_table('stats', 'daily_activity', 'stats.daily_activity exists');
SELECT has_table('stats', 'daily_active_users', 'stats.daily_active_users exists');
SELECT has_table('stats', 'daily_entity_counts', 'stats.daily_entity_counts exists');
SELECT has_table('stats', 'country_daily_stats', 'stats.country_daily_stats exists');
SELECT has_table('stats', 'gk_countries_visited', 'stats.gk_countries_visited exists');
SELECT has_table('stats', 'user_countries', 'stats.user_countries exists');
SELECT has_table('stats', 'gk_country_history', 'stats.gk_country_history exists');
SELECT has_table('stats', 'waypoints', 'stats.waypoints exists');
SELECT has_table('stats', 'gk_cache_visits', 'stats.gk_cache_visits exists');
SELECT has_table('stats', 'user_cache_visits', 'stats.user_cache_visits exists');
SELECT has_table('stats', 'gk_related_users', 'stats.gk_related_users exists');
SELECT has_table('stats', 'user_related_users', 'stats.user_related_users exists');
SELECT has_view('stats', 'v_waypoints_source_union', 'stats.v_waypoints_source_union exists');
SELECT has_function('stats', 'fn_seed_waypoints', ARRAY[]::text[], 'stats.fn_seed_waypoints exists');
SELECT has_function('stats', 'fn_snapshot_waypoints', ARRAY[]::text[], 'stats.fn_snapshot_waypoints exists');
SELECT has_function('stats', 'fn_snapshot_cache_visits', ARRAY[]::text[], 'stats.fn_snapshot_cache_visits exists');
SELECT has_function('stats', 'fn_snapshot_relations', ARRAY[]::text[], 'stats.fn_snapshot_relations exists');
SELECT has_function('stats', 'fn_snapshot_relationship_tables', ARRAY['daterange'], 'stats.fn_snapshot_relationship_tables exists');

SELECT * FROM finish();
ROLLBACK;
