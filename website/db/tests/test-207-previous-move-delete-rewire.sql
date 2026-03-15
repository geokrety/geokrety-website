BEGIN;
SELECT plan(9);

INSERT INTO gk_users (id, username, registration_ip) VALUES (601, 'bulk-delete-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (601, 'Bulk delete GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (6010, 601, 601, coords2position(52.22968, 21.01223), '2020-01-02 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (6011, 601, 601, coords2position(52.52000, 13.40500), '2020-01-02 09:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (6012, 601, 601, coords2position(48.20820, 16.37380), '2020-01-02 10:00:00+00', 0);

SELECT lives_ok($$DELETE FROM gk_moves WHERE geokret = 601$$, 'bulk delete succeeds without tuple-modified trigger errors');
SELECT is((SELECT COUNT(*)::int FROM gk_moves WHERE geokret = 601), 0, 'bulk delete removes all matching moves');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (602, 'Multi delete rewire GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (6020, 602, 601, coords2position(52.22968, 21.01223), '2020-01-03 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (6021, 602, 601, coords2position(52.52000, 13.40500), '2020-01-03 09:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (6022, 602, 601, coords2position(50.07550, 14.43780), '2020-01-03 10:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (6023, 602, 601, coords2position(48.20820, 16.37380), '2020-01-03 11:00:00+00', 0);

SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 6023), 6022::bigint, 'initial predecessor chain is populated before multi-row delete');

SELECT lives_ok($$DELETE FROM gk_moves WHERE id IN (6021, 6022)$$, 'multi-row delete succeeds without conflicting trigger updates');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 6023), 6020::bigint, 'multi-row delete rewires the surviving successor from final table state');
SELECT is(
    (SELECT km_distance FROM gk_moves WHERE id = 6023),
    (
        SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::NUMERIC(8,3)
        FROM gk_moves a
        CROSS JOIN gk_moves b
        WHERE a.id = 6020 AND b.id = 6023
    ),
    'multi-row delete recomputes km_distance against the surviving predecessor'
);

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (603, 'Partition delete GK A', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (604, 'Partition delete GK B', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (6030, 603, 601, coords2position(52.22968, 21.01223), '2020-01-04 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (6031, 603, 601, coords2position(52.52000, 13.40500), '2020-01-04 09:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (6040, 604, 601, coords2position(50.07550, 14.43780), '2020-01-04 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (6041, 604, 601, coords2position(48.20820, 16.37380), '2020-01-04 09:00:00+00', 0);

SELECT lives_ok($$DELETE FROM gk_moves WHERE id IN (6030, 6040)$$, 'single delete statement rewires survivors independently per GeoKret');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 6031), NULL::bigint, 'surviving row for first GeoKret becomes a new chain head');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 6041), NULL::bigint, 'surviving row for second GeoKret becomes a new chain head');

SELECT * FROM finish();
ROLLBACK;
