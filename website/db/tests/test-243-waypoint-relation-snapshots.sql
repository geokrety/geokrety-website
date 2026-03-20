BEGIN;
SELECT plan(22);

DELETE FROM stats.user_related_users;
DELETE FROM stats.gk_related_users;
DELETE FROM stats.user_cache_visits;
DELETE FROM stats.gk_cache_visits;
DELETE FROM stats.waypoints WHERE waypoint_code IN ('GC243X', 'OC243Y');
DELETE FROM stats.job_log
WHERE job_name IN (
  'fn_snapshot_waypoints',
  'fn_snapshot_cache_visits',
  'fn_snapshot_relations',
  'fn_snapshot_relationship_tables'
);

SELECT has_function('stats', 'fn_snapshot_waypoints', ARRAY['daterange'], 'fn_snapshot_waypoints function exists');
SELECT has_function('stats', 'fn_snapshot_cache_visits', ARRAY['daterange'], 'fn_snapshot_cache_visits function exists');
SELECT has_function('stats', 'fn_snapshot_relations', ARRAY['daterange'], 'fn_snapshot_relations function exists');
SELECT has_function('stats', 'fn_snapshot_relationship_tables', ARRAY['daterange'], 'fn_snapshot_relationship_tables function exists');
SELECT function_returns('stats', 'fn_snapshot_waypoints', ARRAY['daterange'], 'bigint', 'fn_snapshot_waypoints returns bigint');
SELECT function_returns('stats', 'fn_snapshot_cache_visits', ARRAY['daterange'], 'bigint', 'fn_snapshot_cache_visits returns bigint');
SELECT function_returns('stats', 'fn_snapshot_relations', ARRAY['daterange'], 'bigint', 'fn_snapshot_relations returns bigint');
SELECT function_returns('stats', 'fn_snapshot_relationship_tables', ARRAY['daterange'], 'bigint', 'fn_snapshot_relationship_tables returns bigint');

