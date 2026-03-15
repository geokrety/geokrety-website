BEGIN;
SELECT plan(14);

DELETE FROM stats.job_log WHERE job_name = 'fn_backfill_heavy_previous_move_id_all';
DELETE FROM gk_moves WHERE id IN (24520, 24521, 24522, 24523);
DELETE FROM gk_geokrety WHERE id = 24510;
DELETE FROM gk_users WHERE id = 24501;

SELECT has_function('stats', 'fn_backfill_heavy_previous_move_id_all', ARRAY['integer', 'integer'], 'fn_backfill_heavy_previous_move_id_all function exists');
SELECT function_returns('stats', 'fn_backfill_heavy_previous_move_id_all', ARRAY['integer', 'integer'], 'text', 'fn_backfill_heavy_previous_move_id_all returns text');

INSERT INTO gk_users (id, username, registration_ip) VALUES (24501, 'heavy-prev-user', '127.0.0.1');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (24510, 'Heavy previous GK', 0, 24501, 24501, '2019-01-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24520, 24510, 24501, coords2position(52.22968, 21.01223), '2019-01-02 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24521, 24510, 24501, coords2position(50.06143, 19.93658), '2020-01-02 08:00:00+00', 1);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (24523, 24510, 24501, '2020-06-02 08:00:00+00', 1);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24522, 24510, 24501, coords2position(51.10788, 17.03854), '2021-01-02 08:00:00+00', 5);

SET CONSTRAINTS ALL IMMEDIATE;
ALTER TABLE geokrety.gk_moves DISABLE TRIGGER ALL;

UPDATE gk_moves
SET previous_move_id = NULL,
	previous_position_id = NULL,
	km_distance = NULL
WHERE id IN (24520, 24521, 24522, 24523);

ALTER TABLE geokrety.gk_moves ENABLE TRIGGER ALL;
SET CONSTRAINTS ALL IMMEDIATE;

REFRESH MATERIALIZED VIEW stats.mv_backfill_working_set;

SELECT ok(position('completed:' IN stats.fn_backfill_heavy_previous_move_id_all(5000, 12)) > 0, 'bounded heavy previous-move backfill executes successfully');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 24523), 24521::bigint, 'bounded heavy backfill repairs event-chain rows inside the processed month window');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 24523), 24521::bigint, 'bounded heavy backfill restores previous_position_id for non-position moves too');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 24522), NULL::bigint, 'bounded heavy backfill leaves later months untouched');
SELECT ok(position('completed:' IN stats.fn_backfill_heavy_previous_move_id_all(5000, NULL)) > 0, 'unlimited heavy previous-move backfill completes the remaining months');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 24522), 24523::bigint, 'unlimited heavy backfill restores previous_move_id from the event chain');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 24522), 24521::bigint, 'unlimited heavy backfill restores previous_position_id from the positioned chain');
SELECT ok((SELECT km_distance IS NOT NULL FROM gk_moves WHERE id = 24522), 'unlimited heavy backfill restores km_distance from the MV');

SELECT ok(position('nothing to backfill' IN stats.fn_backfill_heavy_previous_move_id_all(5000)) > 0, 'legacy one-argument heavy previous-move call exits early when data is already filled');
SELECT ok((SELECT status = 'ok' FROM stats.job_log WHERE job_name = 'fn_backfill_heavy_previous_move_id_all' ORDER BY id DESC LIMIT 1), 'heavy previous-move backfill logs ok status');
SELECT ok((SELECT metadata ? 'summary' FROM stats.job_log WHERE job_name = 'fn_backfill_heavy_previous_move_id_all' ORDER BY id DESC LIMIT 1), 'heavy previous-move backfill stores a summary');
SELECT ok((SELECT metadata ? 'month_limit' FROM stats.job_log WHERE job_name = 'fn_backfill_heavy_previous_move_id_all' ORDER BY id DESC LIMIT 1), 'heavy previous-move backfill logs month_limit metadata');

SELECT * FROM finish();
ROLLBACK;
