BEGIN;

SELECT plan(7);

ALTER TABLE geokrety.gk_moves DISABLE TRIGGER after_99_notify_amqp_moves;

CREATE TEMP TABLE daily_activity_baseline AS
SELECT
	COALESCE((SELECT total_moves FROM stats.daily_activity WHERE activity_date = '2020-02-01'), 0::bigint) AS total_moves,
	COALESCE((SELECT drops FROM stats.daily_activity WHERE activity_date = '2020-02-01'), 0::bigint) AS drops,
	COALESCE((SELECT dips FROM stats.daily_activity WHERE activity_date = '2020-02-01'), 0::bigint) AS dips,
	COALESCE((SELECT COUNT(*)::bigint FROM stats.daily_active_users WHERE activity_date = '2020-02-01'), 0::bigint) AS active_users;

INSERT INTO gk_users (id, username, registration_ip) VALUES (926600001, 'daily-activity-owner', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (926600002, 'daily-activity-user-a', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (926600003, 'daily-activity-user-b', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (926600010, 'Daily Activity GK', 0, 926600001, 926600001, '2020-02-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (926600020, 926600010, 926600002, coords2position(52.22968, 21.01223), '2020-02-01 08:00:00+00', 0);

SELECT is((SELECT total_moves FROM stats.daily_activity WHERE activity_date = '2020-02-01') - (SELECT total_moves FROM daily_activity_baseline), 1::bigint, 'first insert increments total_moves for the day');
SELECT is((SELECT drops FROM stats.daily_activity WHERE activity_date = '2020-02-01') - (SELECT drops FROM daily_activity_baseline), 1::bigint, 'first insert increments the move-type bucket');
SELECT is((SELECT COUNT(*)::bigint FROM stats.daily_active_users WHERE activity_date = '2020-02-01' AND user_id = 926600002), 1::bigint, 'first insert seeds daily_active_users for the author');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (926600021, 926600010, 926600002, coords2position(50.06143, 19.93658), '2020-02-01 09:00:00+00', 5);

SELECT is((SELECT total_moves FROM stats.daily_activity WHERE activity_date = '2020-02-01') - (SELECT total_moves FROM daily_activity_baseline), 2::bigint, 'same-day inserts increment total_moves in place');
SELECT is((SELECT dips FROM stats.daily_activity WHERE activity_date = '2020-02-01') - (SELECT dips FROM daily_activity_baseline), 1::bigint, 'same-day inserts increment the dip bucket in place');
SELECT is((SELECT COUNT(*)::bigint FROM stats.daily_active_users WHERE activity_date = '2020-02-01' AND user_id = 926600002), 1::bigint, 'same-author same-day inserts do not duplicate daily_active_users rows');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (926600022, 926600010, 926600003, coords2position(52.52000, 13.40500), '2020-02-01 10:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.daily_active_users WHERE activity_date = '2020-02-01') - (SELECT active_users FROM daily_activity_baseline), 2::bigint, 'new authors on the same day are appended to daily_active_users');

SELECT * FROM finish();
ROLLBACK;
