BEGIN;
SELECT plan(22);

ALTER TABLE geokrety.gk_moves DISABLE TRIGGER after_99_notify_amqp_moves;

CREATE TEMP TABLE counter_baseline AS
SELECT entity, shard, cnt
FROM stats.entity_counters_shard
WHERE entity = 'gk_moves'
    OR entity LIKE 'gk_moves_type_%';

INSERT INTO gk_users (id, username, registration_ip) VALUES (701, 'counter-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (801, 'Counter insert GK', 0, '2020-01-01 00:00:00+00');

SELECT has_function('geokrety', 'fn_gk_moves_sharded_counter', ARRAY[]::text[], 'fn_gk_moves_sharded_counter function exists');
SELECT function_returns('geokrety', 'fn_gk_moves_sharded_counter', ARRAY[]::text[], 'trigger', 'fn_gk_moves_sharded_counter returns trigger');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_after_sharded_counters', 'tr_gk_moves_after_sharded_counters trigger exists');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (801, 801, 701, coords2position(52.22968, 21.01223), '2020-01-02 10:00:00+00', 0);

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves'), (SELECT SUM(cnt)::bigint + 1 FROM counter_baseline WHERE entity = 'gk_moves'), 'insert increments the total gk_moves counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_0'), (SELECT SUM(cnt)::bigint + 1 FROM counter_baseline WHERE entity = 'gk_moves_type_0'), 'insert increments the type-0 counter');
SELECT is((SELECT cnt::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves' AND shard = 1), (SELECT cnt::bigint + 1 FROM counter_baseline WHERE entity = 'gk_moves' AND shard = 1), 'insert updates shard id % 16 for the total counter');

DELETE FROM gk_moves WHERE id = 801;

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves'), (SELECT SUM(cnt)::bigint FROM counter_baseline WHERE entity = 'gk_moves'), 'delete decrements the total gk_moves counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_0'), (SELECT SUM(cnt)::bigint FROM counter_baseline WHERE entity = 'gk_moves_type_0'), 'delete decrements the original type counter');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (820, 'Counter type 0 GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (821, 'Counter type 1 GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (822, 'Counter type 2 GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (823, 'Counter type 3 GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime, owner) VALUES (824, 'Counter type 4 GK', 0, '2020-01-01 00:00:00+00', 701);
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (825, 'Counter type 5 GK', 0, '2020-01-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (810, 820, 701, coords2position(52.22968, 21.01223), '2020-01-03 10:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (811, 821, 701, '2020-01-03 11:00:00+00', 1);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (812, 822, 701, '2020-01-03 12:00:00+00', 2);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (813, 823, 701, coords2position(52.52000, 13.40500), '2020-01-03 13:00:00+00', 3);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (814, 824, 701, '2020-01-03 14:00:00+00', 4);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (815, 825, 701, coords2position(48.20820, 16.37380), '2020-01-03 15:00:00+00', 5);

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves'), (SELECT SUM(cnt)::bigint + 6 FROM counter_baseline WHERE entity = 'gk_moves'), 'one insert for each move type produces six total moves');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_0'), (SELECT SUM(cnt)::bigint + 1 FROM counter_baseline WHERE entity = 'gk_moves_type_0'), 'type-0 rows increment their dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_1'), (SELECT SUM(cnt)::bigint + 1 FROM counter_baseline WHERE entity = 'gk_moves_type_1'), 'type-1 rows increment their dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_2'), (SELECT SUM(cnt)::bigint + 1 FROM counter_baseline WHERE entity = 'gk_moves_type_2'), 'type-2 rows increment their dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_3'), (SELECT SUM(cnt)::bigint + 1 FROM counter_baseline WHERE entity = 'gk_moves_type_3'), 'type-3 rows increment their dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_4'), (SELECT SUM(cnt)::bigint + 1 FROM counter_baseline WHERE entity = 'gk_moves_type_4'), 'type-4 rows increment their dedicated counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_5'), (SELECT SUM(cnt)::bigint + 1 FROM counter_baseline WHERE entity = 'gk_moves_type_5'), 'type-5 rows increment their dedicated counter');

UPDATE gk_moves
SET comment = 'noop update'
WHERE id = 811;

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves'), (SELECT SUM(cnt)::bigint + 6 FROM counter_baseline WHERE entity = 'gk_moves'), 'update without id or move_type change leaves the total counter unchanged');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_1'), (SELECT SUM(cnt)::bigint + 1 FROM counter_baseline WHERE entity = 'gk_moves_type_1'), 'update without id or move_type change leaves the type counter unchanged');

UPDATE gk_moves
SET move_type = 5,
    position = coords2position(50.07550, 14.43780)
WHERE id = 812;

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves'), (SELECT SUM(cnt)::bigint + 6 FROM counter_baseline WHERE entity = 'gk_moves'), 'type-changing update keeps the total counter stable');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_2'), (SELECT SUM(cnt)::bigint FROM counter_baseline WHERE entity = 'gk_moves_type_2'), 'type-changing update removes the old type contribution');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_5'), (SELECT SUM(cnt)::bigint + 2 FROM counter_baseline WHERE entity = 'gk_moves_type_5'), 'type-changing update applies the new type contribution');

DELETE FROM gk_moves WHERE id = 815;

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves'), (SELECT SUM(cnt)::bigint + 5 FROM counter_baseline WHERE entity = 'gk_moves'), 'delete after updates decrements the total counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_5'), (SELECT SUM(cnt)::bigint + 1 FROM counter_baseline WHERE entity = 'gk_moves_type_5'), 'delete after updates decrements the correct type counter');

SELECT * FROM finish();
ROLLBACK;
