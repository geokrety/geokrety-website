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
  pg_temp.explain_plan_json($sql$
    SELECT id
    FROM geokrety.gk_moves
    WHERE author = 24801
      AND country IS NOT NULL
      AND geokrety.fn_normalize_country_code(country) = 'PL'
      AND moved_on_datetime >= '2020-04-01 00:00:00+00'::timestamptz
      AND moved_on_datetime < '2020-05-01 00:00:00+00'::timestamptz
    ORDER BY moved_on_datetime, id
    LIMIT 50
  $sql$)::TEXT LIKE '%idx_gk_moves_author_norm_country_hist%',
  'author-country history lookups can use idx_gk_moves_author_norm_country_hist'
);
SELECT ok(
  pg_temp.explain_plan_json($sql$
    SELECT id
    FROM geokrety.gk_moves
    WHERE geokret = 24810
      AND country IS NOT NULL
      AND geokrety.fn_normalize_country_code(country) = 'PL'
      AND moved_on_datetime >= '2020-04-01 00:00:00+00'::timestamptz
      AND moved_on_datetime < '2020-05-01 00:00:00+00'::timestamptz
    ORDER BY moved_on_datetime, id
    LIMIT 50
  $sql$)::TEXT LIKE '%idx_gk_moves_geokret_norm_country_hist%',
  'geokret-country history lookups can use idx_gk_moves_geokret_norm_country_hist'
);
SELECT ok(
  pg_temp.explain_plan_json($sql$
    SELECT geokret, author
    FROM geokrety.gk_moves
    WHERE waypoint IS NOT NULL
      AND BTRIM(waypoint) <> ''
      AND move_type <> 2
      AND UPPER(BTRIM(waypoint)) = 'GC243X'
    ORDER BY moved_on_datetime, id
    LIMIT 50
  $sql$)::TEXT LIKE '%idx_gk_moves_waypoint_code_hist%',
  'waypoint history lookups can use idx_gk_moves_waypoint_code_hist'
);
SELECT ok(
  pg_temp.explain_plan_json($sql$
    SELECT geokret, author
    FROM geokrety.gk_moves
    WHERE geokret = 24310
      AND author IS NOT NULL
      AND move_type IN (0, 1, 3, 5)
    ORDER BY moved_on_datetime, id
    LIMIT 50
  $sql$)::TEXT LIKE '%idx_gk_moves_relation_geokret_hist%',
  'relation history lookups can use idx_gk_moves_relation_geokret_hist'
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
