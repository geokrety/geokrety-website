-- TODO you MUST remove this file and associated function `fn_backfill_km_distance`, the backfill do not need it and if not needed by live function then we're good
BEGIN;
SELECT plan(15);

DELETE FROM stats.job_log WHERE job_name = 'fn_backfill_km_distance';

SELECT has_function('stats', 'fn_backfill_km_distance', ARRAY['tstzrange', 'integer'], 'fn_backfill_km_distance function exists');
SELECT function_returns('stats', 'fn_backfill_km_distance', ARRAY['tstzrange', 'integer'], 'bigint', 'fn_backfill_km_distance returns bigint');

INSERT INTO gk_users (id, username, registration_ip) VALUES (24601, 'backfill-km-user', '127.0.0.1');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (24610, 'Backfill km GK', 0, 24601, 24601, '2020-01-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24620, 24610, 24601, coords2position(52.22968, 21.01223), '2020-03-01 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24621, 24610, 24601, coords2position(50.06143, 19.93658), '2020-03-02 08:00:00+00', 1);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (24622, 24610, 24601, '2020-03-03 08:00:00+00', 2);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (24623, 24610, 24601, '2020-03-04 08:00:00+00', 1);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24624, 24610, 24601, coords2position(51.10788, 17.03854), '2020-03-05 08:00:00+00', 0);

SET CONSTRAINTS ALL IMMEDIATE;
ALTER TABLE geokrety.gk_moves DISABLE TRIGGER ALL;

UPDATE gk_moves
SET km_distance = NULL,
	previous_move_id = NULL,
	previous_position_id = NULL
WHERE id IN (24620, 24621, 24622, 24623, 24624);

ALTER TABLE geokrety.gk_moves ENABLE TRIGGER ALL;
SET CONSTRAINTS ALL IMMEDIATE;

REFRESH MATERIALIZED VIEW stats.mv_backfill_working_set;

SELECT is(stats.fn_backfill_km_distance(), 4::bigint, 'km-distance backfill repairs split predecessor state on the first pass');
SELECT is((SELECT km_distance FROM gk_moves WHERE id = 24620), NULL::numeric, 'first qualifying move keeps km_distance NULL');
SELECT is((SELECT km_distance FROM gk_moves WHERE id = 24622), NULL::numeric, 'non-qualifying move keeps km_distance NULL');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 24622), 24621::bigint, 'non-qualifying move inherits the last qualifying predecessor');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 24622), 24621::bigint, 'non-qualifying move inherits the last positioned predecessor');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 24623), 24621::bigint, 'qualifying move without coordinates stays in the previous_move_id chain');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 24623), 24621::bigint, 'qualifying move without coordinates still tracks the previous positioned move');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 24624), 24623::bigint, 'later positioned move links to the last event-chain move');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 24624), 24621::bigint, 'later positioned move links to the last positioned move');
SELECT is((SELECT km_distance FROM gk_moves WHERE id = 24621), (SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::numeric(8,3) FROM gk_moves a JOIN gk_moves b ON b.id = 24621 WHERE a.id = 24620), 'km-distance backfill computes the canonical rounded distance for directly chained positioned moves');
SELECT is((SELECT km_distance FROM gk_moves WHERE id = 24624), (SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::numeric(8,3) FROM gk_moves a JOIN gk_moves b ON b.id = 24624 WHERE a.id = 24621), 'km-distance backfill uses previous_position_id instead of previous_move_id');
SELECT is(stats.fn_backfill_km_distance(), 0::bigint, 're-running km-distance backfill is idempotent');
SELECT ok((SELECT metadata ?& ARRAY['period', 'batch_size', 'rows_updated'] FROM stats.job_log WHERE job_name = 'fn_backfill_km_distance' ORDER BY id DESC LIMIT 1), 'km-distance backfill writes canonical metadata keys');

SELECT * FROM finish();
ROLLBACK;
