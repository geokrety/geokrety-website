BEGIN;
SELECT plan(11);

SELECT has_table('stats', 'user_countries', 'stats.user_countries table exists');
SELECT col_is_pk('stats', 'user_countries', ARRAY['user_id', 'country_code'], 'user_countries primary key is (user_id, country_code)');
SELECT col_type_is('stats', 'user_countries', 'user_id', 'integer', 'user_id column is integer');
SELECT col_type_is('stats', 'user_countries', 'country_code', 'character(2)', 'country_code column is char(2)');
SELECT col_default_is('stats', 'user_countries', 'move_count', '0', 'move_count default is 0');
SELECT col_type_is('stats', 'user_countries', 'first_visit', 'timestamp with time zone', 'first_visit column is timestamptz');
SELECT col_type_is('stats', 'user_countries', 'last_visit', 'timestamp with time zone', 'last_visit column is timestamptz');

SELECT lives_ok(
  $$
  INSERT INTO stats.user_countries (user_id, country_code, move_count, first_visit, last_visit)
  VALUES (42, 'pl', 5, '2025-06-15 10:00:00+00', '2025-06-15 11:00:00+00')
  $$,
  'insert into user_countries succeeds'
);
SELECT is((SELECT move_count FROM stats.user_countries WHERE user_id = 42 AND country_code = 'pl'), 5::bigint, 'inserted move_count can be read back');
SELECT is((SELECT first_visit FROM stats.user_countries WHERE user_id = 42 AND country_code = 'pl'), '2025-06-15 10:00:00+00'::timestamptz, 'inserted first_visit can be read back');
SELECT throws_ok(
  $$
  INSERT INTO stats.user_countries (user_id, country_code, first_visit, last_visit)
  VALUES (42, 'pl', '2025-06-16 10:00:00+00', '2025-06-16 10:00:00+00')
  $$,
  '23505',
  NULL,
  'duplicate user_countries primary key is rejected'
);

SELECT * FROM finish();
ROLLBACK;
