BEGIN;
SELECT plan(5);

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
SET LOCAL enable_bitmapscan = off;

SELECT has_index(
  'geokrety',
  'gk_moves',
  'idx_gk_moves_author_geokret_recent_lookup',
  'idx_gk_moves_author_geokret_recent_lookup exists'
);
SELECT ok(
  (
    SELECT i.indisvalid
    FROM pg_index AS i
    JOIN pg_class AS c ON c.oid = i.indexrelid
    JOIN pg_namespace AS n ON n.oid = c.relnamespace
    WHERE n.nspname = 'geokrety'
      AND c.relname = 'idx_gk_moves_author_geokret_recent_lookup'
  ),
  'idx_gk_moves_author_geokret_recent_lookup is valid'
);
SELECT ok(
  position('(author, geokret, moved_on_datetime DESC, id DESC)' IN pg_get_indexdef('geokrety.idx_gk_moves_author_geokret_recent_lookup'::regclass)) > 0,
  'idx_gk_moves_author_geokret_recent_lookup stores author, geokret, moved_on_datetime DESC, id DESC in order'
);
SELECT ok(
  position('WHERE (author IS NOT NULL)' IN pg_get_indexdef('geokrety.idx_gk_moves_author_geokret_recent_lookup'::regclass)) > 0,
  'idx_gk_moves_author_geokret_recent_lookup is partial on author IS NOT NULL'
);
SELECT ok(
  (
    WITH plan_text AS (
      SELECT pg_temp.explain_plan_json($$
        SELECT DISTINCT ON (m.geokret)
          m.geokret,
          m.moved_on_datetime,
          m.id
        FROM geokrety.gk_moves AS m
        WHERE m.author = 1
        ORDER BY m.geokret, m.moved_on_datetime DESC, m.id DESC
        LIMIT 20
      $$)::text AS p
    )
    SELECT
      p LIKE '%Index%Scan%' AND p NOT LIKE '%Seq Scan%'
    FROM plan_text
  ),
  'planner uses an index scan (no seq scan) for author-scoped DISTINCT ON geokret lookups'
);

SELECT * FROM finish();
ROLLBACK;
