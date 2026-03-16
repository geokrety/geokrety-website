BEGIN;
SELECT plan(15);

DELETE FROM stats.daily_entity_counts;
DELETE FROM stats.job_log WHERE job_name = 'fn_snapshot_daily_entity_counts';

SELECT has_function('stats', 'fn_snapshot_daily_entity_counts', ARRAY[]::text[], 'fn_snapshot_daily_entity_counts function exists');
SELECT function_returns('stats', 'fn_snapshot_daily_entity_counts', ARRAY[]::text[], 'bigint', 'fn_snapshot_daily_entity_counts returns bigint');

INSERT INTO gk_users (id, username, registration_ip, joined_on_datetime)
VALUES
  (25401, 'snapshot-user-1', '127.0.0.1', '2026-01-01 08:00:00+00'),
  (25402, 'snapshot-user-2', '127.0.0.1', '2026-01-03 08:00:00+00');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES
  (25410, 'Snapshot GK 1', 0, 25401, 25401, '2026-01-01 09:00:00+00'),
  (25411, 'Snapshot GK 2', 3, 25401, 25401, '2026-01-02 09:00:00+00');

INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES
  (25420, 25410, 25401, 'GC254A', 'pl', coords2position(52.22968, 21.01223), '2026-01-01 10:00:00+00', 0),
  (25421, 25410, 25402, NULL, 'de', coords2position(52.52000, 13.40500), '2026-01-02 10:00:00+00', 1),
  (25422, 25411, 25402, NULL, NULL, NULL, '2026-01-03 10:00:00+00', 2);

INSERT INTO gk_pictures (id, geokret, move, "user", type, filename, uploaded_on_datetime)
VALUES (25430, 25410, 25420, NULL, 1, 'snapshot-daily-entity-counts.jpg', '2026-01-02 11:00:00+00');

INSERT INTO gk_loves (id, geokret, "user", created_on_datetime)
VALUES (25440, 25410, 25402, '2026-01-03 12:00:00+00');

SELECT is(stats.fn_snapshot_daily_entity_counts(), 75::bigint, 'full daily_entity_counts snapshot writes three days for all 25 canonical entities');
SELECT is((SELECT cnt FROM stats.daily_entity_counts WHERE count_date = '2026-01-01' AND entity = 'gk_users'), 1::bigint, 'day 1 user count is cumulative from joined_on_datetime');
SELECT is((SELECT cnt FROM stats.daily_entity_counts WHERE count_date = '2026-01-02' AND entity = 'gk_geokrety'), 2::bigint, 'day 2 GeoKret total includes both created rows');
SELECT is((SELECT cnt FROM stats.daily_entity_counts WHERE count_date = '2026-01-02' AND entity = 'gk_pictures_type_1'), 1::bigint, 'day 2 uploaded move picture count is cumulative');
SELECT is((SELECT cnt FROM stats.daily_entity_counts WHERE count_date = '2026-01-03' AND entity = 'gk_moves_type_2'), 1::bigint, 'day 3 comment move count is cumulative');
SELECT is((SELECT cnt FROM stats.daily_entity_counts WHERE count_date = '2026-01-03' AND entity = 'gk_loves'), 1::bigint, 'day 3 love count is cumulative');

UPDATE stats.daily_entity_counts
SET cnt = 999
WHERE count_date = '2026-01-02'
  AND entity = 'gk_geokrety';

INSERT INTO stats.daily_entity_counts (count_date, entity, cnt)
VALUES ('2026-01-03', 'ghost_entity', 7);

SELECT is(stats.fn_snapshot_daily_entity_counts(), 75::bigint, 'rerunning daily_entity_counts snapshot repairs stale rows and removes ghosts');
SELECT is((SELECT cnt FROM stats.daily_entity_counts WHERE count_date = '2026-01-02' AND entity = 'gk_geokrety'), 2::bigint, 'rerun repairs the corrupted cumulative count');
SELECT is((SELECT COUNT(*)::bigint FROM stats.daily_entity_counts WHERE entity = 'ghost_entity'), 0::bigint, 'rerun removes ghost entities outside the canonical catalog');
SELECT ok((SELECT COUNT(*) = 2 FROM stats.job_log WHERE job_name = 'fn_snapshot_daily_entity_counts' AND status = 'ok'), 'daily_entity_counts snapshot logs one ok job_log row per execution');
SELECT ok((SELECT metadata ? 'timing_ms' FROM stats.job_log WHERE job_name = 'fn_snapshot_daily_entity_counts' ORDER BY id DESC LIMIT 1), 'daily_entity_counts snapshot logs timing metadata');

SELECT * FROM finish();
ROLLBACK;
