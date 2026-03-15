BEGIN;
SELECT plan(14);

SELECT has_function('geokrety', 'fn_gk_moves_milestones', ARRAY[]::text[], 'fn_gk_moves_milestones function exists');
SELECT function_returns('geokrety', 'fn_gk_moves_milestones', ARRAY[]::text[], 'trigger', 'fn_gk_moves_milestones returns trigger');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_after_milestones', 'tr_gk_moves_after_milestones trigger exists');

INSERT INTO gk_users (id, username, registration_ip)
VALUES (23901, 'milestone-owner', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (23910, 'Milestone KM GK', 0, 23901, 23901, '2020-11-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23920, 23910, 23901, coords2position(52.22968, 21.01223), '2020-11-02 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23921, 23910, 23901, coords2position(52.52000, 13.40500), '2020-11-02 09:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 23910 AND event_type = 'km_100'), 1::bigint, 'crossing 100 km inserts one km_100 milestone');
SELECT is((SELECT event_value FROM stats.gk_milestone_events WHERE gk_id = 23910 AND event_type = 'km_100'), 100::numeric, 'km_100 milestone stores the threshold value');
SELECT is((SELECT additional_data->>'move_id' FROM stats.gk_milestone_events WHERE gk_id = 23910 AND event_type = 'km_100'), '23921', 'km milestone stores the triggering move id in additional_data');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 23910 AND event_type = 'km_1000'), 0::bigint, 'km_1000 is absent before the higher threshold is crossed');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23922, 23910, 23901, coords2position(48.85660, 2.35220), '2020-11-02 10:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 23910 AND event_type = 'km_1000'), 1::bigint, 'crossing 1000 km inserts one km_1000 milestone');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23923, 23910, 23901, coords2position(50.07550, 14.43780), '2020-11-02 11:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 23910 AND event_type = 'km_100'), 1::bigint, 'later qualifying moves do not duplicate km_100');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 23910 AND event_type = 'km_10000'), 0::bigint, 'km_10000 remains absent below that threshold');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (23911, 'Milestone users GK', 0, 23901, 23901, '2020-11-03 00:00:00+00');

INSERT INTO gk_users (id, username, registration_ip) VALUES (23930, 'milestone-user-0', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (23931, 'milestone-user-1', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (23932, 'milestone-user-2', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (23933, 'milestone-user-3', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (23934, 'milestone-user-4', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (23935, 'milestone-user-5', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (23936, 'milestone-user-6', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (23937, 'milestone-user-7', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (23938, 'milestone-user-8', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (23939, 'milestone-user-9', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (23940, 'milestone-user-10', '127.0.0.1');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type) VALUES (23940, 23911, 23930, coords2position(52.22968, 21.01223), '2020-11-03 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type) VALUES (23941, 23911, 23931, coords2position(52.52000, 13.40500), '2020-11-03 09:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type) VALUES (23942, 23911, 23932, coords2position(48.85660, 2.35220), '2020-11-03 10:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type) VALUES (23943, 23911, 23933, coords2position(50.07550, 14.43780), '2020-11-03 11:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type) VALUES (23944, 23911, 23934, coords2position(48.20820, 16.37380), '2020-11-03 12:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type) VALUES (23945, 23911, 23935, coords2position(41.90280, 12.49640), '2020-11-03 13:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type) VALUES (23946, 23911, 23936, coords2position(40.41680, -3.70380), '2020-11-03 14:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type) VALUES (23947, 23911, 23937, coords2position(47.49790, 19.04020), '2020-11-03 15:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type) VALUES (23948, 23911, 23938, coords2position(46.94800, 7.44740), '2020-11-03 16:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type) VALUES (23949, 23911, 23939, coords2position(45.46420, 9.19000), '2020-11-03 17:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 23911 AND event_type = 'users_10'), 1::bigint, 'the tenth distinct user inserts one users_10 milestone');
SELECT is((SELECT additional_data->>'actor_user_id' FROM stats.gk_milestone_events WHERE gk_id = 23911 AND event_type = 'users_10'), '23939', 'users_10 milestone stores the triggering actor in additional_data');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23950, 23911, 23940, coords2position(51.10790, 17.03850), '2020-11-03 18:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 23911 AND event_type = 'users_10'), 1::bigint, 'later distinct users do not duplicate users_10');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (23912, 'Milestone nonqualifying GK', 0, 23901, 23901, '2020-11-04 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (23960, 23912, 23901, '2020-11-04 08:00:00+00', 2);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_milestone_events WHERE gk_id = 23912), 0::bigint, 'non-qualifying moves do not create milestone rows');

SELECT * FROM finish();
ROLLBACK;
