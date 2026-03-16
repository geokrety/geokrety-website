BEGIN;
SELECT plan(16);

ALTER TABLE geokrety.gk_moves DISABLE TRIGGER after_99_notify_amqp_moves;

SELECT matches(
  (SELECT pg_get_triggerdef(oid) FROM pg_trigger WHERE tgname = 'tr_gk_moves_after_sharded_counters' AND tgrelid = 'geokrety.gk_moves'::regclass),
  'AFTER INSERT OR DELETE OR UPDATE OF id, move_type ON geokrety\.gk_moves FOR EACH ROW EXECUTE FUNCTION fn_gk_moves_sharded_counter\(\)',
  'sharded counter trigger watches only id and move_type on UPDATE'
);
SELECT matches(
  (SELECT pg_get_triggerdef(oid) FROM pg_trigger WHERE tgname = 'tr_gk_moves_after_country_rollups' AND tgrelid = 'geokrety.gk_moves'::regclass),
  'AFTER INSERT OR DELETE OR UPDATE OF geokret, author, country, moved_on_datetime, move_type ON geokrety\.gk_moves FOR EACH ROW EXECUTE FUNCTION fn_gk_moves_country_rollups\(\)',
  'country rollups trigger ignores derived-column-only updates'
);
SELECT matches(
  (SELECT pg_get_triggerdef(oid) FROM pg_trigger WHERE tgname = 'tr_gk_moves_after_country_history' AND tgrelid = 'geokrety.gk_moves'::regclass),
  'AFTER INSERT OR DELETE OR UPDATE OF geokret, country, moved_on_datetime, move_type ON geokrety\.gk_moves FOR EACH ROW EXECUTE FUNCTION fn_gk_moves_country_history\(\)',
  'country history trigger watches only interval-shaping columns'
);
SELECT matches(
  (SELECT pg_get_triggerdef(oid) FROM pg_trigger WHERE tgname = 'tr_gk_moves_after_waypoint_visits' AND tgrelid = 'geokrety.gk_moves'::regclass),
  'AFTER INSERT OR DELETE OR UPDATE OF geokret, author, waypoint, moved_on_datetime, move_type, "position", country ON geokrety\.gk_moves FOR EACH ROW EXECUTE FUNCTION fn_gk_moves_waypoint_cache\(\)',
  'waypoint cache trigger watches only waypoint metadata columns'
);
SELECT matches(
  (SELECT pg_get_triggerdef(oid) FROM pg_trigger WHERE tgname = 'tr_gk_moves_after_relations' AND tgrelid = 'geokrety.gk_moves'::regclass),
  'AFTER INSERT OR DELETE OR UPDATE OF geokret, author, moved_on_datetime, move_type ON geokrety\.gk_moves FOR EACH ROW EXECUTE FUNCTION fn_gk_moves_relations\(\)',
  'relations trigger watches only relation-shaping columns'
);
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_after_daily_activity_insert', 'daily activity insert trigger exists');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_after_daily_activity', 'daily activity update trigger exists');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_after_daily_activity_delete', 'daily activity delete trigger exists');
SELECT matches(
  (SELECT pg_get_triggerdef(oid) FROM pg_trigger WHERE tgname = 'tr_gk_moves_after_daily_activity' AND tgrelid = 'geokrety.gk_moves'::regclass),
  'AFTER UPDATE ON geokrety\.gk_moves REFERENCING OLD TABLE AS old_moves NEW TABLE AS new_moves FOR EACH STATEMENT EXECUTE FUNCTION fn_gk_moves_daily_activity\(\)',
  'daily activity update trigger batches affected dates once per UPDATE statement and filters watched columns inside the function'
);

INSERT INTO gk_users (id, username, registration_ip) VALUES (25901, 'trigger-scope-user', '127.0.0.1');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime, owner, holder) VALUES (25901, 'Trigger scope GK', 0, '2020-10-01 00:00:00+00', 25901, 25901);

INSERT INTO gk_moves (id, geokret, author, position, waypoint, moved_on_datetime, move_type)
VALUES (25910, 25901, 25901, coords2position(52.22968, 21.01223), 'GC259A', '2020-10-02 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, waypoint, moved_on_datetime, move_type)
VALUES (25911, 25901, 25901, coords2position(52.52000, 13.40500), 'GC259A', '2020-10-03 08:00:00+00', 0);

CREATE TEMP TABLE trigger_scope_baseline AS
SELECT
  (SELECT interaction_count FROM stats.gk_related_users WHERE geokrety_id = 25901 AND user_id = 25901) AS relation_count,
  (SELECT visit_count FROM stats.gk_cache_visits gv JOIN stats.waypoints w ON w.id = gv.waypoint_id WHERE gv.gk_id = 25901 AND w.waypoint_code = 'GC259A') AS waypoint_visits,
  (SELECT moves_count FROM stats.country_daily_stats WHERE stats_date = '2020-10-03' AND country_code = 'DE') AS country_moves_count,
  (SELECT km_contributed FROM stats.daily_activity WHERE activity_date = '2020-10-03') AS daily_km_contributed,
  (SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves') AS total_move_count;

UPDATE gk_moves
SET previous_move_id = NULL,
    previous_position_id = NULL,
    km_distance = 0
WHERE id = 25911;

SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 25911), 25910::bigint, 'manual previous_move_id edits still self-heal to the canonical predecessor');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 25911), 25910::bigint, 'manual previous_position_id edits still self-heal to the canonical positioned predecessor');
SELECT is((SELECT interaction_count FROM stats.gk_related_users WHERE geokrety_id = 25901 AND user_id = 25901), (SELECT relation_count FROM trigger_scope_baseline), 'derived-column repairs leave relation aggregates untouched');
SELECT is((SELECT visit_count FROM stats.gk_cache_visits gv JOIN stats.waypoints w ON w.id = gv.waypoint_id WHERE gv.gk_id = 25901 AND w.waypoint_code = 'GC259A'), (SELECT waypoint_visits FROM trigger_scope_baseline), 'derived-column repairs leave waypoint visit aggregates untouched');
SELECT is((SELECT moves_count FROM stats.country_daily_stats WHERE stats_date = '2020-10-03' AND country_code = 'DE'), (SELECT country_moves_count FROM trigger_scope_baseline), 'derived-column repairs leave country rollups untouched');
SELECT is((SELECT km_contributed FROM stats.daily_activity WHERE activity_date = '2020-10-03'), (SELECT daily_km_contributed FROM trigger_scope_baseline), 'daily activity remains correct after the self-healed repair update');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_moves'), (SELECT total_move_count FROM trigger_scope_baseline), 'derived-column repairs leave sharded move counters untouched');

SELECT * FROM finish();
ROLLBACK;
