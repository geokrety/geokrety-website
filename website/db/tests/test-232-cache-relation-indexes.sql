BEGIN;
SELECT plan(8);

SELECT has_index('stats', 'waypoints', 'idx_waypoints_country', 'idx_waypoints_country exists');
SELECT has_index('stats', 'gk_cache_visits', 'idx_gk_cache_visits_waypoint', 'idx_gk_cache_visits_waypoint exists');
SELECT has_index('stats', 'user_cache_visits', 'idx_user_cache_visits_waypoint', 'idx_user_cache_visits_waypoint exists');
SELECT has_index('stats', 'gk_related_users', 'idx_gk_related_users_user', 'idx_gk_related_users_user exists');
SELECT ok((SELECT indexdef LIKE '%WHERE%' FROM pg_indexes WHERE schemaname = 'stats' AND indexname = 'idx_waypoints_country'), 'idx_waypoints_country is a partial index');
SELECT ok((SELECT indexdef LIKE '%country IS NOT NULL%' FROM pg_indexes WHERE schemaname = 'stats' AND indexname = 'idx_waypoints_country'), 'idx_waypoints_country filters on country IS NOT NULL');
SELECT ok((
  SELECT bool_and(i.indisvalid)
  FROM pg_index i
  JOIN pg_class c ON c.oid = i.indexrelid
  JOIN pg_namespace n ON n.oid = c.relnamespace
  WHERE n.nspname = 'stats'
    AND c.relname IN (
      'idx_waypoints_country',
      'idx_gk_cache_visits_waypoint',
      'idx_user_cache_visits_waypoint',
      'idx_gk_related_users_user'
    )
), 'all Sprint 4 indexes are valid');
SELECT is((
  SELECT COUNT(*)::bigint
  FROM pg_indexes
  WHERE schemaname = 'stats'
    AND tablename = 'waypoints'
    AND indexdef LIKE '%waypoint_code%'
), 1::bigint, 'waypoint_code lookup remains covered by a single unique index');

SELECT * FROM finish();
ROLLBACK;
