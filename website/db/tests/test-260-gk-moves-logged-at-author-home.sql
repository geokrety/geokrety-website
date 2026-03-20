BEGIN;
SELECT plan(43);

ALTER TABLE geokrety.gk_moves DISABLE TRIGGER after_99_notify_amqp_moves;

SELECT has_column('geokrety', 'gk_moves', 'logged_at_author_home', 'logged_at_author_home column exists');
SELECT col_not_null('geokrety', 'gk_moves', 'logged_at_author_home', 'logged_at_author_home is NOT NULL');
SELECT col_default_is('geokrety', 'gk_moves', 'logged_at_author_home', 'false', 'logged_at_author_home defaults to false');
SELECT has_function('geokrety', 'fn_gk_moves_set_logged_at_author_home', ARRAY[]::text[], 'trigger function exists');
SELECT function_returns('geokrety', 'fn_gk_moves_set_logged_at_author_home', ARRAY[]::text[], 'trigger', 'trigger function returns trigger');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_before_logged_at_author_home', 'trigger exists on gk_moves');
SELECT has_function('stats', 'fn_backfill_gk_moves_logged_at_author_home', ARRAY['tstzrange','integer'], 'backfill function exists');
SELECT function_returns('stats', 'fn_backfill_gk_moves_logged_at_author_home', ARRAY['tstzrange','integer'], 'text', 'backfill function returns text');
SELECT matches(
  (SELECT pg_get_triggerdef(oid) FROM pg_trigger WHERE tgname = 'tr_gk_moves_before_logged_at_author_home' AND tgrelid = 'geokrety.gk_moves'::regclass),
  'BEFORE INSERT OR UPDATE ON geokrety\.gk_moves FOR EACH ROW EXECUTE FUNCTION fn_gk_moves_set_logged_at_author_home\(\)',
  'trigger fires before every insert and update'
);
SELECT results_eq(
  $$
  SELECT tgname
  FROM pg_trigger
  WHERE tgrelid = 'geokrety.gk_moves'::regclass
    AND tgname IN (
      'before_00_updated_on_datetime',
      'before_10_moved_on_datetime_updater',
      'before_20_gis_updates',
      'before_30_waypoint_uppercase',
      'before_40_update_missing',
      'before_50_check_archive_author',
      'tr_gk_moves_before_logged_at_author_home'
    )
  ORDER BY tgname
  $$,
  $$
  VALUES
    ('before_00_updated_on_datetime'::name),
    ('before_10_moved_on_datetime_updater'::name),
    ('before_20_gis_updates'::name),
    ('before_30_waypoint_uppercase'::name),
    ('before_40_update_missing'::name),
    ('before_50_check_archive_author'::name),
    ('tr_gk_moves_before_logged_at_author_home'::name)
  $$,
  'the logged-at-home trigger sorts after the current BEFORE gk_moves trigger stack'
);

INSERT INTO gk_users (id, username, registration_ip, home_latitude, home_longitude)
VALUES
  (26001, 'lah-home-paris', '127.0.0.1', 48.8, 2.3),
  (26002, 'lah-no-home', '127.0.0.1', NULL, NULL),
  (26003, 'lah-home-berlin', '127.0.0.1', 52.5, 13.3),
  (26004, 'lah-home-lyon', '127.0.0.1', 45.76404, 4.83566);

INSERT INTO gk_geokrety (id, name, type, created_on_datetime)
SELECT id, 'LAH GK ' || id::text, 0, '2020-01-01 00:00:00+00'::timestamptz
FROM generate_series(26010, 26040) AS id;

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (26010, 26010, 26001, coords2position(48.8, 2.3), '2020-04-01 08:00:00+00', 0);
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26010), true, 'exactly-home insert is true (0m)');
UPDATE gk_moves SET app = 'lah' WHERE id = 26010;
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26010), true, 'unrelated updates keep the derived flag stable');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (26011, 26011, 26001, public.ST_Project(coords2position(48.8, 2.3), 49.9, 0), '2020-04-01 09:00:00+00', 0);
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26011), true, 'insert just under 50m is true');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (26012, 26012, 26001, public.ST_Project(coords2position(48.8, 2.3), 50.0, 0), '2020-04-01 10:00:00+00', 0);
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26012), true, 'insert exactly 50m is true');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (26013, 26013, 26001, public.ST_Project(coords2position(48.8, 2.3), 50.1, 0), '2020-04-01 11:00:00+00', 0);
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26013), false, 'insert just over 50m is false');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (26014, 26014, 26002, coords2position(48.8, 2.3), '2020-04-01 12:00:00+00', 0);
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26014), false, 'author without home_position stores false');

