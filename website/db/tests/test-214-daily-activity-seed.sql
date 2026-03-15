BEGIN;
SELECT plan(20);

INSERT INTO gk_users (id, username, registration_ip, joined_on_datetime)
VALUES (9701, 'daily-seed-user-1', '127.0.0.1', '2020-06-01 07:00:00+00');
INSERT INTO gk_users (id, username, registration_ip, joined_on_datetime)
VALUES (9702, 'daily-seed-user-2', '127.0.0.1', '2020-06-02 07:00:00+00');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (9801, 'Daily seed GK 1', 0, 9701, 9701, '2020-06-01 08:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (9802, 'Daily seed GK 2', 0, 9702, 9702, '2020-06-02 08:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (9901, 9801, 9701, coords2position(52.22968, 21.01223), '2020-06-01 09:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (9902, 9801, 9701, coords2position(52.52000, 13.40500), '2020-06-01 10:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, username, moved_on_datetime, move_type)
VALUES (9903, 9802, 'anonymous-daily-seed', '2020-06-02 11:00:00+00', 2);

INSERT INTO gk_pictures (id, author, geokret, type, created_on_datetime, uploaded_on_datetime)
VALUES (9951, 9701, 9801, 0, '2020-06-01 12:00:00+00', '2020-06-01 12:05:00+00');
INSERT INTO gk_pictures (id, author, "user", type, created_on_datetime, uploaded_on_datetime)
VALUES (9952, 9702, 9702, 2, '2020-06-02 12:00:00+00', '2020-06-02 12:05:00+00');

DELETE FROM stats.daily_active_users;
DELETE FROM stats.daily_activity;

SELECT has_function('stats', 'fn_seed_daily_activity', ARRAY['tstzrange'], 'fn_seed_daily_activity function exists');
SELECT function_returns('stats', 'fn_seed_daily_activity', ARRAY['tstzrange'], 'bigint', 'fn_seed_daily_activity returns bigint');
SELECT cmp_ok(stats.fn_seed_daily_activity(NULL), '>=', 2::bigint, 'full seed returns the number of daily_activity rows written');
SELECT is((SELECT total_moves FROM stats.daily_activity WHERE activity_date = '2020-06-01'), 2::bigint, 'full seed writes total_moves from gk_moves');
SELECT is(
  (SELECT km_contributed FROM stats.daily_activity WHERE activity_date = '2020-06-01'),
  (SELECT COALESCE(SUM(km_distance), 0)::numeric FROM gk_moves WHERE moved_on_datetime::date = '2020-06-01'),
  'full seed writes km_contributed from gk_moves'
);
SELECT is((SELECT gk_created FROM stats.daily_activity WHERE activity_date = '2020-06-01'), 1::bigint, 'full seed writes gk_created from gk_geokrety');
SELECT is((SELECT users_registered FROM stats.daily_activity WHERE activity_date = '2020-06-02'), 1::bigint, 'full seed writes users_registered from gk_users');
SELECT is((SELECT pictures_uploaded_total FROM stats.daily_activity WHERE activity_date = '2020-06-02'), 1::bigint, 'full seed writes picture totals from gk_pictures');
SELECT is((SELECT pictures_uploaded_user FROM stats.daily_activity WHERE activity_date = '2020-06-02'), 1::bigint, 'full seed writes picture type subtotals from gk_pictures');
SELECT is((SELECT COUNT(*)::bigint FROM stats.daily_active_users WHERE activity_date = '2020-06-01' AND user_id = 9701), 1::bigint, 'full seed writes daily_active_users from move authors');
SELECT is((SELECT COUNT(*)::bigint FROM stats.daily_active_users WHERE activity_date = '2020-06-02'), 0::bigint, 'full seed excludes NULL move authors from daily_active_users');

CREATE TEMP TABLE daily_activity_seed_snapshot AS
SELECT * FROM stats.daily_activity ORDER BY activity_date;
CREATE TEMP TABLE daily_active_users_seed_snapshot AS
SELECT * FROM stats.daily_active_users ORDER BY activity_date, user_id;

SELECT lives_ok($$SELECT stats.fn_seed_daily_activity(NULL);$$, 'full seed is safe to re-run');
SELECT results_eq(
  $$SELECT activity_date, total_moves, drops, grabs, comments, sees, archives, dips, km_contributed, gk_created, pictures_uploaded_total, pictures_uploaded_avatar, pictures_uploaded_move, pictures_uploaded_user, users_registered FROM stats.daily_activity ORDER BY activity_date$$,
  $$SELECT activity_date, total_moves, drops, grabs, comments, sees, archives, dips, km_contributed, gk_created, pictures_uploaded_total, pictures_uploaded_avatar, pictures_uploaded_move, pictures_uploaded_user, users_registered FROM daily_activity_seed_snapshot ORDER BY activity_date$$,
  're-running the full seed leaves daily_activity unchanged'
);
SELECT results_eq(
  $$SELECT activity_date, user_id FROM stats.daily_active_users ORDER BY activity_date, user_id$$,
  $$SELECT activity_date, user_id FROM daily_active_users_seed_snapshot ORDER BY activity_date, user_id$$,
  're-running the full seed leaves daily_active_users unchanged'
);

UPDATE stats.daily_activity
SET points_contributed = 12.3456,
    loves_count = 7
WHERE activity_date = '2020-06-02';

SELECT is(stats.fn_seed_daily_activity('[2020-06-02 00:00:00+00,2020-06-03 00:00:00+00)'::tstzrange), 1::bigint, 'range seed returns the number of day rows written for the period');
SELECT is((SELECT COUNT(*)::bigint FROM stats.daily_activity), 2::bigint, 'range seed preserves out-of-range daily_activity rows');
SELECT is((SELECT comments FROM stats.daily_activity WHERE activity_date = '2020-06-02'), 1::bigint, 'range seed refreshes the requested day');
SELECT is((SELECT points_contributed FROM stats.daily_activity WHERE activity_date = '2020-06-02'), 12.3456::numeric, 'range seed preserves unrelated daily_activity metrics');
SELECT is((SELECT COUNT(*)::bigint FROM stats.daily_active_users WHERE activity_date = '2020-06-01' AND user_id = 9701), 1::bigint, 'range seed preserves out-of-range daily_active_users rows');

SELECT throws_ok(
  $$SELECT stats.fn_seed_daily_activity('[2020-06-02 12:00:00+00,2020-06-03 00:00:00+00)'::tstzrange);$$,
  '22023',
  NULL,
  'non-day-aligned ranges are rejected'
);

SELECT * FROM finish();
ROLLBACK;
