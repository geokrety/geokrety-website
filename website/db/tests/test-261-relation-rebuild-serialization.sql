BEGIN;
SELECT plan(14);

ALTER TABLE geokrety.gk_moves DISABLE TRIGGER after_99_notify_amqp_moves;

DELETE FROM stats.user_related_users;
DELETE FROM stats.gk_related_users;
DELETE FROM stats.job_log
WHERE job_name = 'fn_snapshot_relations';

DELETE FROM gk_moves WHERE id BETWEEN 926120 AND 926123;
DELETE FROM gk_geokrety WHERE id BETWEEN 926111 AND 926112;
DELETE FROM gk_users WHERE id BETWEEN 926101 AND 926103;

SELECT matches(
  regexp_replace(pg_get_functiondef('stats.fn_snapshot_relations(daterange)'::regprocedure), '\s+', ' ', 'g'),
  'pg_advisory_xact_lock\(20260321, 1\)',
  'fn_snapshot_relations takes the shared advisory lock before rebuilding relation tables'
);
SELECT matches(
  regexp_replace(pg_get_functiondef('geokrety.fn_gk_moves_relations()'::regprocedure), '\s+', ' ', 'g'),
  'pg_advisory_xact_lock\(20260321, 1\)',
  'fn_gk_moves_relations takes the same shared advisory lock as the snapshot rebuild'
);
SELECT matches(
  regexp_replace(pg_get_functiondef('stats.fn_snapshot_relations(daterange)'::regprocedure), '\s+', ' ', 'g'),
  'DELETE FROM stats\.user_related_users uru WHERE EXISTS \( SELECT 1 FROM tmp_snapshot_impacted_users iu WHERE iu\.user_id = uru\.user_id OR iu\.user_id = uru\.related_user_id \)',
  'scoped fn_snapshot_relations deletes impacted relation rows regardless of pair direction'
);
SELECT matches(
  regexp_replace(pg_get_functiondef('stats.fn_snapshot_relations(daterange)'::regprocedure), '\s+', ' ', 'g'),
  'WHERE left_side\.user_id IN \( SELECT iu\.user_id FROM tmp_snapshot_impacted_users iu \) OR right_side\.user_id IN \( SELECT iu\.user_id FROM tmp_snapshot_impacted_users iu \)',
  'scoped fn_snapshot_relations rebuilds relation rows for either impacted side of the pair'
);

INSERT INTO gk_users (id, username, registration_ip) VALUES (926101, 'relation-lock-user-a', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (926102, 'relation-lock-user-b', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (926103, 'relation-lock-user-c', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (926111, 'Relation lock touched GK', 0, 926101, 926101, '2020-01-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (926112, 'Relation lock untouched GK', 0, 926102, 926102, '2020-01-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (926120, 926111, 926101, coords2position(52.22968, 21.01223), '2020-02-10 10:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (926121, 926111, 926102, coords2position(52.52000, 13.40500), '2020-02-11 10:00:00+00', 5);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (926122, 926112, 926102, coords2position(50.07550, 14.43780), '2020-01-10 10:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (926123, 926112, 926103, coords2position(48.85660, 2.35220), '2020-01-11 10:00:00+00', 5);

SELECT lives_ok($$SELECT stats.fn_snapshot_relations(NULL);$$, 'baseline full relation snapshot executes successfully');
SELECT is((SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 926101 AND related_user_id = 926102), 1::bigint, 'baseline full snapshot creates A to B for the touched GK');
SELECT is((SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 926102 AND related_user_id = 926101), 1::bigint, 'baseline full snapshot creates B to A for the touched GK');
SELECT is((SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 926102 AND related_user_id = 926103), 1::bigint, 'baseline full snapshot preserves B to C for the untouched GK');
SELECT is((SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 926103 AND related_user_id = 926102), 1::bigint, 'baseline full snapshot preserves C to B for the untouched GK');

SET CONSTRAINTS ALL IMMEDIATE;
ALTER TABLE geokrety.gk_moves DISABLE TRIGGER ALL;

DELETE FROM gk_moves
WHERE id = 926121;

ALTER TABLE geokrety.gk_moves ENABLE TRIGGER ALL;
SET CONSTRAINTS ALL IMMEDIATE;

SELECT lives_ok(
  $$SELECT stats.fn_snapshot_relations(daterange('2020-02-01', '2020-03-01', '[)'));$$,
  'scoped relation snapshot executes successfully after a touched user loses the last qualifying move on that GK'
);
SELECT is((SELECT COUNT(*)::bigint FROM stats.user_related_users WHERE user_id = 926101 AND related_user_id = 926102), 0::bigint, 'scoped snapshot removes A to B when the touched relation disappears');
SELECT is((SELECT COUNT(*)::bigint FROM stats.user_related_users WHERE user_id = 926102 AND related_user_id = 926101), 0::bigint, 'scoped snapshot removes B to A even when the stale row survives only as the reverse direction');
SELECT is((SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 926102 AND related_user_id = 926103), 1::bigint, 'scoped snapshot leaves untouched B to C relations intact');
SELECT is((SELECT shared_geokrety_count FROM stats.user_related_users WHERE user_id = 926103 AND related_user_id = 926102), 1::bigint, 'scoped snapshot leaves untouched C to B relations intact');

SELECT * FROM finish();
ROLLBACK;
