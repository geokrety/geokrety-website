BEGIN;
SELECT plan(16);

SELECT has_table('stats', 'country_pair_flows', 'stats.country_pair_flows table exists');
SELECT col_is_pk('stats', 'country_pair_flows', ARRAY['year_month', 'from_country', 'to_country'], 'country_pair_flows primary key is (year_month, from_country, to_country)');
SELECT col_type_is('stats', 'country_pair_flows', 'from_country', 'character(2)', 'from_country column is char(2)');
SELECT col_type_is('stats', 'country_pair_flows', 'to_country', 'character(2)', 'to_country column is char(2)');
SELECT col_type_is('stats', 'country_pair_flows', 'move_count', 'bigint', 'move_count column is bigint');
SELECT col_type_is('stats', 'country_pair_flows', 'unique_gk_count', 'bigint', 'unique_gk_count column is bigint');
SELECT col_default_is('stats', 'country_pair_flows', 'move_count', '0', 'move_count defaults to 0');
SELECT col_default_is('stats', 'country_pair_flows', 'unique_gk_count', '0', 'unique_gk_count defaults to 0');
SELECT is((SELECT COUNT(*)::bigint FROM stats.country_pair_flows), 0::bigint, 'country_pair_flows starts empty');
SELECT throws_ok(
  $$
  INSERT INTO stats.country_pair_flows (year_month, from_country, to_country)
  VALUES ('2026-01-01', 'PL', 'PL')
  $$,
  '23514',
  NULL,
  'self-loops are rejected'
);
SELECT throws_ok(
  $$
  INSERT INTO stats.country_pair_flows (year_month, from_country, to_country)
  VALUES ('2026-01-15', 'PL', 'DE')
  $$,
  '23514',
  NULL,
  'year_month must be normalized to the first day of the month'
);
SELECT throws_ok(
  $$
  INSERT INTO stats.country_pair_flows (year_month, from_country, to_country)
  VALUES ('2026-01-01', 'pl', 'DE')
  $$,
  '23514',
  NULL,
  'lowercase from_country is rejected'
);
SELECT throws_ok(
  $$
  INSERT INTO stats.country_pair_flows (year_month, from_country, to_country)
  VALUES ('2026-01-01', 'PL', 'de')
  $$,
  '23514',
  NULL,
  'lowercase to_country is rejected'
);
SELECT lives_ok(
  $$
  INSERT INTO stats.country_pair_flows (year_month, from_country, to_country)
  VALUES ('2026-01-01', 'PL', 'DE')
  $$,
  'valid country pair flow row inserts successfully'
);
SELECT is((SELECT move_count FROM stats.country_pair_flows WHERE year_month = '2026-01-01' AND from_country = 'PL' AND to_country = 'DE'), 0::bigint, 'move_count default is applied');
SELECT is((SELECT unique_gk_count FROM stats.country_pair_flows WHERE year_month = '2026-01-01' AND from_country = 'PL' AND to_country = 'DE'), 0::bigint, 'unique_gk_count default is applied');

SELECT * FROM finish();
ROLLBACK;