INSERT INTO gk_users (id, username, registration_ip) VALUES (24301, 'snapshot-rel-user-1', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (24302, 'snapshot-rel-user-2', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (24310, 'Snapshot relation GK', 0, 24301, 24301, '2019-01-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES (24320, 24310, 24301, 'gc243x', 'pl', coords2position(52.22968, 21.01223), '2020-01-02 10:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES (24321, 24310, 24302, 'GC243X', 'pl', coords2position(52.22968, 21.01223), '2020-01-03 11:00:00+00', 5);
INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES (24322, 24310, 24302, 'oc243y', 'de', coords2position(52.52000, 13.40500), '2020-01-04 12:00:00+00', 3);
INSERT INTO gk_moves (id, geokret, author, country, moved_on_datetime, move_type)
VALUES (24323, 24310, 24301, 'pl', '2020-01-05 13:00:00+00', 2);
INSERT INTO gk_moves (id, geokret, username, waypoint, country, position, moved_on_datetime, move_type)
VALUES (24324, 24310, 'snapshot-anon', 'oc243y', 'de', coords2position(52.52000, 13.40500), '2020-01-06 14:00:00+00', 0);

SELECT lives_ok($$SELECT stats.fn_snapshot_relationship_tables(NULL);$$, 'snapshot relationship wrapper executes successfully');

SELECT is((SELECT waypoint_code FROM stats.waypoints WHERE waypoint_code = 'GC243X'), 'GC243X', 'fn_snapshot_waypoints stores waypoint codes in uppercase');
SELECT is((SELECT visit_count FROM stats.gk_cache_visits WHERE gk_id = 24310 AND waypoint_id = (SELECT id FROM stats.waypoints WHERE waypoint_code = 'GC243X')), 2::bigint, 'gk_cache_visits aggregates qualifying waypoint visits');
SELECT is((SELECT COUNT(*)::bigint FROM stats.user_cache_visits WHERE user_id = 24302), 2::bigint, 'user_cache_visits includes only authenticated users');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_related_users WHERE geokrety_id = 24310), 2::bigint, 'gk_related_users keeps one row per GK/user pair');
SELECT is((SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 24301 AND related_user_id = 24302), 1::bigint, 'user_related_users stores distinct shared GeoKrety counts');
SELECT ok((SELECT COUNT(*) = 0 FROM stats.user_related_users a WHERE NOT EXISTS (SELECT 1 FROM stats.user_related_users b WHERE b.user_id = a.related_user_id AND b.related_user_id = a.user_id)), 'user_related_users remains symmetric after snapshot');

INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES (24319, 24310, 24301, 'GC243X', 'cz', coords2position(50.07550, 14.43780), '2019-12-31 23:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES (24325, 24310, 24301, 'GC243X', 'pl', coords2position(52.22968, 21.01223), '2020-02-02 10:00:00+00', 0);

SELECT lives_ok($$SELECT stats.fn_snapshot_waypoints();$$, 'fn_snapshot_waypoints remains safe when an earlier move arrives later');
SELECT results_eq(
  $$SELECT waypoint_code, country::text, first_seen_at::text FROM stats.waypoints WHERE waypoint_code = 'GC243X'$$,
  $$VALUES ('GC243X'::varchar, 'CZ'::text, '2019-12-31 23:00:00+00'::text)$$,
  'rerunning fn_snapshot_waypoints refreshes earliest-move facts consistently for UK-sourced waypoints'
);

SELECT lives_ok(
  $$SELECT stats.fn_snapshot_relationship_tables(daterange('2020-02-01', '2020-03-01', '[)'));$$,
  'scoped fn_snapshot_relationship_tables executes successfully for a touched period'
);
SELECT is(
  (SELECT visit_count FROM stats.gk_cache_visits WHERE gk_id = 24310 AND waypoint_id = (SELECT id FROM stats.waypoints WHERE waypoint_code = 'GC243X')),
  4::bigint,
  'scoped cache visit refresh recomputes the touched waypoint from full history'
);
SELECT ok(
  (
    SELECT metadata ? 'timing_ms'
    FROM stats.job_log
    WHERE job_name = 'fn_snapshot_relationship_tables'
      AND status = 'ok'
    ORDER BY id DESC
    LIMIT 1
  ),
  'scoped relationship wrapper logs timing metadata'
);

CREATE TEMP TABLE snapshot_rel_counts AS
SELECT *
FROM (VALUES
  ('waypoints', (SELECT COUNT(*)::bigint FROM stats.waypoints WHERE waypoint_code IN ('GC243X', 'OC243Y'))),
  ('gk_cache_visits', (SELECT COUNT(*)::bigint FROM stats.gk_cache_visits WHERE gk_id = 24310)),
  ('user_related_users', (SELECT COUNT(*)::bigint FROM stats.user_related_users WHERE user_id IN (24301, 24302)))
) AS counts(name, cnt);

SELECT stats.fn_snapshot_relationship_tables(NULL);

SELECT results_eq(
  $$SELECT name, cnt FROM snapshot_rel_counts ORDER BY name$$,
  $$SELECT * FROM (VALUES
    ('gk_cache_visits', (SELECT COUNT(*)::bigint FROM stats.gk_cache_visits WHERE gk_id = 24310)),
    ('user_related_users', (SELECT COUNT(*)::bigint FROM stats.user_related_users WHERE user_id IN (24301, 24302))),
    ('waypoints', (SELECT COUNT(*)::bigint FROM stats.waypoints WHERE waypoint_code IN ('GC243X', 'OC243Y')))
  ) AS counts(name, cnt) ORDER BY name$$,
  're-running the snapshot wrapper is idempotent'
);

SELECT ok((SELECT COUNT(*) = 3 FROM stats.job_log WHERE job_name = 'fn_snapshot_relationship_tables' AND status = 'ok'), 'wrapper writes one canonical job_log row per execution');

SELECT * FROM finish();
ROLLBACK;
