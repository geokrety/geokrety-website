-- GeoKrety #3 travel history test: validates previous_move_id and previous_position_id chains
BEGIN;
SELECT plan(19);

-- Test user
INSERT INTO "gk_users" ("id", "username", "registration_ip", "joined_on_datetime")
VALUES (1003, 'test_gk3_user', '127.0.0.1'::inet, NOW());

-- GeoKrety #3
INSERT INTO "gk_geokrety" ("id", "owner", "name", "type", "created_on_datetime", "born_on_datetime")
VALUES (3, 1003, 'Test GK 3', 0, '2007-10-01 00:00:00+00', '2007-10-01 00:00:00+00');

-- Move 1: Type 3 (Found), no position - starting event
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "lat", "lon", "elevation", "country")
VALUES (1, 3, 1003, '2007-10-18 12:00:00+00', 3, NULL, NULL, NULL, 'PL');

-- Move 2: Type 2 (Comment), no position
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "lat", "lon", "elevation", "country")
VALUES (2, 3, 1003, '2007-10-18 12:05:00+00', 2, NULL, NULL, NULL, NULL);

-- Move 417: Type 0 (Move), HAS position - first move with coords
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "lat", "lon", "elevation", "country", "waypoint")
VALUES (417, 3, 1003, '2007-12-15 11:00:00+00', 0, 52.1, 20.1, 100, 'PL', 'OP0023');

-- Move 10394: Type 2 (Comment), no position, 11 months later
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "lat", "lon", "elevation", "country")
VALUES (10394, 3, 1003, '2008-11-16 10:00:00+00', 2, NULL, NULL, NULL, NULL);

-- Move 10395: Type 0 (Move), HAS position, 1 minute later
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "lat", "lon", "elevation", "country", "waypoint")
VALUES (10395, 3, 1003, '2008-11-16 10:01:00+00', 0, 51.3, 21.5, 100, NULL, 'OP14CF');

-- Move 11428: Type 0 (Move), HAS position,34 days later
INSERT INTO "gk_moves" ("id", "geokret", "author", "moved_on_datetime", "move_type", "lat", "lon", "elevation", "country", "waypoint")
VALUES (11428, 3, 1003, '2008-12-19 12:33:00+00', 0, 51.2, 21.6, 100, NULL, 'OP14D1');

-- Update gk_geokrety
UPDATE geokrety.gk_geokrety SET last_log = 11428, last_position = 11428 WHERE id = 3;

-- Refresh the materialized view for consistency checks
REFRESH MATERIALIZED VIEW stats.mv_backfill_working_set;

-- ─── TESTS ───────────────────────────────────────────────────────────────────

-- Schema tests
SELECT has_column('geokrety', 'gk_moves', 'previous_move_id', 'previous_move_id column exists');
SELECT has_column('geokrety', 'gk_moves', 'previous_position_id', 'previous_position_id column exists');
SELECT has_column('geokrety', 'gk_moves', 'km_distance', 'km_distance column exists');

-- Move 1: First event, no previous
SELECT is(
    (SELECT previous_move_id FROM geokrety.gk_moves WHERE id = 1 AND geokret = 3),
    NULL::bigint,
    'Move 1: no previous event'
);
SELECT is(
    (SELECT previous_position_id FROM geokrety.gk_moves WHERE id = 1 AND geokret = 3),
    NULL::bigint,
    'Move 1: no previous position'
);

-- Move 2: Event chain points to 1
SELECT is(
    (SELECT previous_move_id FROM geokrety.gk_moves WHERE id = 2 AND geokret = 3),
    1::bigint,
    'Move 2: previous_move_id = 1'
);
SELECT is(
    (SELECT previous_position_id FROM geokrety.gk_moves WHERE id = 2 AND geokret = 3),
    NULL::bigint,
    'Move 2: no previous position(no pos moves before it)'
);

-- Move 417: First position, event chain from 2
SELECT is(
    (SELECT previous_move_id FROM geokrety.gk_moves WHERE id = 417 AND geokret = 3),
    1::bigint,
    'Move 417: previous_move_id = 1 (the last earlier qualifying move, not the intervening comment)'
);
SELECT is(
    (SELECT previous_position_id FROM geokrety.gk_moves WHERE id = 417 AND geokret = 3),
    NULL::bigint,
    'Move 417: no previous position (first with pos)'
);

-- Move 10394: Gap, no position, event chain from 417
SELECT is(
    (SELECT previous_move_id FROM geokrety.gk_moves WHERE id = 10394 AND geokret = 3),
    417::bigint,
    'Move 10394: previous_move_id = 417'
);
SELECT is(
    (SELECT previous_position_id FROM geokrety.gk_moves WHERE id = 10394 AND geokret = 3),
    417::bigint,
    'Move 10394: previous_position_id = 417 (comment rows inherit the last positioned predecessor)'
);

-- Move 10395: KEY TEST - should NOT skip 10394!
SELECT is(
    (SELECT previous_move_id FROM geokrety.gk_moves WHERE id = 10395 AND geokret = 3),
    417::bigint,
    'Move 10395: previous_move_id = 417 (comments keep inherited links but do not become qualifying predecessors)'
);
SELECT is(
    (SELECT previous_position_id FROM geokrety.gk_moves WHERE id = 10395 AND geokret = 3),
    417::bigint,
    'Move 10395: previous_position_id = 417 (last move WITH position)'
);
SELECT is(
    (SELECT km_distance FROM geokrety.gk_moves WHERE id = 10395 AND geokret = 3),
    (
        SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::NUMERIC(8,3)
        FROM geokrety.gk_moves a
        CROSS JOIN geokrety.gk_moves b
        WHERE a.id = 417 AND b.id = 10395
    ),
    'Move 10395: km_distance is calculated from previous_position_id = 417'
);

-- Move 11428: Both should be 10395
SELECT is(
    (SELECT previous_move_id FROM geokrety.gk_moves WHERE id = 11428 AND geokret = 3),
    10395::bigint,
    'Move 11428: previous_move_id = 10395'
);
SELECT is(
    (SELECT previous_position_id FROM geokrety.gk_moves WHERE id = 11428 AND geokret = 3),
    10395::bigint,
    'Move 11428: previous_position_id = 10395'
);
SELECT is(
    (SELECT km_distance FROM geokrety.gk_moves WHERE id = 11428 AND geokret = 3),
    (
        SELECT (public.ST_Distance(a.position, b.position) / 1000.0)::NUMERIC(8,3)
        FROM geokrety.gk_moves a
        CROSS JOIN geokrety.gk_moves b
        WHERE a.id = 10395 AND b.id = 11428
    ),
    'Move 11428: km_distance is calculated from previous_position_id = 10395'
);

-- MV consistency check
SELECT is(
    (SELECT previous_move_id FROM stats.mv_backfill_working_set WHERE id = 10395 AND geokret = 3),
    (SELECT previous_move_id FROM geokrety.gk_moves WHERE id = 10395 AND geokret = 3),
    'Move 10395: MV and table agree on previous_move_id'
);
SELECT is(
    (SELECT previous_position_id FROM stats.mv_backfill_working_set WHERE id = 10395 AND geokret = 3),
    (SELECT previous_position_id FROM geokrety.gk_moves WHERE id = 10395 AND geokret = 3),
    'Move 10395: MV and table agree on previous_position_id'
);

SELECT * FROM finish();
ROLLBACK;
