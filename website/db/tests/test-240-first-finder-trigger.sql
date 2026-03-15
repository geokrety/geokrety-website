BEGIN;
SELECT plan(11);

SELECT has_function('stats', 'fn_detect_first_finder', ARRAY['bigint', 'bigint', 'bigint', 'smallint', 'timestamp with time zone'], 'stats.fn_detect_first_finder helper exists');
SELECT has_function('geokrety', 'fn_gk_moves_first_finder', ARRAY[]::text[], 'fn_gk_moves_first_finder function exists');
SELECT function_returns('geokrety', 'fn_gk_moves_first_finder', ARRAY[]::text[], 'trigger', 'fn_gk_moves_first_finder returns trigger');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_after_first_finder', 'tr_gk_moves_after_first_finder trigger exists');

INSERT INTO gk_users (id, username, registration_ip) VALUES (24001, 'first-finder-owner', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (24002, 'first-finder-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (24010, 'First finder eligible GK', 0, 24001, 24001, '2020-12-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24020, 24010, 24002, coords2position(52.22968, 21.01223), '2020-12-02 01:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.first_finder_events WHERE gk_id = 24010), 1::bigint, 'first qualifying non-owner move inserts one row');
SELECT is((SELECT hours_since_creation FROM stats.first_finder_events WHERE gk_id = 24010), 25::smallint, 'hours_since_creation stores whole hours since GeoKret creation');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 24010 AND event_type = 'first_find'), 1::bigint, 'first-finder detection also appends the first_find milestone');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24021, 24010, 24001, coords2position(52.52000, 13.40500), '2020-12-02 02:00:00+00', 0);
SELECT is((SELECT COUNT(*)::bigint FROM stats.first_finder_events WHERE gk_id = 24010), 1::bigint, 'owner moves do not create or duplicate first-finder rows');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (24011, 'First finder anonymous GK', 0, 24001, 24001, '2020-12-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, username, position, moved_on_datetime, move_type)
VALUES (24022, 24011, 'anonymous-first-finder', coords2position(48.85660, 2.35220), '2020-12-02 03:00:00+00', 0);
SELECT is((SELECT COUNT(*)::bigint FROM stats.first_finder_events WHERE gk_id = 24011), 0::bigint, 'anonymous moves do not create first-finder rows');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (24012, 'First finder late GK', 0, 24001, 24001, '2020-12-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24023, 24012, 24002, coords2position(50.07550, 14.43780), '2020-12-08 01:00:01+00', 0);
SELECT is((SELECT COUNT(*)::bigint FROM stats.first_finder_events WHERE gk_id = 24012), 0::bigint, 'moves after the 168-hour cutoff do not create first-finder rows');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24024, 24010, 24002, coords2position(48.20820, 16.37380), '2020-12-02 04:00:00+00', 5);
SELECT is((SELECT COUNT(*)::bigint FROM stats.first_finder_events WHERE gk_id = 24010), 1::bigint, 'repeated qualifying moves do not duplicate first-finder rows');

SELECT * FROM finish();
ROLLBACK;
