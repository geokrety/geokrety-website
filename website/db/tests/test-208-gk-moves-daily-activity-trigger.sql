BEGIN;
SELECT plan(19);

INSERT INTO gk_users (id, username, registration_ip)
VALUES (741, 'daily-activity-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime)
VALUES (841, 'Daily activity drop GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (900, 841, 741, coords2position(52.22968, 21.01223), '2020-01-01 10:00:00+00', 0);

SELECT has_function('geokrety', 'fn_gk_moves_daily_activity', ARRAY[]::text[], 'fn_gk_moves_daily_activity function exists');
SELECT function_returns('geokrety', 'fn_gk_moves_daily_activity', ARRAY[]::text[], 'trigger', 'fn_gk_moves_daily_activity returns trigger');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_after_daily_activity', 'tr_gk_moves_after_daily_activity trigger exists');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (901, 841, 741, coords2position(52.52000, 13.40500), '2020-01-02 10:00:00+00', 0);

SELECT is((SELECT drops FROM stats.daily_activity WHERE activity_date = '2020-01-02'), 1::bigint, 'insert DROP increments drops');
SELECT is((SELECT total_moves FROM stats.daily_activity WHERE activity_date = '2020-01-02'), 1::bigint, 'insert DROP increments total_moves');
SELECT is(
    (SELECT km_contributed FROM stats.daily_activity WHERE activity_date = '2020-01-02'),
    (SELECT km_distance FROM gk_moves WHERE id = 901),
    'insert stores the move km_distance in daily_activity.km_contributed'
);
SELECT is((SELECT COUNT(*)::bigint FROM stats.daily_active_users WHERE activity_date = '2020-01-02' AND user_id = 741), 1::bigint, 'insert with author records daily_active_users presence');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime)
VALUES (842, 'Daily activity grab GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (910, 842, 741, '2020-01-03 10:00:00+00', 1);

SELECT is((SELECT grabs FROM stats.daily_activity WHERE activity_date = '2020-01-03'), 1::bigint, 'insert GRAB increments grabs');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime)
VALUES (843, 'Anonymous daily activity GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, username, moved_on_datetime, move_type)
VALUES (920, 843, 'anonymous-move', '2020-01-04 10:00:00+00', 2);

SELECT is((SELECT COUNT(*)::bigint FROM stats.daily_active_users WHERE activity_date = '2020-01-04'), 0::bigint, 'insert with NULL author skips daily_active_users');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime)
VALUES (844, 'Repeated presence A GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime)
VALUES (845, 'Repeated presence B GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (930, 844, 741, coords2position(48.20820, 16.37380), '2020-01-05 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (931, 845, 741, '2020-01-05 09:00:00+00', 2);

SELECT is((SELECT COUNT(*)::bigint FROM stats.daily_active_users WHERE activity_date = '2020-01-05' AND user_id = 741), 1::bigint, 'second move by the same user keeps one presence row');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime)
VALUES (846, 'Move between days GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (940, 846, 741, coords2position(50.07550, 14.43780), '2020-01-06 11:00:00+00', 0);

UPDATE gk_moves
SET moved_on_datetime = '2020-01-07 11:00:00+00'
WHERE id = 940;

SELECT is((SELECT total_moves FROM stats.daily_activity WHERE activity_date = '2020-01-06'), 0::bigint, 'update moving a row to a new day decrements the old day');
SELECT is((SELECT total_moves FROM stats.daily_activity WHERE activity_date = '2020-01-07'), 1::bigint, 'update moving a row to a new day increments the new day');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime)
VALUES (847, 'Move type update GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (950, 847, 741, coords2position(48.85660, 2.35220), '2020-01-08 12:00:00+00', 0);

UPDATE gk_moves
SET move_type = 5
WHERE id = 950;

SELECT is((SELECT drops FROM stats.daily_activity WHERE activity_date = '2020-01-08'), 0::bigint, 'update changing move_type decrements the old type column');
SELECT is((SELECT dips FROM stats.daily_activity WHERE activity_date = '2020-01-08'), 1::bigint, 'update changing move_type increments the new type column');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime)
VALUES (848, 'Successor km refresh GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (960, 848, 741, coords2position(52.22968, 21.01223), '2020-01-09 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (961, 848, 741, coords2position(52.52000, 13.40500), '2020-01-09 09:00:00+00', 0);

UPDATE gk_moves
SET position = coords2position(50.07550, 14.43780)
WHERE id = 960;

SELECT is(
    (SELECT km_contributed FROM stats.daily_activity WHERE activity_date = '2020-01-09'),
    (SELECT COALESCE(SUM(km_distance), 0)::numeric FROM gk_moves WHERE geokret = 848 AND moved_on_datetime::date = '2020-01-09'),
    'updating an earlier move keeps km_contributed aligned with successor km rewrites'
);

INSERT INTO gk_geokrety (id, name, type, created_on_datetime)
VALUES (849, 'Cross-day successor km refresh GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (970, 849, 741, coords2position(52.22968, 21.01223), '2020-01-10 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (971, 849, 741, coords2position(52.52000, 13.40500), '2020-01-11 08:00:00+00', 0);

UPDATE gk_moves
SET position = coords2position(41.90280, 12.49640)
WHERE id = 970;

SELECT is(
    (SELECT km_contributed FROM stats.daily_activity WHERE activity_date = '2020-01-11'),
    (SELECT COALESCE(SUM(km_distance), 0)::numeric FROM gk_moves WHERE moved_on_datetime::date = '2020-01-11' AND geokret = 849),
    'successor rewrites on a different day refresh that successor day as well'
);

DELETE FROM gk_moves WHERE id = 950;

SELECT is((SELECT total_moves FROM stats.daily_activity WHERE activity_date = '2020-01-08'), 0::bigint, 'delete decrements total_moves');
SELECT is((SELECT dips FROM stats.daily_activity WHERE activity_date = '2020-01-08'), 0::bigint, 'delete decrements the current type column');
SELECT is((SELECT COUNT(*)::bigint FROM stats.daily_active_users WHERE activity_date = '2020-01-08' AND user_id = 741), 0::bigint, 'delete removes daily_active_users rows when no authored move remains for the day');

SELECT * FROM finish();
ROLLBACK;
