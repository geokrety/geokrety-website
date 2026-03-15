BEGIN;
SELECT plan(6);

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

SELECT has_index('geokrety', 'gk_moves', 'idx_gk_moves_qualified_period', 'idx_gk_moves_qualified_period exists');
SELECT has_index('geokrety', 'gk_moves', 'idx_gk_moves_distance_records', 'idx_gk_moves_distance_records exists');
SELECT ok(
  (
    SELECT bool_and(i.indisvalid)
    FROM pg_index i
    JOIN pg_class c ON c.oid = i.indexrelid
    JOIN pg_namespace n ON n.oid = c.relnamespace
    WHERE n.nspname = 'geokrety'
      AND c.relname IN ('idx_gk_moves_qualified_period', 'idx_gk_moves_distance_records')
  ),
  'runtime indexes are valid'
);
SELECT ok(
  (
    WITH plan_text AS (
      SELECT pg_temp.explain_plan_json($sql$
        SELECT *
        FROM stats.v_uc15_distance_records
        WHERE gk_id = 24810
      $sql$)::TEXT AS value
    )
    SELECT value LIKE '%"Relation Name": "gk_moves"%'
      AND (
        value LIKE '%"Node Type": "Index Scan"%'
        OR value LIKE '%"Node Type": "Index Only Scan"%'
        OR (
          value LIKE '%"Node Type": "Bitmap Heap Scan"%'
          AND value LIKE '%"Node Type": "Bitmap Index Scan"%'
        )
      )
    FROM plan_text
  ),
  'v_uc15_distance_records keyed lookups use indexed access on gk_moves'
);
SELECT ok(
  (
    SELECT pg_temp.explain_plan_json($sql$
      SELECT id
      FROM geokrety.gk_moves
      WHERE position IS NOT NULL
        AND move_type IN (0, 1, 3, 5)
      ORDER BY moved_on_datetime, id
      LIMIT 50
    $sql$)::TEXT LIKE '%idx_gk_moves_qualified_period%'
  ),
  'qualified replay-order scans can use idx_gk_moves_qualified_period'
);
SELECT is(
  (
    SELECT COUNT(*)::BIGINT
    FROM pg_indexes
    WHERE schemaname = 'geokrety'
      AND indexname IN ('idx_gk_moves_qualified_period', 'idx_gk_moves_distance_records')
  ),
  2::BIGINT,
  'all task-owned runtime indexes are present'
);

SELECT * FROM finish();
ROLLBACK;
