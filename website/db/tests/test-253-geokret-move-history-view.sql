BEGIN;
SELECT plan(5);

SELECT has_view('geokrety', 'vw_geokret_move_history', 'geokrety.vw_geokret_move_history exists');
SELECT ok(
  (
    SELECT COUNT(*) = 2
    FROM information_schema.columns
    WHERE table_schema = 'geokrety'
      AND table_name = 'vw_geokret_move_history'
      AND column_name IN ('previous_position_id', 'move_type_label')
  ),
  'vw_geokret_move_history exposes the expected derived columns'
);

INSERT INTO gk_users (id, username, registration_ip) VALUES (25301, 'move-history-user', '127.0.0.1');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (25310, 'Move History GK', 0, 25301, 25301, '2020-05-01 00:00:00+00');
INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES (25320, 25310, 25301, 'GC253A', 'pl', coords2position(52.22968, 21.01223), '2020-05-02 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, waypoint, country, position, moved_on_datetime, move_type)
VALUES (25321, 25310, 25301, NULL, 'pl', NULL, '2020-05-03 08:00:00+00', 2);

SELECT is(
  (SELECT move_type_label FROM geokrety.vw_geokret_move_history WHERE move_id = 25321),
  'comment',
  'vw_geokret_move_history maps move_type to a human-readable label'
);
SELECT is(
  (SELECT position FROM geokrety.vw_geokret_move_history WHERE move_id = 25321),
  '-',
  'vw_geokret_move_history renders NULL positions as a dash'
);
SELECT ok(
  (
    SELECT position <> '-'
    FROM geokrety.vw_geokret_move_history
    WHERE move_id = 25320
  ),
  'vw_geokret_move_history renders positioned moves as text coordinates'
);

SELECT * FROM finish();
ROLLBACK;
