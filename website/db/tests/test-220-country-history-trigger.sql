BEGIN;
SELECT plan(18);

SELECT has_function('geokrety', 'fn_gk_moves_country_history', ARRAY[]::text[], 'fn_gk_moves_country_history function exists');
SELECT function_returns('geokrety', 'fn_gk_moves_country_history', ARRAY[]::text[], 'trigger', 'fn_gk_moves_country_history returns trigger');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_after_country_history', 'tr_gk_moves_after_country_history trigger exists');

INSERT INTO gk_users (id, username, registration_ip) VALUES (12001, 'country-history-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, created_on_datetime) VALUES (12001, 'Country history GK 1', 0, 12001, '2020-08-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12010, 12001, 12001, coords2position(52.22968, 21.01223), '2020-08-01 08:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 12001 AND country_code = 'pl' AND departed_at IS NULL), 1::bigint, 'first move in PL opens an interval');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12011, 12001, 12001, coords2position(50.06143, 19.93658), '2020-08-01 09:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 12001), 1::bigint, 'second move in the same country is a no-op');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12012, 12001, 12001, coords2position(52.52000, 13.40500), '2020-08-01 10:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 12001), 2::bigint, 'move in DE creates a second interval');
SELECT is((SELECT departed_at FROM stats.gk_country_history WHERE geokrety_id = 12001 AND country_code = 'pl'), '2020-08-01 10:00:00+00'::timestamptz, 'move in DE closes the PL interval');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 12001 AND country_code = 'de' AND departed_at IS NULL), 1::bigint, 'move in DE opens the DE interval');

INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (12013, 12001, 12001, '2020-08-01 11:00:00+00', 2);
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 12001), 2::bigint, 'COMMENT move does not affect country history');

INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (12014, 12001, 12001, '2020-08-01 12:00:00+00', 4);
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 12001), 2::bigint, 'ARCHIVE move does not affect country history');

INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (12015, 12001, 12001, '2020-08-01 13:00:00+00', 0);
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 12001), 2::bigint, 'move with NULL country does not affect country history');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (12002, 'Country history GK 2', 0, '2020-08-02 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12020, 12002, 12001, coords2position(52.22968, 21.01223), '2020-08-02 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12021, 12002, 12001, coords2position(52.52000, 13.40500), '2020-08-02 09:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12022, 12002, 12001, coords2position(50.07550, 14.43780), '2020-08-02 10:00:00+00', 0);

DELETE FROM gk_moves WHERE id = 12021;

SELECT results_eq(
  $$
  SELECT country_code, arrived_at, COALESCE(departed_at, 'infinity'::timestamptz), move_id
  FROM stats.gk_country_history
  WHERE geokrety_id = 12002
  ORDER BY arrived_at
  $$,
  $$
  VALUES
    ('pl'::character(2), '2020-08-02 08:00:00+00'::timestamptz, '2020-08-02 10:00:00+00'::timestamptz, 12020::bigint),
    ('cz'::character(2), '2020-08-02 10:00:00+00'::timestamptz, 'infinity'::timestamptz, 12022::bigint)
  $$,
  'DELETE repairs neighboring intervals exactly'
);

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (12003, 'Country history GK 3', 0, '2020-08-03 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12030, 12003, 12001, coords2position(52.22968, 21.01223), '2020-08-03 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12031, 12003, 12001, coords2position(52.52000, 13.40500), '2020-08-03 09:00:00+00', 0);

UPDATE gk_moves
SET position = coords2position(48.85660, 2.35220)
WHERE id = 12031;

SELECT results_eq(
  $$
  SELECT country_code, arrived_at, COALESCE(departed_at, 'infinity'::timestamptz), move_id
  FROM stats.gk_country_history
  WHERE geokrety_id = 12003
  ORDER BY arrived_at
  $$,
  $$
  VALUES
    ('pl'::character(2), '2020-08-03 08:00:00+00'::timestamptz, '2020-08-03 09:00:00+00'::timestamptz, 12030::bigint),
    ('fr'::character(2), '2020-08-03 09:00:00+00'::timestamptz, 'infinity'::timestamptz, 12031::bigint)
  $$,
  'UPDATE country repairs interval boundaries exactly'
);

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (12004, 'Country history GK 4', 0, '2020-08-04 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12040, 12004, 12001, coords2position(48.85660, 2.35220), '2020-08-04 08:00:00+00', 3);
SELECT is((SELECT country_code FROM stats.gk_country_history WHERE geokrety_id = 12004 AND departed_at IS NULL), 'fr'::character(2), 'SEEN move with country opens an interval');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (12005, 'Country history GK 5', 0, '2020-08-05 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12050, 12005, 12001, coords2position(48.20820, 16.37380), '2020-08-05 08:00:00+00', 5);
SELECT is((SELECT country_code FROM stats.gk_country_history WHERE geokrety_id = 12005 AND departed_at IS NULL), 'at'::character(2), 'DIP move with country opens an interval');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (12006, 'Country history GK 6', 0, '2020-08-06 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12060, 12006, 12001, coords2position(52.22968, 21.01223), '2020-08-06 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12061, 12006, 12001, coords2position(48.14860, 17.10770), '2020-08-06 09:00:00+00', 1);
SELECT results_eq(
  $$
  SELECT country_code, arrived_at, COALESCE(departed_at, 'infinity'::timestamptz)
  FROM stats.gk_country_history
  WHERE geokrety_id = 12006
  ORDER BY arrived_at
  $$,
  $$
  VALUES
    ('pl'::character(2), '2020-08-06 08:00:00+00'::timestamptz, '2020-08-06 09:00:00+00'::timestamptz),
    ('sk'::character(2), '2020-08-06 09:00:00+00'::timestamptz, 'infinity'::timestamptz)
  $$,
  'GRAB in a new country closes the prior interval and opens the new one'
);

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (12007, 'Country history GK 7', 0, '2020-08-07 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (12008, 'Country history GK 8', 0, '2020-08-07 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12070, 12007, 12001, coords2position(52.22968, 21.01223), '2020-08-07 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (12080, 12008, 12001, coords2position(50.06143, 19.93658), '2020-08-07 08:30:00+00', 0);
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_country_history WHERE geokrety_id IN (12007, 12008) AND country_code = 'pl' AND departed_at IS NULL), 2::bigint, 'multiple GeoKrety keep independent open intervals');

SELECT throws_ok(
  $$
  INSERT INTO stats.gk_country_history (id, geokrety_id, country_code, arrived_at, departed_at, move_id)
  VALUES
    (12990, 12999, 'PL', '2025-01-01 00:00:00+00', '2025-06-01 00:00:00+00', 1),
    (12991, 12999, 'DE', '2025-03-01 00:00:00+00', '2025-07-01 00:00:00+00', 2)
  $$,
  '23P01',
  NULL,
  'manual overlapping inserts are still rejected by the exclusion constraint'
);

SELECT * FROM finish();
ROLLBACK;
