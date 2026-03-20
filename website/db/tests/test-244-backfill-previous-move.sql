BEGIN;
SELECT plan(11);

DELETE FROM stats.job_log WHERE job_name = 'fn_backfill_previous_move_id';

SELECT has_function('stats', 'fn_backfill_previous_move_id', ARRAY['tstzrange', 'integer'], 'fn_backfill_previous_move_id function exists');
SELECT function_returns('stats', 'fn_backfill_previous_move_id', ARRAY['tstzrange', 'integer'], 'bigint', 'fn_backfill_previous_move_id returns bigint');

INSERT INTO gk_users (id, username, registration_ip) VALUES (24401, 'backfill-prev-user-1', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (24402, 'backfill-prev-user-2', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (24410, 'Backfill previous GK 1', 0, 24401, 24401, '2020-01-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (24411, 'Backfill previous GK 2', 0, 24402, 24402, '2020-01-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24420, 24410, 24401, coords2position(52.22968, 21.01223), '2020-02-01 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24421, 24410, 24401, coords2position(50.06143, 19.93658), '2020-02-02 09:00:00+00', 3);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (24425, 24410, 24401, '2020-02-02 12:00:00+00', 1);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (24426, 24410, 24401, '2020-02-02 12:30:00+00', 2);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24422, 24410, 24401, coords2position(51.10788, 17.03854), '2020-02-03 10:00:00+00', 5);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24423, 24411, 24402, coords2position(48.85661, 2.35222), '2020-02-01 08:30:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24424, 24411, 24402, coords2position(45.76404, 4.83566), '2020-02-02 09:30:00+00', 3);

SET CONSTRAINTS ALL IMMEDIATE;
ALTER TABLE geokrety.gk_moves DISABLE TRIGGER ALL;

UPDATE gk_moves
SET previous_move_id = NULL
  , previous_position_id = NULL
WHERE id IN (24420, 24421, 24422, 24423, 24424, 24425, 24426);

ALTER TABLE geokrety.gk_moves ENABLE TRIGGER ALL;
SET CONSTRAINTS ALL IMMEDIATE;

REFRESH MATERIALIZED VIEW stats.mv_backfill_working_set;

SELECT is(stats.fn_backfill_previous_move_id(), 5::bigint, 'previous-move backfill repairs both predecessor columns on the first pass, including comment rows');

SELECT results_eq(
  $$
  SELECT id, previous_move_id, previous_position_id
  FROM gk_moves
  WHERE id IN (24420, 24421, 24422, 24423, 24424, 24425, 24426)
  ORDER BY id
  $$,
  $$
  VALUES
    (24420::bigint, NULL::bigint, NULL::bigint),
    (24421::bigint, 24420::bigint, 24420::bigint),
    (24422::bigint, 24425::bigint, 24421::bigint),
    (24423::bigint, NULL::bigint, NULL::bigint),
    (24424::bigint, 24423::bigint, 24423::bigint),
    (24425::bigint, 24421::bigint, 24421::bigint),
    (24426::bigint, 24425::bigint, 24421::bigint)
  $$,
  'backfill reconstructs both event and positioned predecessor links per GeoKret only, including comment inheritance'
);
SELECT is((SELECT km_distance FROM gk_moves WHERE id = 24425), NULL::numeric, 'previous-move backfill clears stale km_distance on non-position qualifying rows');
SELECT is(
  (SELECT km_distance FROM gk_moves WHERE id = 24422),
  (
    SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::NUMERIC(8,3)
    FROM gk_moves a
    CROSS JOIN gk_moves b
    WHERE a.id = 24421 AND b.id = 24422
  ),
  'previous-move backfill recomputes km_distance from the positioned predecessor when repairing mismatched rows'
);

SELECT is(stats.fn_backfill_previous_move_id(), 0::bigint, 're-running previous-move backfill is idempotent');

SET CONSTRAINTS ALL IMMEDIATE;
ALTER TABLE geokrety.gk_moves DISABLE TRIGGER ALL;

UPDATE gk_moves
SET previous_move_id = 24420,
    previous_position_id = 24420,
    km_distance = 0
WHERE id IN (24422, 24425, 24426);

ALTER TABLE geokrety.gk_moves ENABLE TRIGGER ALL;
SET CONSTRAINTS ALL IMMEDIATE;

SELECT is(stats.fn_backfill_previous_move_id(), 3::bigint, 'previous-move backfill repairs wrong non-NULL predecessor state too');
SELECT results_eq(
  $$
  SELECT id, previous_move_id, previous_position_id
  FROM gk_moves
  WHERE id IN (24422, 24425, 24426)
  ORDER BY id
  $$,
  $$
  VALUES
    (24422::bigint, 24425::bigint, 24421::bigint),
    (24425::bigint, 24421::bigint, 24421::bigint),
    (24426::bigint, 24425::bigint, 24421::bigint)
  $$,
  'previous-move backfill repairs mismatched non-NULL predecessor chains'
);

SELECT ok((SELECT status = 'ok' FROM stats.job_log WHERE job_name = 'fn_backfill_previous_move_id' ORDER BY id DESC LIMIT 1), 'job_log status is ok');
SELECT ok((SELECT metadata ?& ARRAY['period', 'batch_size', 'rows_updated'] FROM stats.job_log WHERE job_name = 'fn_backfill_previous_move_id' ORDER BY id DESC LIMIT 1), 'job_log metadata carries the canonical keys');

SELECT * FROM finish();
ROLLBACK;
