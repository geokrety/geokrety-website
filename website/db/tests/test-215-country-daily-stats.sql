BEGIN;
SELECT plan(13);

SELECT has_table('stats', 'country_daily_stats', 'stats.country_daily_stats table exists');
SELECT col_is_pk('stats', 'country_daily_stats', ARRAY['stats_date', 'country_code'], 'country_daily_stats primary key is (stats_date, country_code)');
SELECT col_type_is('stats', 'country_daily_stats', 'stats_date', 'date', 'stats_date column is date');
SELECT col_type_is('stats', 'country_daily_stats', 'country_code', 'character(2)', 'country_code column is char(2)');
SELECT col_default_is('stats', 'country_daily_stats', 'moves_count', '0', 'moves_count default is 0');
SELECT col_type_is('stats', 'country_daily_stats', 'km_contributed', 'numeric(14,3)', 'km_contributed column is numeric(14,3)');
SELECT col_type_is('stats', 'country_daily_stats', 'points_contributed', 'numeric(16,4)', 'points_contributed column is numeric(16,4)');
SELECT is(
  (
    SELECT COUNT(*)::INT
    FROM information_schema.columns
    WHERE table_schema = 'stats'
      AND table_name = 'country_daily_stats'
  ),
  18,
  'country_daily_stats has 18 columns'
);

SELECT lives_ok(
  $$
  INSERT INTO stats.country_daily_stats (
    stats_date,
    country_code,
    moves_count,
    drops,
    grabs,
    comments,
    sees,
    archives,
    dips,
    unique_users,
    unique_gks,
    km_contributed,
    points_contributed,
    loves_count,
    pictures_uploaded_total,
    pictures_uploaded_avatar,
    pictures_uploaded_move,
    pictures_uploaded_user
  ) VALUES (
    '2025-06-15',
    'pl',
    10,
    5,
    1,
    1,
    1,
    1,
    1,
    3,
    4,
    150.500,
    12.3456,
    2,
    3,
    1,
    1,
    1
  )
  $$,
  'insert into country_daily_stats succeeds'
);
SELECT is((SELECT moves_count FROM stats.country_daily_stats WHERE stats_date = '2025-06-15' AND country_code = 'pl'), 10::bigint, 'inserted moves_count can be read back');
SELECT is((SELECT km_contributed FROM stats.country_daily_stats WHERE stats_date = '2025-06-15' AND country_code = 'pl'), 150.500::numeric, 'inserted km_contributed can be read back');
SELECT is((SELECT points_contributed FROM stats.country_daily_stats WHERE stats_date = '2025-06-15' AND country_code = 'pl'), 12.3456::numeric, 'inserted points_contributed can be read back');
SELECT throws_ok(
  $$
  INSERT INTO stats.country_daily_stats (stats_date, country_code)
  VALUES ('2025-06-15', 'pl')
  $$,
  '23505',
  NULL,
  'duplicate country_daily_stats primary key is rejected'
);

SELECT * FROM finish();
ROLLBACK;
