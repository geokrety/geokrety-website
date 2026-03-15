BEGIN;
SELECT plan(6);

SELECT has_index('stats', 'country_daily_stats', 'idx_country_daily_stats_country_date', 'idx_country_daily_stats_country_date exists');
SELECT has_index('stats', 'gk_country_history', 'idx_gk_country_history_active_by_country', 'idx_gk_country_history_active_by_country exists');
SELECT has_index('stats', 'gk_country_history', 'idx_gk_country_history_gk_arrived', 'idx_gk_country_history_gk_arrived exists');
SELECT ok(
  (
    SELECT indexdef LIKE '%WHERE%'
    FROM pg_indexes
    WHERE schemaname = 'stats'
      AND indexname = 'idx_gk_country_history_active_by_country'
  ),
  'idx_gk_country_history_active_by_country is a partial index'
);
SELECT ok(
  (
    SELECT indexdef LIKE '%departed_at IS NULL%'
    FROM pg_indexes
    WHERE schemaname = 'stats'
      AND indexname = 'idx_gk_country_history_active_by_country'
  ),
  'idx_gk_country_history_active_by_country filters on departed_at IS NULL'
);
SELECT ok(
  (
    SELECT bool_and(i.indisvalid)
    FROM pg_index i
    JOIN pg_class c ON c.oid = i.indexrelid
    JOIN pg_namespace n ON n.oid = c.relnamespace
    WHERE n.nspname = 'stats'
      AND c.relname IN (
        'idx_country_daily_stats_country_date',
        'idx_gk_country_history_active_by_country',
        'idx_gk_country_history_gk_arrived'
      )
  ),
  'all country indexes are valid'
);

SELECT * FROM finish();
ROLLBACK;
