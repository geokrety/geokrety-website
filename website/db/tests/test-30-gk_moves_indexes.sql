-- Test: gk_moves indexes created by migration
BEGIN;
SELECT plan(4);

SELECT ok(EXISTS (SELECT 1 FROM pg_indexes WHERE schemaname = 'geokrety' AND indexname = 'idx_gk_moves_replay_cursor'), 'replay cursor index exists');
SELECT ok(EXISTS (SELECT 1 FROM pg_indexes WHERE schemaname = 'geokrety' AND indexname = 'idx_gk_moves_prev_loc_lookup'), 'previous-position lookup index exists');
SELECT ok(EXISTS (SELECT 1 FROM pg_indexes WHERE schemaname = 'geokrety' AND indexname = 'idx_gk_moves_geokret_chainlookup'), 'covering chain lookup index exists');
SELECT ok(EXISTS (SELECT 1 FROM pg_indexes WHERE schemaname = 'stats' AND indexname = 'idx_mv_backfill_period'), 'working-set MV runtime index exists');

SELECT * FROM finish();
ROLLBACK;
