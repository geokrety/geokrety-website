BEGIN;
SELECT plan(11);

SELECT has_table('stats', 'gk_countries_visited', 'stats.gk_countries_visited table exists');
SELECT col_is_pk('stats', 'gk_countries_visited', ARRAY['geokrety_id', 'country_code'], 'gk_countries_visited primary key is (geokrety_id, country_code)');
SELECT col_type_is('stats', 'gk_countries_visited', 'geokrety_id', 'integer', 'geokrety_id column is integer');
SELECT col_type_is('stats', 'gk_countries_visited', 'country_code', 'character(2)', 'country_code column is char(2)');
SELECT col_type_is('stats', 'gk_countries_visited', 'first_visited_at', 'timestamp with time zone', 'first_visited_at column is timestamptz');
SELECT col_not_null('stats', 'gk_countries_visited', 'first_move_id', 'first_move_id is NOT NULL');
SELECT col_default_is('stats', 'gk_countries_visited', 'move_count', '1', 'move_count default is 1');

SELECT lives_ok(
  $$
  INSERT INTO stats.gk_countries_visited (geokrety_id, country_code, first_visited_at, first_move_id, move_count)
  VALUES (1, 'pl', '2025-06-15 10:00:00+00', 100, 1)
  $$,
  'insert into gk_countries_visited succeeds'
);
SELECT is((SELECT first_move_id FROM stats.gk_countries_visited WHERE geokrety_id = 1 AND country_code = 'pl'), 100::bigint, 'inserted first_move_id can be read back');
SELECT is((SELECT move_count FROM stats.gk_countries_visited WHERE geokrety_id = 1 AND country_code = 'pl'), 1::integer, 'inserted move_count can be read back');
SELECT throws_ok(
  $$
  INSERT INTO stats.gk_countries_visited (geokrety_id, country_code, first_visited_at, first_move_id)
  VALUES (1, 'pl', '2025-06-16 10:00:00+00', 101)
  $$,
  '23505',
  NULL,
  'duplicate gk_countries_visited primary key is rejected'
);

SELECT * FROM finish();
ROLLBACK;
