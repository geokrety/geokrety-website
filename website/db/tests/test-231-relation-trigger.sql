BEGIN;
SELECT plan(16);

INSERT INTO gk_users (id, username, registration_ip) VALUES (23101, 'relation-user-a', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (23102, 'relation-user-b', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (23103, 'relation-user-c', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (23101, 'Relation GK 1', 0, '2020-09-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (23102, 'Relation GK 2', 0, '2020-09-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (23103, 'Relation GK 3', 0, '2020-09-01 00:00:00+00');

SELECT has_function('geokrety', 'fn_gk_moves_relations', ARRAY[]::text[], 'fn_gk_moves_relations function exists');
SELECT function_returns('geokrety', 'fn_gk_moves_relations', ARRAY[]::text[], 'trigger', 'fn_gk_moves_relations returns trigger');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_after_relations', 'tr_gk_moves_after_relations trigger exists');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23110, 23101, 23101, coords2position(52.22968, 21.01223), '2020-09-01 10:00:00+00', 0);

SELECT is((SELECT interaction_count FROM stats.gk_related_users WHERE geokrety_id = 23101 AND user_id = 23101), 1::bigint, 'first qualifying move creates the GK-user relation');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23111, 23101, 23102, coords2position(52.52000, 13.40500), '2020-09-01 11:00:00+00', 1);

SELECT is((SELECT interaction_count FROM stats.gk_related_users WHERE geokrety_id = 23101 AND user_id = 23102), 1::bigint, 'second user on the same GK creates its GK-user relation');
SELECT is((SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 23101 AND related_user_id = 23102), 1::bigint, 'user_related_users stores A to B');
SELECT is((SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 23102 AND related_user_id = 23101), 1::bigint, 'user_related_users stores B to A');
SELECT is(
  (SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 23101 AND related_user_id = 23102),
  (SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 23102 AND related_user_id = 23101),
  'symmetric user pair counts stay equal'
);

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23112, 23101, 23101, coords2position(50.07550, 14.43780), '2020-09-01 12:00:00+00', 5);

SELECT is((SELECT interaction_count FROM stats.gk_related_users WHERE geokrety_id = 23101 AND user_id = 23101), 2::bigint, 'repeat qualifying moves increment interaction_count');
SELECT is((SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 23101 AND related_user_id = 23102), 1::bigint, 'shared_geokrety_count stays distinct-GK based');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (23113, 23102, 23103, coords2position(48.20820, 16.37380), '2020-09-02 10:00:00+00', 2);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_related_users WHERE geokrety_id = 23102 AND user_id = 23103), 0::bigint, 'comment moves do not create relations');

INSERT INTO gk_moves (id, geokret, username, position, moved_on_datetime, move_type)
VALUES (23114, 23103, 'relation-anon', coords2position(48.85660, 2.35220), '2020-09-02 11:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_related_users WHERE geokrety_id = 23103), 0::bigint, 'anonymous moves do not create relations');

UPDATE gk_moves
SET move_type = 2
WHERE id = 23111;

SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_related_users WHERE geokrety_id = 23101 AND user_id = 23102), 0::bigint, 'update to a non-qualifying move removes the GK-user relation');
SELECT is((SELECT COUNT(*)::bigint FROM stats.user_related_users WHERE user_id = 23101 AND related_user_id = 23102), 0::bigint, 'update to a non-qualifying move removes A to B');
SELECT is((SELECT COUNT(*)::bigint FROM stats.user_related_users WHERE user_id = 23102 AND related_user_id = 23101), 0::bigint, 'update to a non-qualifying move removes B to A');

DELETE FROM gk_moves WHERE id = 23112;

SELECT is((SELECT interaction_count FROM stats.gk_related_users WHERE geokrety_id = 23101 AND user_id = 23101), 1::bigint, 'delete reconciles remaining interaction_count exactly');

SELECT * FROM finish();
ROLLBACK;
