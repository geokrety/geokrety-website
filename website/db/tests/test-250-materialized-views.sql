BEGIN;
SELECT plan(7);

SELECT ok((SELECT COUNT(*) = 3 FROM pg_matviews WHERE schemaname = 'stats' AND matviewname = ANY (ARRAY['mv_country_month_rollup', 'mv_top_caches_global', 'mv_global_kpi'])), 'all 3 canonical materialized views exist');
SELECT ok((SELECT COUNT(*) = 1 FROM pg_indexes WHERE schemaname = 'stats' AND indexname = 'idx_mv_country_month_rollup_pk'), 'mv_country_month_rollup has its unique index');
SELECT ok((SELECT COUNT(*) = 1 FROM pg_indexes WHERE schemaname = 'stats' AND indexname = 'idx_mv_top_caches_global_pk'), 'mv_top_caches_global has its unique index');
SELECT ok((SELECT COUNT(*) = 1 FROM pg_indexes WHERE schemaname = 'stats' AND indexname = 'idx_mv_global_kpi_pk'), 'mv_global_kpi has its unique index');
SELECT ok((SELECT definition ILIKE '%stats.country_pair_flows%' FROM pg_matviews WHERE schemaname = 'stats' AND matviewname = 'mv_country_month_rollup'), 'mv_country_month_rollup uses canonical stats sources');
SELECT ok((SELECT definition ILIKE '%stats.v_uc10_cache_popularity%' FROM pg_matviews WHERE schemaname = 'stats' AND matviewname = 'mv_top_caches_global'), 'mv_top_caches_global uses the canonical UC view source');
SELECT ok((SELECT definition ILIKE '%singleton_key%' FROM pg_matviews WHERE schemaname = 'stats' AND matviewname = 'mv_global_kpi'), 'mv_global_kpi exposes a real singleton key for concurrent refresh');

SELECT * FROM finish();
ROLLBACK;
