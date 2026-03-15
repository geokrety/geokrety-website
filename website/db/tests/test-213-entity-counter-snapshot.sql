BEGIN;
SELECT plan(15);

INSERT INTO gk_users (id, username, registration_ip, joined_on_datetime)
VALUES (9201, 'snapshot-user-1', '127.0.0.1', '2020-05-01 08:00:00+00');
INSERT INTO gk_users (id, username, registration_ip, joined_on_datetime)
VALUES (9202, 'snapshot-user-2', '127.0.0.1', '2020-05-02 08:00:00+00');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (9301, 'Snapshot GK 0', 0, 9201, 9201, '2020-05-01 09:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (9302, 'Snapshot GK 3', 3, 9202, 9202, '2020-05-02 09:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (9401, 9301, 9201, coords2position(52.22968, 21.01223), '2020-05-03 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (9402, 9302, 9202, '2020-05-03 09:00:00+00', 2);

INSERT INTO gk_pictures (id, author, geokret, type, created_on_datetime, uploaded_on_datetime)
VALUES (9501, 9201, 9301, 0, '2020-05-04 10:00:00+00', '2020-05-04 10:05:00+00');
INSERT INTO gk_pictures (id, author, move, geokret, type, created_on_datetime, uploaded_on_datetime)
VALUES (9502, 9201, 9401, 9301, 1, '2020-05-04 11:00:00+00', '2020-05-04 11:05:00+00');
INSERT INTO gk_pictures (id, author, "user", type, created_on_datetime, uploaded_on_datetime)
VALUES (9503, 9202, 9202, 2, '2020-05-04 12:00:00+00', '2020-05-04 12:05:00+00');

INSERT INTO gk_loves (id, "user", geokret, created_on_datetime)
VALUES (9601, 9201, 9302, '2020-05-05 10:00:00+00');

SELECT has_function('stats', 'fn_snapshot_entity_counters', ARRAY[]::text[], 'fn_snapshot_entity_counters function exists');
SELECT function_returns('stats', 'fn_snapshot_entity_counters', ARRAY[]::text[], 'void', 'fn_snapshot_entity_counters returns void');
SELECT lives_ok($$SELECT stats.fn_snapshot_entity_counters();$$, 'snapshot function executes without error');
SELECT is((SELECT COUNT(DISTINCT entity)::int FROM stats.entity_counters_shard), 25, 'snapshot refreshes the canonical 25 entities');
SELECT is((SELECT COUNT(*)::int FROM stats.entity_counters_shard), 400, 'snapshot recreates the canonical 400 shard rows');
SELECT set_eq(
  $$SELECT DISTINCT entity::text FROM stats.entity_counters_shard$$,
  $$VALUES
    ('gk_moves'::text),
    ('gk_moves_type_0'::text),
    ('gk_moves_type_1'::text),
    ('gk_moves_type_2'::text),
    ('gk_moves_type_3'::text),
    ('gk_moves_type_4'::text),
    ('gk_moves_type_5'::text),
    ('gk_geokrety'::text),
    ('gk_geokrety_type_0'::text),
    ('gk_geokrety_type_1'::text),
    ('gk_geokrety_type_2'::text),
    ('gk_geokrety_type_3'::text),
    ('gk_geokrety_type_4'::text),
    ('gk_geokrety_type_5'::text),
    ('gk_geokrety_type_6'::text),
    ('gk_geokrety_type_7'::text),
    ('gk_geokrety_type_8'::text),
    ('gk_geokrety_type_9'::text),
    ('gk_geokrety_type_10'::text),
    ('gk_pictures'::text),
    ('gk_pictures_type_0'::text),
    ('gk_pictures_type_1'::text),
    ('gk_pictures_type_2'::text),
    ('gk_users'::text),
    ('gk_loves'::text)$$,
  'snapshot recreates the canonical entity catalog'
);
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves'), 2::bigint, 'gk_moves total matches the source table');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves_type_2'), 1::bigint, 'gk_moves type counters match the source table');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety'), 2::bigint, 'gk_geokrety total matches the source table');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_geokrety_type_3'), 1::bigint, 'gk_geokrety type counters match the source table');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_pictures'), 3::bigint, 'gk_pictures total matches the source table');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_pictures_type_1'), 1::bigint, 'gk_pictures type counters match the source table');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_users'), 2::bigint, 'gk_users total matches the source table');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_loves'), 1::bigint, 'gk_loves total matches the source table');

CREATE TEMP TABLE entity_counter_snapshot_first AS
SELECT entity, shard, cnt
FROM stats.entity_counters_shard
ORDER BY entity, shard;

SELECT stats.fn_snapshot_entity_counters();

SELECT results_eq(
  $$SELECT entity, shard, cnt FROM stats.entity_counters_shard ORDER BY entity, shard$$,
  $$SELECT entity, shard, cnt FROM entity_counter_snapshot_first ORDER BY entity, shard$$,
  'snapshot is idempotent when re-run'
);

SELECT * FROM finish();
ROLLBACK;
