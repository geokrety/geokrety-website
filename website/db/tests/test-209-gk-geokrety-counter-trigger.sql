BEGIN;
SELECT plan(22);

CREATE TEMP TABLE geokrety_counter_baseline AS
SELECT entity, shard, cnt
FROM stats.entity_counters_shard
WHERE entity = 'gk_geokrety'
   OR entity LIKE 'gk_geokrety_type_%';

INSERT INTO gk_users (id, username, registration_ip)
VALUES (9741, 'geokrety-counter-user', '127.0.0.1');

SELECT has_function('geokrety', 'fn_gk_geokrety_counter', ARRAY[]::text[], 'fn_gk_geokrety_counter function exists');
SELECT function_returns('geokrety', 'fn_gk_geokrety_counter', ARRAY[]::text[], 'trigger', 'fn_gk_geokrety_counter returns trigger');
SELECT has_trigger('geokrety', 'gk_geokrety', 'tr_gk_geokrety_counters', 'tr_gk_geokrety_counters trigger exists');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (9880, 'Counter single GK', 0, 9741, 9741, '2020-02-01 10:00:00+00');

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety'), (SELECT SUM(cnt)::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety'), 'insert increments the total gk_geokrety counter');
SELECT is((SELECT cnt::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_0' AND shard = 8), (SELECT cnt::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_0' AND shard = 8), 'insert updates the correct type shard for type 0');
SELECT is((SELECT cnt::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety' AND shard = 8), (SELECT cnt::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety' AND shard = 8), 'insert updates shard id % 16 for the total counter');
SELECT is((SELECT gk_created FROM stats.daily_activity WHERE activity_date = '2020-02-01'), 1::bigint, 'insert updates daily_activity.gk_created');

DELETE FROM gk_geokrety WHERE id = 9880;

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety'), (SELECT SUM(cnt)::bigint FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety'), 'delete decrements the total gk_geokrety counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_0'), (SELECT SUM(cnt)::bigint FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_0'), 'delete decrements the type-specific counter');
SELECT is((SELECT gk_created FROM stats.daily_activity WHERE activity_date = '2020-02-01'), 0::bigint, 'delete refreshes daily_activity.gk_created back to zero');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime) VALUES (9881, 'Type 0 GK', 0, 9741, 9741, '2020-02-02 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime) VALUES (9882, 'Type 1 GK', 1, 9741, 9741, '2020-02-02 01:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime) VALUES (9883, 'Type 2 GK', 2, 9741, 9741, '2020-02-02 02:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime) VALUES (9884, 'Type 3 GK', 3, 9741, 9741, '2020-02-02 03:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime) VALUES (9885, 'Type 4 GK', 4, 9741, 9741, '2020-02-02 04:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime) VALUES (9886, 'Type 5 GK', 5, 9741, 9741, '2020-02-02 05:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime) VALUES (9887, 'Type 6 GK', 6, 9741, 9741, '2020-02-02 06:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime) VALUES (9888, 'Type 7 GK', 7, 9741, 9741, '2020-02-02 07:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime) VALUES (9889, 'Type 8 GK', 8, 9741, 9741, '2020-02-02 08:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime) VALUES (9890, 'Type 9 GK', 9, 9741, 9741, '2020-02-02 09:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime) VALUES (9891, 'Type 10 GK', 10, 9741, 9741, '2020-02-02 10:00:00+00');

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_0'), (SELECT SUM(cnt)::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_0'), 'type-0 insert increments its dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_1'), (SELECT SUM(cnt)::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_1'), 'type-1 insert increments its dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_2'), (SELECT SUM(cnt)::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_2'), 'type-2 insert increments its dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_3'), (SELECT SUM(cnt)::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_3'), 'type-3 insert increments its dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_4'), (SELECT SUM(cnt)::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_4'), 'type-4 insert increments its dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_5'), (SELECT SUM(cnt)::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_5'), 'type-5 insert increments its dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_6'), (SELECT SUM(cnt)::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_6'), 'type-6 insert increments its dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_7'), (SELECT SUM(cnt)::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_7'), 'type-7 insert increments its dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_8'), (SELECT SUM(cnt)::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_8'), 'type-8 insert increments its dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_9'), (SELECT SUM(cnt)::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_9'), 'type-9 insert increments its dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_10'), (SELECT SUM(cnt)::bigint + 1 FROM geokrety_counter_baseline WHERE entity = 'gk_geokrety_type_10'), 'type-10 insert increments its dedicated counter');
SELECT is((SELECT gk_created FROM stats.daily_activity WHERE activity_date = '2020-02-02'), 11::bigint, 'daily_activity.gk_created tracks all inserted GeoKrety for the day');

SELECT * FROM finish();
ROLLBACK;