INSERT INTO gk_moves (id, geokret, author, username, moved_on_datetime, move_type)
VALUES (26015, 26015, NULL, 'lah-anon', '2020-04-01 13:00:00+00', 2);
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26015), false, 'NULL author stores false');

INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (26029, 26029, 26001, '2020-04-01 13:30:00+00', 2);
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26029), false, 'NULL position stores false');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (26016, 26016, 26001, public.ST_Project(coords2position(48.8, 2.3), 120, 0), '2020-04-01 14:00:00+00', 0);
UPDATE gk_moves SET position = public.ST_Project(coords2position(48.8, 2.3), 25, 0) WHERE id = 26016;
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26016), true, 'update from non-match to match flips to true');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (26017, 26017, 26001, public.ST_Project(coords2position(48.8, 2.3), 15, 0), '2020-04-01 15:00:00+00', 0);
UPDATE gk_moves SET position = public.ST_Project(coords2position(48.8, 2.3), 160, 0) WHERE id = 26017;
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26017), false, 'update from match to non-match flips to false');
UPDATE gk_moves SET move_type = 2, position = NULL, lat = NULL, lon = NULL WHERE id = 26017;
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26017), false, 'updating a move to NULL position keeps the derived flag false');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (26018, 26018, 26001, coords2position(48.8, 2.3), '2020-04-01 16:00:00+00', 0);
UPDATE gk_moves SET author = 26003 WHERE id = 26018;
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26018), false, 'author update recomputes against the new author home');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (26019, 26019, 26001, public.ST_Project(coords2position(48.8, 2.3), 50, 0), '2020-04-01 17:00:00+00', 0);
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26019), true, 'position-only insert at the boundary stays true');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (26020, 26020, 26001, public.ST_Project(coords2position(48.8, 2.3), 140, 0), '2020-04-01 18:00:00+00', 0);
UPDATE gk_moves SET position = public.ST_Project(coords2position(48.8, 2.3), 10, 0) WHERE id = 26020;
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26020), true, 'position-only update from non-match to match flips to true');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type, logged_at_author_home)
VALUES (26021, 26021, 26001, coords2position(48.8, 2.3), '2020-04-01 19:00:00+00', 0, false);
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26021), true, 'caller override on insert is ignored');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (26022, 26022, 26001, public.ST_Project(coords2position(48.8, 2.3), 120, 0), '2020-04-01 20:00:00+00', 0);
UPDATE gk_moves SET logged_at_author_home = true WHERE id = 26022;
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26022), false, 'caller override on update is ignored');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (26023, 26023, 26001, coords2position(48.8, 2.3), '2020-04-01 21:00:00+00', 0);
UPDATE gk_users SET home_latitude = 40.7128, home_longitude = -74.0060 WHERE id = 26001;
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26023), true, 'changing gk_users home does not auto-update historical moves');
UPDATE gk_users SET home_latitude = 48.8, home_longitude = 2.3 WHERE id = 26001;

INSERT INTO gk_moves (id, geokret, author, lat, lon, position, moved_on_datetime, move_type)
VALUES (
  26024,
  26024,
  26004,
  45.76404,
  4.83566,
  public.ST_Project(coords2position(45.76404, 4.83566), 200, 0),
  '2020-04-01 22:00:00+00',
  0
);
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26024), true, 'mixed input uses the normalized lat/lon-derived position');
SELECT is((SELECT position FROM gk_moves WHERE id = 26024), coords2position(45.76404, 4.83566), 'normalized position is the stored position used by the home predicate');
UPDATE gk_moves
SET lat = 52.5,
    lon = 13.3,
    position = public.ST_Project(coords2position(45.76404, 4.83566), 200, 0)
WHERE id = 26024;
SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26024), false, 'mixed update uses the normalized lat/lon-derived position');
SELECT is((SELECT position FROM gk_moves WHERE id = 26024), coords2position(52.5, 13.3), 'mixed update stores the normalized position');

SELECT throws_ok(
  $$
  INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
  VALUES (26025, 26025, 999999, coords2position(48.8, 2.3), '2020-04-01 23:00:00+00', 0)
  $$,
  '23503',
  NULL,
  'non-existent author is still protected by the FK contract'
);

