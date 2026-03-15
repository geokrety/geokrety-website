BEGIN;
SELECT plan(13);

SELECT has_table('stats', 'gk_country_history', 'stats.gk_country_history table exists');
SELECT col_is_pk('stats', 'gk_country_history', 'id', 'gk_country_history primary key is id');
SELECT col_type_is('stats', 'gk_country_history', 'id', 'bigint', 'id column is bigint');
SELECT col_type_is('stats', 'gk_country_history', 'geokrety_id', 'integer', 'geokrety_id column is integer');
SELECT col_type_is('stats', 'gk_country_history', 'country_code', 'character(2)', 'country_code column is char(2)');
SELECT col_not_null('stats', 'gk_country_history', 'arrived_at', 'arrived_at is NOT NULL');
SELECT col_is_null('stats', 'gk_country_history', 'departed_at', 'departed_at is nullable');
SELECT col_not_null('stats', 'gk_country_history', 'move_id', 'move_id is NOT NULL');
SELECT ok(
  EXISTS(
    SELECT 1
    FROM pg_constraint
    WHERE conname = 'gk_country_history_excl'
      AND contype = 'x'
  ),
  'gk_country_history exclusion constraint exists'
);

SELECT lives_ok(
  $$
  INSERT INTO stats.gk_country_history (id, geokrety_id, country_code, arrived_at, departed_at, move_id)
  VALUES
    (1, 1, 'pl', '2025-01-01 00:00:00+00', '2025-06-01 00:00:00+00', 100),
    (2, 1, 'de', '2025-06-01 00:00:00+00', NULL, 200)
  $$,
  'non-overlapping country history intervals succeed'
);
SELECT throws_ok(
  $$
  INSERT INTO stats.gk_country_history (id, geokrety_id, country_code, arrived_at, departed_at, move_id)
  VALUES (3, 1, 'cz', '2025-03-01 00:00:00+00', '2025-09-01 00:00:00+00', 300)
  $$,
  '23P01',
  NULL,
  'overlapping country history intervals are rejected'
);
SELECT lives_ok(
  $$
  INSERT INTO stats.gk_country_history (id, geokrety_id, country_code, arrived_at, departed_at, move_id)
  VALUES (4, 2, 'pl', '2025-03-01 00:00:00+00', NULL, 400)
  $$,
  'different GeoKrety can have overlapping intervals'
);
SELECT throws_ok(
  $$
  INSERT INTO stats.gk_country_history (id, geokrety_id, country_code, arrived_at, departed_at, move_id)
  VALUES (5, 1, 'fr', '2025-07-01 00:00:00+00', NULL, 500)
  $$,
  '23P01',
  NULL,
  'open intervals block later overlaps for the same GeoKret'
);

SELECT * FROM finish();
ROLLBACK;
