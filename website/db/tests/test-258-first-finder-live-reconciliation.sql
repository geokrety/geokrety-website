BEGIN;
SELECT plan(19);

SELECT has_function('stats', 'fn_reconcile_first_finder_event', ARRAY['bigint'], 'stats.fn_reconcile_first_finder_event exists');
SELECT function_returns('stats', 'fn_reconcile_first_finder_event', ARRAY['bigint'], 'boolean', 'stats.fn_reconcile_first_finder_event returns boolean');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_after_first_finder', 'tr_gk_moves_after_first_finder trigger exists');
SELECT has_trigger('geokrety', 'gk_geokrety', 'tr_gk_geokrety_after_first_finder', 'tr_gk_geokrety_after_first_finder trigger exists');

INSERT INTO gk_users (id, username, registration_ip)
VALUES
  (25801, 'first-finder-hardening-owner', '127.0.0.1'),
  (25802, 'first-finder-hardening-user-1', '127.0.0.1'),
  (25803, 'first-finder-hardening-user-2', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES
  (25810, 'First Finder Hardening GK', 0, 25801, 25801, '2020-01-01 00:00:00+00'),
  (25811, 'First Finder Owner Drift GK', 0, 25801, 25801, '2020-01-01 00:00:00+00'),
  (25812, 'First Finder Move Source GK', 0, 25801, 25801, '2020-01-01 00:00:00+00'),
  (25813, 'First Finder Move Destination GK', 0, 25801, 25801, '2020-01-01 00:00:00+00'),
  (25814, 'First Finder Boundary GK', 0, 25801, 25801, '2020-01-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (25820, 25810, 25802, coords2position(52.22968, 21.01223), '2020-01-01 05:00:00+00', 0);

SELECT is((SELECT move_id FROM stats.first_finder_events WHERE gk_id = 25810), 25820::bigint, 'initial qualifying insert creates the canonical first-finder row');
SELECT is((SELECT event_value FROM stats.gk_milestone_events WHERE gk_id = 25810 AND event_type = 'first_find'), 5::numeric, 'initial first_find milestone stores whole hours since creation');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (25821, 25810, 25803, coords2position(52.52000, 13.40500), '2020-01-01 02:00:00+00', 5);

SELECT is((SELECT move_id FROM stats.first_finder_events WHERE gk_id = 25810), 25821::bigint, 'later backdated insert replaces the canonical first-finder row');
SELECT is((SELECT additional_data->>'move_id' FROM stats.gk_milestone_events WHERE gk_id = 25810 AND event_type = 'first_find'), '25821', 'first_find milestone is updated to the canonical triggering move');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (25822, 25810, 25802, coords2position(48.85660, 2.35220), '2020-01-01 04:00:00+00', 3);

DELETE FROM gk_moves WHERE id = 25822;

SELECT is((SELECT move_id FROM stats.first_finder_events WHERE gk_id = 25810), 25821::bigint, 'deleting a losing candidate leaves the canonical first-finder row unchanged');

UPDATE gk_moves
SET move_type = 2,
    position = NULL
WHERE id = 25821;

SELECT is((SELECT move_id FROM stats.first_finder_events WHERE gk_id = 25810), 25820::bigint, 'disqualifying the winning move falls back to the next canonical candidate');
SELECT is((SELECT additional_data->>'move_id' FROM stats.gk_milestone_events WHERE gk_id = 25810 AND event_type = 'first_find'), '25820', 'milestone fallback stays aligned with the repaired first-finder row');

DELETE FROM gk_moves WHERE id = 25820;

SELECT is((SELECT COUNT(*)::bigint FROM stats.first_finder_events WHERE gk_id = 25810), 0::bigint, 'removing the last qualifying candidate deletes the first-finder row');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 25810 AND event_type = 'first_find'), 0::bigint, 'removing the last qualifying candidate also deletes the first_find milestone');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (25830, 25811, 25802, coords2position(48.20820, 16.37380), '2020-01-01 04:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.first_finder_events WHERE gk_id = 25811), 1::bigint, 'owner-drift fixture starts with one canonical first-finder row');

UPDATE gk_geokrety
SET owner = 25802
WHERE id = 25811;

SELECT is((SELECT COUNT(*)::bigint FROM stats.first_finder_events WHERE gk_id = 25811), 0::bigint, 'owner updates reconcile away first-finder rows that become owner interactions');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 25811 AND event_type = 'first_find'), 0::bigint, 'owner updates also remove the linked first_find milestone');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (25840, 25812, 25802, coords2position(41.90280, 12.49640), '2020-01-01 03:00:00+00', 0);

UPDATE gk_moves
SET geokret = 25813
WHERE id = 25840;

SELECT is((SELECT COUNT(*)::bigint FROM stats.first_finder_events WHERE gk_id = 25812), 0::bigint, 'moving a qualifying move away reconciles the old GeoKret id');
SELECT is((SELECT move_id FROM stats.first_finder_events WHERE gk_id = 25813), 25840::bigint, 'moving a qualifying move also reconciles the destination GeoKret id');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (25850, 25814, 25802, coords2position(40.41680, -3.70380), '2020-01-08 00:00:00+00', 0);

SELECT is((SELECT hours_since_creation FROM stats.first_finder_events WHERE gk_id = 25814), 168::smallint, 'the 168-hour upper boundary remains eligible after live hardening');

SELECT * FROM finish();
ROLLBACK;
