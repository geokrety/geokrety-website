BEGIN;
SELECT plan(57);
-- TODO do we really need to disable the trigger for this test, should we relax the trigger function instead?
-- Disabling the trigger is a bit of a sledgehammer and risks missing issues with trigger recursion,
-- but it allows testing the core logic in isolation without having to insert a bunch of qualifying moves first.
ALTER TABLE geokrety.gk_moves DISABLE TRIGGER before_40_update_missing;

\set move_type_drop 0
\set move_type_grab 1
\set move_type_comment 2
\set move_type_archive 4

INSERT INTO gk_users (id, username, registration_ip) VALUES (501, 'prev-move-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (501, 'First move GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (501, 501, 501, coords2position(52.22968, 21.01223), '2020-01-01 10:00:00+00', :move_type_drop);

SELECT has_function('geokrety', 'fn_set_previous_move_id_and_distance', ARRAY[]::text[], 'fn_set_previous_move_id_and_distance function exists');
SELECT function_returns('geokrety', 'fn_set_previous_move_id_and_distance', ARRAY[]::text[], 'trigger', 'fn_set_previous_move_id_and_distance returns trigger');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_before_prev_move', 'tr_gk_moves_before_prev_move trigger exists');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 501), NULL::bigint, 'first location-bearing move keeps previous_move_id NULL');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (502, 501, 501, coords2position(52.52000, 13.40500), '2020-01-02 10:00:00+00', :move_type_drop);

SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 502), 501::bigint, 'second drop links to the previous location-bearing move');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 502), 501::bigint, 'second drop links to the previous positioned move');
SELECT cmp_ok((SELECT km_distance FROM gk_moves WHERE id = 502), '>', 0::numeric, 'second drop computes a positive km_distance');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime, owner) VALUES (507, 'Archive fallback GK', 0, '2020-01-01 00:00:00+00', 501);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (550, 507, 501, coords2position(52.22968, 21.01223), '2020-01-02 08:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (551, 507, 501, '2020-01-02 09:00:00+00', :move_type_archive);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (552, 507, 501, coords2position(52.52000, 13.40500), '2020-01-02 10:00:00+00', :move_type_drop);
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 552), 550::bigint, 'archive last_position is rejected and fallback finds the prior qualifying move');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 552), 550::bigint, 'archive last_position is rejected for previous_position_id too');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (502, 'Comment GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (5020, 502, 501, coords2position(52.22968, 21.01223), '2020-01-03 09:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (503, 502, 501, '2020-01-03 10:00:00+00', :move_type_comment);
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 503), 5020::bigint, 'comment move inherits the previous qualifying move');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 503), 5020::bigint, 'comment move inherits the previous positioned move');
SELECT is((SELECT km_distance FROM gk_moves WHERE id = 503), NULL::numeric, 'comment move still leaves km_distance NULL');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime, owner) VALUES (503, 'Archive GK', 0, '2020-01-01 00:00:00+00', 501);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (504, 503, 501, '2020-01-04 10:00:00+00', :move_type_archive);
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 504), NULL::bigint, 'archive move leaves previous_move_id NULL');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 504), NULL::bigint, 'archive move leaves previous_position_id NULL');
SELECT is((SELECT km_distance FROM gk_moves WHERE id = 504), NULL::numeric, 'archive move leaves km_distance NULL');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (504, 'Null position GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (505, 504, 501, '2020-01-05 10:00:00+00', :move_type_grab);
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 505), NULL::bigint, 'qualifying move without position leaves previous_move_id NULL');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 505), NULL::bigint, 'qualifying move without position leaves previous_position_id NULL');
SELECT is((SELECT km_distance FROM gk_moves WHERE id = 505), NULL::numeric, 'qualifying move without position leaves km_distance NULL');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (511, 'Split chain GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (580, 511, 501, coords2position(52.22968, 21.01223), '2020-01-06 08:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (581, 511, 501, '2020-01-06 09:00:00+00', :move_type_grab);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (582, 511, 501, coords2position(52.52000, 13.40500), '2020-01-06 10:00:00+00', :move_type_drop);
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 581), 580::bigint, 'non-position qualifying move stays in the previous_move_id chain');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 581), 580::bigint, 'non-position qualifying move inherits the previous positioned move');
SELECT is((SELECT km_distance FROM gk_moves WHERE id = 581), NULL::numeric, 'non-position qualifying move has NULL km_distance');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 582), 581::bigint, 'later positioned move links to the previous event-chain move');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 582), 580::bigint, 'later positioned move links to the previous positioned move');
SELECT is(
    (SELECT km_distance FROM gk_moves WHERE id = 582),
    (
        SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::NUMERIC(8,3)
        FROM gk_moves a
        CROSS JOIN gk_moves b
        WHERE a.id = 580 AND b.id = 582
    ),
    'later positioned move computes km_distance from previous_position_id instead of previous_move_id'
);
SELECT lives_ok($$UPDATE gk_moves SET previous_move_id = 580, previous_position_id = 580, km_distance = NULL WHERE id = 582$$, 'manual edits of derived columns self-heal without missing-helper errors');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 582), 581::bigint, 'manual derived-column edit is repaired back to the canonical event-chain predecessor');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 582), 580::bigint, 'manual derived-column edit is repaired back to the canonical positioned predecessor');
SELECT is(
    (SELECT km_distance FROM gk_moves WHERE id = 582),
    (
        SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::NUMERIC(8,3)
        FROM gk_moves a
        CROSS JOIN gk_moves b
        WHERE a.id = 580 AND b.id = 582
    ),
    'manual derived-column edit is repaired back to the canonical km_distance'
);

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (513, 'Retro non-position insert GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (600, 513, 501, coords2position(52.22968, 21.01223), '2020-01-07 08:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (602, 513, 501, coords2position(48.20820, 16.37380), '2020-04-07 08:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (601, 513, 501, '2020-02-07 08:00:00+00', :move_type_grab);
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 601), 600::bigint, 'retroactive non-position insert links to the preceding event-chain move');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 601), 600::bigint, 'retroactive non-position insert inherits the previous positioned move');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 602), 601::bigint, 'retroactive non-position insert rewires successor previous_move_id');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 602), 600::bigint, 'retroactive non-position insert leaves successor previous_position_id anchored to the prior positioned move');
SELECT is(
    (SELECT km_distance FROM gk_moves WHERE id = 602),
    (
        SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::NUMERIC(8,3)
        FROM gk_moves a
        CROSS JOIN gk_moves b
        WHERE a.id = 600 AND b.id = 602
    ),
    'retroactive non-position insert leaves successor km_distance anchored to the prior positioned move'
);
SELECT lives_ok($$DELETE FROM gk_moves WHERE id = 601$$, 'deleting the retroactive non-position move succeeds without recursive trigger errors');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 602), 600::bigint, 'deleting the non-position middle event rewires successor previous_move_id');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 602), 600::bigint, 'deleting the non-position middle event preserves successor previous_position_id');
SELECT is(
    (SELECT km_distance FROM gk_moves WHERE id = 602),
    (
        SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::NUMERIC(8,3)
        FROM gk_moves a
        CROSS JOIN gk_moves b
        WHERE a.id = 600 AND b.id = 602
    ),
    'deleting the non-position middle event preserves successor km_distance'
);

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (508, 'Tie-break GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (520, 508, 501, coords2position(52.22968, 21.01223), '2020-01-05 12:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (521, 508, 501, coords2position(52.52000, 13.40500), '2020-01-05 12:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (522, 508, 501, coords2position(50.07550, 14.43780), '2020-01-05 12:00:00+00', :move_type_drop);
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 521), 520::bigint, 'same-timestamp predecessor selection uses the lower id for the later inserted peer');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 522), 521::bigint, 'same-timestamp ordering picks the highest qualifying id below the current row');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (505, 'Scale GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (506, 505, 501, coords2position(50.00000, 5.00000), '2020-01-06 10:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (507, 505, 501, coords2position(50.00010, 5.00010), '2020-01-06 11:00:00+00', :move_type_drop);
SELECT is((SELECT scale(km_distance) FROM gk_moves WHERE id = 507), 3, 'km_distance is rounded to three decimal places');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (506, 'Update GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (510, 506, 501, coords2position(52.22968, 21.01223), '2020-01-07 08:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (511, 506, 501, coords2position(52.52000, 13.40500), '2020-01-07 09:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (512, 506, 501, coords2position(48.20820, 16.37380), '2020-01-07 10:00:00+00', :move_type_drop);

UPDATE gk_moves
SET moved_on_datetime = '2020-01-07 08:30:00+00',
    position = coords2position(50.07550, 14.43780)
WHERE id = 512;

SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 512), 510::bigint, 'update recomputes previous_move_id using an earlier move instead of self last_position');
SELECT is(
    (SELECT km_distance FROM gk_moves WHERE id = 512),
    (
        SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::NUMERIC(8,3)
        FROM gk_moves a
        CROSS JOIN gk_moves b
        WHERE a.id = 510 AND b.id = 512
    ),
    'update recomputes km_distance from the new predecessor and position'
);

UPDATE gk_moves
SET move_type = :move_type_comment,
    position = NULL
WHERE id = 512;

SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 512), 510::bigint, 'update to a non-location-bearing state keeps the last qualifying predecessor');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 512), 510::bigint, 'update to a non-location-bearing state keeps the last positioned predecessor');
SELECT is((SELECT km_distance FROM gk_moves WHERE id = 512), NULL::numeric, 'update to a non-location-bearing state still clears km_distance');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (510, 'Update successor GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (570, 510, 501, coords2position(52.22968, 21.01223), '2020-01-07 12:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (571, 510, 501, coords2position(52.52000, 13.40500), '2020-01-07 13:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (572, 510, 501, coords2position(48.20820, 16.37380), '2020-01-07 14:00:00+00', :move_type_drop);

UPDATE gk_moves
SET move_type = :move_type_comment,
    position = NULL
WHERE id = 571;

SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 572), 570::bigint, 'update-side successor rewiring bypasses a row that no longer qualifies');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 572), 570::bigint, 'update-side successor rewiring updates previous_position_id too');
SELECT is(
    (SELECT km_distance FROM gk_moves WHERE id = 572),
    (
        SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::NUMERIC(8,3)
        FROM gk_moves a
        CROSS JOIN gk_moves b
        WHERE a.id = 570 AND b.id = 572
    ),
    'update-side successor rewiring recomputes km_distance against the new predecessor'
);

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (509, 'Delete GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (530, 509, 501, coords2position(52.22968, 21.01223), '2020-01-08 08:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (531, 509, 501, coords2position(52.52000, 13.40500), '2020-01-08 09:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (532, 509, 501, coords2position(48.20820, 16.37380), '2020-01-08 10:00:00+00', :move_type_drop);

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (512, 'Retro insert GK', 0, '2020-01-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (590, 512, 501, coords2position(52.22968, 21.01223), '2020-01-09 08:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (591, 512, 501, coords2position(48.20820, 16.37380), '2020-04-09 08:00:00+00', :move_type_drop);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (589, 512, 501, coords2position(50.07550, 14.43780), '2020-02-09 08:00:00+00', :move_type_drop);
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 589), 590::bigint, 'retroactive positioned insert links to the preceding event-chain move');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 589), 590::bigint, 'retroactive positioned insert links to the preceding positioned move');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 591), 589::bigint, 'retroactive insert recomputes successor previous_move_id');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 591), 589::bigint, 'retroactive insert recomputes successor previous_position_id');
SELECT is(
    (SELECT km_distance FROM gk_moves WHERE id = 591),
    (
        SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::NUMERIC(8,3)
        FROM gk_moves a
        CROSS JOIN gk_moves b
        WHERE a.id = 589 AND b.id = 591
    ),
    'retroactive insert recomputes successor km_distance from the inserted positioned move'
);

SELECT lives_ok($$DELETE FROM gk_moves WHERE id = 531$$, 'delete path executes without recursive trigger errors');
SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 532), 530::bigint, 'deleting a middle move rewires the direct successor to the deleted row predecessor');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 532), 530::bigint, 'deleting a middle move rewires previous_position_id too');
SELECT is(
    (SELECT km_distance FROM gk_moves WHERE id = 532),
    (
        SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::NUMERIC(8,3)
        FROM gk_moves a
        CROSS JOIN gk_moves b
        WHERE a.id = 530 AND b.id = 532
    ),
    'deleting a middle move recomputes the successor km_distance against the new predecessor'
);

SELECT * FROM finish();
ROLLBACK;
