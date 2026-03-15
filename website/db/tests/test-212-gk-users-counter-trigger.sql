BEGIN;
SELECT plan(8);

CREATE TEMP TABLE gk_users_counter_baseline AS
SELECT entity, shard, cnt
FROM stats.entity_counters_shard
WHERE entity = 'gk_users';

SELECT has_function('geokrety', 'fn_gk_users_counter', ARRAY[]::text[], 'fn_gk_users_counter function exists');
SELECT function_returns('geokrety', 'fn_gk_users_counter', ARRAY[]::text[], 'trigger', 'fn_gk_users_counter returns trigger');
SELECT has_trigger('geokrety', 'gk_users', 'tr_gk_users_activity', 'tr_gk_users_activity trigger exists');

INSERT INTO gk_users (id, username, registration_ip, joined_on_datetime)
VALUES (9109, 'users-counter-user', '127.0.0.1', '2020-04-10 14:00:00+00');

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_users'), (SELECT SUM(cnt)::bigint + 1 FROM gk_users_counter_baseline WHERE entity = 'gk_users'), 'insert increments the total gk_users counter');
SELECT is((SELECT cnt::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_users' AND shard = 5), (SELECT cnt::bigint + 1 FROM gk_users_counter_baseline WHERE entity = 'gk_users' AND shard = 5), 'insert updates the shard chosen by id % 16');
SELECT is((SELECT users_registered FROM stats.daily_activity WHERE activity_date = '2020-04-10'), 1::bigint, 'insert refreshes daily_activity.users_registered');

DELETE FROM gk_users WHERE id = 9109;

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_users'), (SELECT SUM(cnt)::bigint FROM gk_users_counter_baseline WHERE entity = 'gk_users'), 'delete decrements the total gk_users counter');
SELECT is((SELECT users_registered FROM stats.daily_activity WHERE activity_date = '2020-04-10'), 0::bigint, 'delete refreshes daily_activity.users_registered back to zero');

SELECT * FROM finish();
ROLLBACK;
