BEGIN;
SELECT plan(16);

CREATE TEMP TABLE gk_loves_counter_baseline AS
SELECT entity, shard, cnt
FROM stats.entity_counters_shard
WHERE entity = 'gk_loves';

INSERT INTO gk_users (id, username, registration_ip)
VALUES (23701, 'loves-owner', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip)
VALUES (23702, 'loves-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (23710, 'Loved GK in PL', 0, 23701, 23701, '2020-09-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (23711, 'Loved GK without location', 0, 23701, 23701, '2020-09-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23720, 23710, 23701, coords2position(52.22968, 21.01223), '2020-09-01 08:00:00+00', 0);

SELECT has_function('geokrety', 'fn_gk_loves_activity', ARRAY[]::text[], 'fn_gk_loves_activity function exists');
SELECT function_returns('geokrety', 'fn_gk_loves_activity', ARRAY[]::text[], 'trigger', 'fn_gk_loves_activity returns trigger');
SELECT has_trigger('geokrety', 'gk_loves', 'tr_gk_loves_activity', 'tr_gk_loves_activity trigger exists');

INSERT INTO gk_loves (id, "user", geokret, created_on_datetime)
VALUES (23730, 23702, 23710, '2020-09-02 10:00:00+00');

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_loves'), (SELECT COALESCE(SUM(cnt)::bigint, 0::bigint) + 1 FROM gk_loves_counter_baseline), 'insert increments the gk_loves shard counter total');
SELECT is((SELECT loves_count FROM stats.daily_activity WHERE activity_date = '2020-09-02'), 1::bigint, 'insert refreshes daily_activity.loves_count');
SELECT is((SELECT loves_count FROM stats.country_daily_stats WHERE stats_date = '2020-09-02' AND country_code = 'pl'), 1::bigint, 'insert refreshes country_daily_stats.loves_count using the loved GK current country');

UPDATE gk_loves
SET created_on_datetime = '2020-09-03 10:00:00+00'
WHERE id = 23730;

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_loves'), (SELECT COALESCE(SUM(cnt)::bigint, 0::bigint) + 1 FROM gk_loves_counter_baseline), 'updates that keep the same love row preserve the total counter');
SELECT is((SELECT loves_count FROM stats.daily_activity WHERE activity_date = '2020-09-02'), 0::bigint, 'updating the love date repairs the old daily loves bucket back to zero');
SELECT is((SELECT loves_count FROM stats.daily_activity WHERE activity_date = '2020-09-03'), 1::bigint, 'updating the love date refreshes the new daily loves bucket');
SELECT is((SELECT loves_count FROM stats.country_daily_stats WHERE stats_date = '2020-09-03' AND country_code = 'pl'), 1::bigint, 'updating the love date refreshes the new country loves bucket');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23721, 23710, 23701, coords2position(52.52000, 13.40500), '2020-09-05 08:00:00+00', 0);

DELETE FROM gk_loves WHERE id = 23730;

SELECT is(COALESCE((SELECT loves_count FROM stats.country_daily_stats WHERE stats_date = '2020-09-03' AND country_code = 'pl'), 0::bigint), 0::bigint, 'delete repairs the original love-time country bucket even after the GeoKret later moved');
SELECT is((SELECT COUNT(*)::bigint FROM stats.country_daily_stats WHERE stats_date = '2020-09-03' AND country_code = 'de' AND loves_count > 0), 0::bigint, 'delete does not mutate the later country bucket for historical loves');

INSERT INTO gk_loves (id, "user", geokret, created_on_datetime)
VALUES (23731, 23702, 23711, '2020-09-06 10:00:00+00');

SELECT is((SELECT loves_count FROM stats.daily_activity WHERE activity_date = '2020-09-06'), 1::bigint, 'missing country context still refreshes daily loves counts');
SELECT is((SELECT COUNT(*)::bigint FROM stats.country_daily_stats WHERE stats_date = '2020-09-06' AND loves_count > 0), 0::bigint, 'missing country context skips only the country rollup');

DELETE FROM gk_loves WHERE id = 23731;

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_loves'), (SELECT COALESCE(SUM(cnt)::bigint, 0::bigint) FROM gk_loves_counter_baseline), 'deletes restore the gk_loves shard counter total');
SELECT is((SELECT loves_count FROM stats.daily_activity WHERE activity_date = '2020-09-06'), 0::bigint, 'delete repairs the final daily loves bucket back to zero');

SELECT * FROM finish();
ROLLBACK;