SET CONSTRAINTS ALL IMMEDIATE;
ALTER TABLE geokrety.gk_moves DISABLE TRIGGER tr_gk_moves_before_logged_at_author_home;
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type, logged_at_author_home)
VALUES (26026, 26026, 26004, coords2position(45.76404, 4.83566), '2020-08-01 08:00:00+00', 0, false);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type, logged_at_author_home)
VALUES (26027, 26027, 26004, public.ST_Project(coords2position(45.76404, 4.83566), 200, 0), '2020-08-01 09:00:00+00', 0, true);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type, logged_at_author_home)
VALUES (26028, 26028, 26002, coords2position(45.76404, 4.83566), '2020-08-01 10:00:00+00', 0, true);
ALTER TABLE geokrety.gk_moves ENABLE TRIGGER tr_gk_moves_before_logged_at_author_home;
SET CONSTRAINTS ALL IMMEDIATE;

SELECT is((SELECT logged_at_author_home FROM gk_moves WHERE id = 26026), false, 'historical rows remain stale until manual backfill runs');
SELECT is(
  stats.fn_backfill_gk_moves_logged_at_author_home(tstzrange('2020-08-01 00:00:00+00', '2020-08-02 00:00:00+00', '[)'), 2),
  'Processed 2 rows; 2 rows updated; 1 batches completed; period-scoped from 2020-08-01 to 2020-08-02.',
  'each backfill call repairs one deterministic batch and reports that batch summary'
);
SELECT results_eq(
  $$
  SELECT id, logged_at_author_home
  FROM gk_moves
  WHERE id IN (26026, 26027, 26028)
  ORDER BY id
  $$,
  $$
  VALUES
    (26026::bigint, true),
    (26027::bigint, false),
    (26028::bigint, true)
  $$,
  'the first batch repairs only the two lowest stale ids in ascending order'
);
SELECT is(
  stats.fn_backfill_gk_moves_logged_at_author_home(tstzrange('2020-08-01 00:00:00+00', '2020-08-02 00:00:00+00', '[)'), 2),
  'Processed 1 rows; 1 rows updated; 1 batches completed; period-scoped from 2020-08-01 to 2020-08-02.',
  'a second caller-managed batch picks up the remaining stale row'
);
SELECT results_eq(
  $$
  SELECT id, logged_at_author_home
  FROM gk_moves
  WHERE id IN (26026, 26027, 26028)
  ORDER BY id
  $$,
  $$
  VALUES
    (26026::bigint, true),
    (26027::bigint, false),
    (26028::bigint, false)
  $$,
  'backfill repairs stale historical rows to the trigger-derived truth table'
);
SELECT is(
  stats.fn_backfill_gk_moves_logged_at_author_home(tstzrange('2020-08-01 00:00:00+00', '2020-08-02 00:00:00+00', '[)'), 2),
  'Processed 0 rows; 0 rows updated; 0 batches completed; period-scoped from 2020-08-01 to 2020-08-02.',
  're-running the same scoped backfill after all batches are committed is idempotent'
);
SELECT is(
  stats.fn_backfill_gk_moves_logged_at_author_home(tstzrange('2030-01-01 00:00:00+00', '2030-01-02 00:00:00+00', '[)'), 2),
  'Processed 0 rows; 0 rows updated; 0 batches completed; empty period scope (no rows in range).',
  'empty scoped backfill returns the empty-period summary'
);
SELECT is(
  stats.fn_backfill_gk_moves_logged_at_author_home(NULL, 100),
  'Processed 0 rows; 0 rows updated; 0 batches completed; full-history scope.',
  'full-history backfill reports the full-history summary branch'
);

SELECT throws_ok(
  $$SELECT stats.fn_backfill_gk_moves_logged_at_author_home(NULL, NULL)$$,
  'P0001',
  'p_batch_size must be a positive integer (got <NULL>)',
  'NULL batch size is rejected'
);
SELECT throws_ok(
  $$SELECT stats.fn_backfill_gk_moves_logged_at_author_home(NULL, 0)$$,
  'P0001',
  'p_batch_size must be a positive integer (got 0)',
  'zero batch size is rejected'
);
SELECT throws_ok(
  $$SELECT stats.fn_backfill_gk_moves_logged_at_author_home(NULL, -1)$$,
  'P0001',
  'p_batch_size must be a positive integer (got -1)',
  'negative batch size is rejected'
);

ALTER TABLE geokrety.gk_moves ENABLE TRIGGER after_99_notify_amqp_moves;

SELECT * FROM finish();
ROLLBACK;
