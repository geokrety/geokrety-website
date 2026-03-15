BEGIN;
SELECT plan(14);

INSERT INTO stats.waypoints (waypoint_code, source, first_seen_at)
VALUES ('S4T05WP', 'UK', now());

SELECT has_table('stats', 'user_cache_visits', 'stats.user_cache_visits table exists');
SELECT col_is_pk('stats', 'user_cache_visits', ARRAY['user_id', 'waypoint_id'], 'user_cache_visits primary key is (user_id, waypoint_id)');
SELECT col_type_is('stats', 'user_cache_visits', 'user_id', 'integer', 'user_id column is integer');
SELECT col_type_is('stats', 'user_cache_visits', 'waypoint_id', 'bigint', 'waypoint_id column is bigint');
SELECT col_type_is('stats', 'user_cache_visits', 'visit_count', 'bigint', 'visit_count column is bigint');
SELECT col_type_is('stats', 'user_cache_visits', 'first_visited_at', 'timestamp with time zone', 'first_visited_at column is timestamptz');
SELECT col_type_is('stats', 'user_cache_visits', 'last_visited_at', 'timestamp with time zone', 'last_visited_at column is timestamptz');
SELECT col_default_is('stats', 'user_cache_visits', 'visit_count', '0', 'visit_count default is 0');
SELECT ok((
  SELECT c.condeferrable AND c.condeferred
  FROM pg_constraint c
  WHERE c.conname = 'fk_user_cache_visits_waypoint'
    AND c.connamespace = 'stats'::regnamespace
), 'fk_user_cache_visits_waypoint is deferrable and initially deferred');
SELECT ok((
  SELECT NOT EXISTS (
    SELECT 1
    FROM information_schema.key_column_usage kcu
    WHERE kcu.table_schema = 'stats'
      AND kcu.table_name = 'user_cache_visits'
      AND kcu.column_name = 'user_id'
      AND kcu.constraint_name <> 'user_cache_visits_pkey'
  )
), 'user_cache_visits does not define an FK on user_id');
SELECT is((SELECT COUNT(*)::bigint FROM stats.user_cache_visits), 0::bigint, 'user_cache_visits starts empty before fixture inserts');
SELECT lives_ok($$
  INSERT INTO stats.user_cache_visits (user_id, waypoint_id, first_visited_at, last_visited_at)
  VALUES (22701, (SELECT id FROM stats.waypoints WHERE waypoint_code = 'S4T05WP'), now(), now())
$$, 'valid user_cache_visits row inserts successfully');
SELECT is((SELECT visit_count FROM stats.user_cache_visits WHERE user_id = 22701 AND waypoint_id = (SELECT id FROM stats.waypoints WHERE waypoint_code = 'S4T05WP')), 0::bigint, 'visit_count defaults to 0');
SELECT throws_ok($$
  INSERT INTO stats.user_cache_visits (user_id, waypoint_id, first_visited_at, last_visited_at)
  VALUES (22702, 999999999, now(), now());
  SET CONSTRAINTS ALL IMMEDIATE;
$$, '23503', NULL, 'invalid waypoint_id is rejected when the deferred FK is checked');

SELECT * FROM finish();
ROLLBACK;
