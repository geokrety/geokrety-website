BEGIN;
SELECT plan(11);

CREATE TEMP TABLE points_event_baseline AS
SELECT COUNT(*)::bigint AS cnt
FROM notify_queues.geokrety_changes;
CREATE TEMP TABLE points_awarder_event_baseline AS
SELECT COUNT(*)::bigint AS cnt
FROM notify_queues.geokrety_changes
WHERE channel = 'points-awarder'
	AND action = 'gk_move_created';

INSERT INTO gk_users (id, username, registration_ip)
VALUES (23801, 'points-event-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (23810, 'Points event GK 1', 0, 23801, 23801, '2020-10-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (23811, 'Points event GK 2', 0, 23801, 23801, '2020-10-01 00:00:00+00');

SELECT has_function('geokrety', 'fn_gk_moves_emit_points_event', ARRAY[]::text[], 'fn_gk_moves_emit_points_event function exists');
SELECT function_returns('geokrety', 'fn_gk_moves_emit_points_event', ARRAY[]::text[], 'trigger', 'fn_gk_moves_emit_points_event returns trigger');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_emit_points_event', 'tr_gk_moves_emit_points_event trigger exists');
SELECT has_trigger('geokrety', 'gk_moves', 'after_99_notify_amqp_moves', 'legacy gk_moves AMQP trigger still coexists with the points bridge');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23820, 23810, 23801, coords2position(52.22968, 21.01223), '2020-10-02 08:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM notify_queues.geokrety_changes WHERE channel = 'points-awarder' AND action = 'gk_move_created'), (SELECT cnt + 1 FROM points_awarder_event_baseline), 'authenticated move insert emits one points-awarder bridge row');
SELECT is((SELECT channel FROM notify_queues.geokrety_changes ORDER BY id DESC LIMIT 1), 'points-awarder', 'bridge row targets the points-awarder channel');
SELECT is((SELECT action FROM notify_queues.geokrety_changes ORDER BY id DESC LIMIT 1), 'gk_move_created', 'bridge row stores the canonical event type');
SELECT is((SELECT payload FROM notify_queues.geokrety_changes ORDER BY id DESC LIMIT 1), 23820::bigint, 'bridge row payload stores the move id');

INSERT INTO gk_moves (id, geokret, username, position, moved_on_datetime, move_type)
VALUES (23821, 23811, 'anonymous-points-event', coords2position(52.52000, 13.40500), '2020-10-02 09:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM notify_queues.geokrety_changes WHERE channel = 'points-awarder' AND action = 'gk_move_created'), (SELECT cnt + 2 FROM points_awarder_event_baseline), 'anonymous move insert also emits one points-awarder bridge row');
SELECT is((SELECT action FROM notify_queues.geokrety_changes ORDER BY id DESC LIMIT 1), 'gk_move_created', 'anonymous move uses the same canonical event type');
SELECT is((SELECT payload FROM notify_queues.geokrety_changes ORDER BY id DESC LIMIT 1), 23821::bigint, 'anonymous move payload stores the move id');

SELECT * FROM finish();
ROLLBACK;
