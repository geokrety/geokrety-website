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

INSERT INTO stats.hourly_activity (activity_date, hour_utc, move_type, move_count)
VALUES
  ('2026-02-01', 10, 0, 4),
  ('2026-02-02', 11, 1, 2),
  ('2026-02-03', 12, 5, 3);

INSERT INTO stats.country_pair_flows (year_month, from_country, to_country, move_count, unique_gk_count)
VALUES
  ('2026-02-01', 'PL', 'DE', 4, 3),
  ('2026-03-01', 'PL', 'CZ', 2, 2),
  ('2026-03-01', 'DE', 'PL', 1, 1);

SET LOCAL enable_seqscan = off;

SELECT has_index('stats', 'hourly_activity', 'idx_hourly_activity_date_desc', 'idx_hourly_activity_date_desc exists');
SELECT has_index('stats', 'country_pair_flows', 'idx_country_pair_flows_month_desc', 'idx_country_pair_flows_month_desc exists');
SELECT has_index('stats', 'country_pair_flows', 'idx_country_pair_flows_from', 'idx_country_pair_flows_from exists');
SELECT has_index('stats', 'country_pair_flows', 'idx_country_pair_flows_to', 'idx_country_pair_flows_to exists');
SELECT ok(
  (
    SELECT bool_and(i.indisvalid)
    FROM pg_index i
    JOIN pg_class c ON c.oid = i.indexrelid
    JOIN pg_namespace n ON n.oid = c.relnamespace
    WHERE n.nspname = 'stats'
      AND c.relname IN (
        'idx_hourly_activity_date_desc',
        'idx_country_pair_flows_month_desc',
        'idx_country_pair_flows_from',
        'idx_country_pair_flows_to'
      )
  ),
  'all Sprint 5 analytics indexes are valid'
);
SELECT ok(
  (
    SELECT pg_temp.explain_plan_json($sql$
      SELECT *
      FROM stats.hourly_activity
      WHERE activity_date >= '2026-02-02'
      ORDER BY activity_date DESC
    $sql$)::text LIKE '%idx_hourly_activity_date_desc%'
  ),
  'recent hourly activity queries use idx_hourly_activity_date_desc'
);
SELECT ok(
  (
    SELECT pg_temp.explain_plan_json($sql$
      SELECT *
      FROM stats.country_pair_flows
      ORDER BY year_month DESC
      LIMIT 2
    $sql$)::text LIKE '%idx_country_pair_flows_month_desc%'
  ),
  'month-ordered country flow queries use idx_country_pair_flows_month_desc'
);
SELECT ok(
  (
    SELECT pg_temp.explain_plan_json($sql$
      SELECT *
      FROM stats.country_pair_flows
      WHERE from_country = 'PL'
      ORDER BY year_month DESC
    $sql$)::text LIKE '%idx_country_pair_flows_from%'
  ),
  'from_country filtered queries use idx_country_pair_flows_from'
);
SELECT ok(
  (
    SELECT pg_temp.explain_plan_json($sql$
      SELECT *
      FROM stats.country_pair_flows
      WHERE to_country = 'PL'
      ORDER BY year_month DESC
    $sql$)::text LIKE '%idx_country_pair_flows_to%'
  ),
  'to_country filtered queries use idx_country_pair_flows_to'
);
SELECT is(
  (
    SELECT COUNT(*)::bigint
    FROM pg_indexes
    WHERE schemaname = 'stats'
      AND indexname IN (
        'idx_hourly_activity_date_desc',
        'idx_country_pair_flows_month_desc',
        'idx_country_pair_flows_from',
        'idx_country_pair_flows_to'
      )
  ),
  4::bigint,
  'all task-owned analytics indexes are present'
);

SELECT * FROM finish();
ROLLBACK;
