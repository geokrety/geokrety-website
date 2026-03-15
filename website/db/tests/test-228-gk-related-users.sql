BEGIN;
SELECT plan(12);

SELECT has_table('stats', 'gk_related_users', 'stats.gk_related_users table exists');
SELECT col_is_pk('stats', 'gk_related_users', ARRAY['geokrety_id', 'user_id'], 'gk_related_users primary key is (geokrety_id, user_id)');
SELECT col_type_is('stats', 'gk_related_users', 'geokrety_id', 'integer', 'geokrety_id column is integer');
SELECT col_type_is('stats', 'gk_related_users', 'user_id', 'integer', 'user_id column is integer');
SELECT col_type_is('stats', 'gk_related_users', 'interaction_count', 'bigint', 'interaction_count column is bigint');
SELECT col_default_is('stats', 'gk_related_users', 'interaction_count', '0', 'interaction_count default is 0');
SELECT col_type_is('stats', 'gk_related_users', 'first_interaction', 'timestamp with time zone', 'first_interaction column is timestamptz');
SELECT col_type_is('stats', 'gk_related_users', 'last_interaction', 'timestamp with time zone', 'last_interaction column is timestamptz');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_related_users), 0::bigint, 'gk_related_users starts empty before fixture inserts');
SELECT throws_ok($$
  INSERT INTO stats.gk_related_users (geokrety_id, user_id)
  VALUES (22801, 22802)
$$, '23502', NULL, 'timestamps are required');
SELECT lives_ok($$
  INSERT INTO stats.gk_related_users (geokrety_id, user_id, first_interaction, last_interaction)
  VALUES (22801, 22802, now(), now())
$$, 'valid gk_related_users row inserts successfully');
SELECT is((SELECT interaction_count FROM stats.gk_related_users WHERE geokrety_id = 22801 AND user_id = 22802), 0::bigint, 'interaction_count defaults to 0');

SELECT * FROM finish();
ROLLBACK;
