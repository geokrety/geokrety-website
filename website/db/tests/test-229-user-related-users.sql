BEGIN;
SELECT plan(13);

SELECT has_table('stats', 'user_related_users', 'stats.user_related_users table exists');
SELECT col_is_pk('stats', 'user_related_users', ARRAY['user_id', 'related_user_id'], 'user_related_users primary key is (user_id, related_user_id)');
SELECT col_type_is('stats', 'user_related_users', 'user_id', 'integer', 'user_id column is integer');
SELECT col_type_is('stats', 'user_related_users', 'related_user_id', 'integer', 'related_user_id column is integer');
SELECT col_type_is('stats', 'user_related_users', 'shared_geokrety_count', 'bigint', 'shared_geokrety_count column is bigint');
SELECT col_default_is('stats', 'user_related_users', 'shared_geokrety_count', '0', 'shared_geokrety_count default is 0');
SELECT col_type_is('stats', 'user_related_users', 'first_seen_at', 'timestamp with time zone', 'first_seen_at column is timestamptz');
SELECT col_type_is('stats', 'user_related_users', 'last_seen_at', 'timestamp with time zone', 'last_seen_at column is timestamptz');
SELECT is((SELECT COUNT(*)::bigint FROM stats.user_related_users), 0::bigint, 'user_related_users starts empty before fixture inserts');
SELECT throws_ok($$
  INSERT INTO stats.user_related_users (user_id, related_user_id, first_seen_at, last_seen_at)
  VALUES (22901, 22901, now(), now())
$$, '23514', NULL, 'self-links are rejected by the CHECK constraint');
SELECT throws_ok($$
  INSERT INTO stats.user_related_users (user_id, related_user_id)
  VALUES (22901, 22902)
$$, '23502', NULL, 'timestamps are required');
SELECT lives_ok($$
  INSERT INTO stats.user_related_users (user_id, related_user_id, first_seen_at, last_seen_at)
  VALUES (22901, 22902, now(), now())
$$, 'valid user_related_users row inserts successfully');
SELECT is((SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 22901 AND related_user_id = 22902), 0::bigint, 'shared_geokrety_count defaults to 0');

SELECT * FROM finish();
ROLLBACK;
