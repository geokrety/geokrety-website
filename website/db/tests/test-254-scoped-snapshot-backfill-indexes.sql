BEGIN;
SELECT plan(10);

CREATE OR REPLACE FUNCTION pg_temp.explain_plan_json(p_sql TEXT)
RETURNS JSON
LANGUAGE plpgsql
AS $$
DECLARE
  v_plan JSON;
BEGIN
  EXECUTE format('EXPLAIN (FORMAT JSON) %s', p_sql) INTO v_plan;
  RETURN v_plan;
END;
$$;

SET LOCAL enable_seqscan = off;

SELECT has_index('geokrety', 'gk_moves', 'idx_gk_moves_author_norm_country_hist', 'idx_gk_moves_author_norm_country_hist exists');
SELECT has_index('geokrety', 'gk_moves', 'idx_gk_moves_geokret_norm_country_hist', 'idx_gk_moves_geokret_norm_country_hist exists');
SELECT has_index('geokrety', 'gk_moves', 'idx_gk_moves_waypoint_code_hist', 'idx_gk_moves_waypoint_code_hist exists');
SELECT has_index('geokrety', 'gk_moves', 'idx_gk_moves_relation_geokret_hist', 'idx_gk_moves_relation_geokret_hist exists');
SELECT ok(
  (
    SELECT bool_and(i.indisvalid)
    FROM pg_index i
    JOIN pg_class c ON c.oid = i.indexrelid
    JOIN pg_namespace n ON n.oid = c.relnamespace
    WHERE n.nspname = 'geokrety'
      AND c.relname IN (
        'idx_gk_moves_author_norm_country_hist',
        'idx_gk_moves_geokret_norm_country_hist',
        'idx_gk_moves_waypoint_code_hist',
        'idx_gk_moves_relation_geokret_hist'
      )
  ),
  'scoped snapshot backfill indexes are valid'
);
SELECT ok(
  position('fn_normalize_country_code((country)::text)' IN pg_get_indexdef('geokrety.idx_gk_moves_author_norm_country_hist'::regclass)) > 0
  AND position('(author,' IN pg_get_indexdef('geokrety.idx_gk_moves_author_norm_country_hist'::regclass)) > 0,
  'author-country history index stores the expected normalized-country leading keys'
);
SELECT ok(
  position('fn_normalize_country_code((country)::text)' IN pg_get_indexdef('geokrety.idx_gk_moves_geokret_norm_country_hist'::regclass)) > 0
  AND position('(geokret,' IN pg_get_indexdef('geokrety.idx_gk_moves_geokret_norm_country_hist'::regclass)) > 0,
  'geokret-country history index stores the expected normalized-country leading keys'
);
SELECT ok(
  position('upper(btrim((waypoint)::text)), moved_on_datetime, id' IN pg_get_indexdef('geokrety.idx_gk_moves_waypoint_code_hist'::regclass)) > 0
  AND position('INCLUDE (geokret, author)' IN pg_get_indexdef('geokrety.idx_gk_moves_waypoint_code_hist'::regclass)) > 0,
  'waypoint history index keeps the normalized waypoint key and INCLUDE columns'
);
SELECT ok(
  position('(geokret, moved_on_datetime, id)' IN pg_get_indexdef('geokrety.idx_gk_moves_relation_geokret_hist'::regclass)) > 0
  AND position('INCLUDE (author)' IN pg_get_indexdef('geokrety.idx_gk_moves_relation_geokret_hist'::regclass)) > 0,
  'relation history index keeps the geokret-ordered key and INCLUDE author column'
);
SELECT is(
  (
    SELECT COUNT(*)::BIGINT
    FROM pg_indexes
    WHERE schemaname = 'geokrety'
      AND indexname IN (
        'idx_gk_moves_author_norm_country_hist',
        'idx_gk_moves_geokret_norm_country_hist',
        'idx_gk_moves_waypoint_code_hist',
        'idx_gk_moves_relation_geokret_hist'
      )
  ),
  4::BIGINT,
  'all scoped snapshot backfill indexes are present'
);

SELECT * FROM finish();
ROLLBACK;
