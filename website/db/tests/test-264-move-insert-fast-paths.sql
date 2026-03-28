BEGIN;

SELECT plan(18);

ALTER TABLE geokrety.gk_moves DISABLE TRIGGER after_99_notify_amqp_moves;

INSERT INTO gk_users (id, username, registration_ip) VALUES (926400001, 'fast-path-owner', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (926400002, 'fast-path-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (926400010, 'Fast path GK', 0, 926400001, 926400001, '2020-01-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, country, waypoint, position, moved_on_datetime, move_type)
VALUES (926400020, 926400010, 926400002, 'pl', 'GC264A', coords2position(52.22968, 21.01223), '2020-01-01 08:00:00+00', 0);

SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 926400020), NULL::bigint, 'first move keeps previous_move_id null');
SELECT is((SELECT count(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 926400010), 1::bigint, 'first qualifying move creates one country-history row');
SELECT is((SELECT country_code FROM stats.gk_country_history WHERE geokrety_id = 926400010 AND departed_at IS NULL), 'PL'::char(2), 'first country-history row uses normalized country code');
SELECT is((SELECT visit_count FROM stats.gk_cache_visits gv JOIN stats.waypoints w ON w.id = gv.waypoint_id WHERE gv.gk_id = 926400010 AND w.waypoint_code = 'GC264A'), 1::bigint, 'first waypoint visit creates one GeoKret cache visit');
SELECT is((SELECT visit_count FROM stats.user_cache_visits uv JOIN stats.waypoints w ON w.id = uv.waypoint_id WHERE uv.user_id = 926400002 AND w.waypoint_code = 'GC264A'), 1::bigint, 'first waypoint visit creates one user cache visit');

INSERT INTO gk_moves (id, geokret, author, country, waypoint, position, moved_on_datetime, move_type)
VALUES (926400021, 926400010, 926400002, 'pl', 'GC264A', coords2position(50.06143, 19.93658), '2020-01-02 09:00:00+00', 5);

SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 926400021), 926400020::bigint, 'append-only insert keeps previous_move_id correct without a chain rebuild');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 926400021), 926400020::bigint, 'append-only insert keeps previous_position_id correct without a chain rebuild');
SELECT is((SELECT count(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 926400010), 1::bigint, 'same-country append-only insert does not add a new country-history row');
SELECT is((SELECT visit_count FROM stats.gk_cache_visits gv JOIN stats.waypoints w ON w.id = gv.waypoint_id WHERE gv.gk_id = 926400010 AND w.waypoint_code = 'GC264A'), 2::bigint, 'append-only insert increments the GeoKret waypoint visit count');
SELECT is((SELECT last_visited_at FROM stats.gk_cache_visits gv JOIN stats.waypoints w ON w.id = gv.waypoint_id WHERE gv.gk_id = 926400010 AND w.waypoint_code = 'GC264A'), '2020-01-02 09:00:00+00'::timestamptz, 'append-only insert updates the GeoKret waypoint last_visited_at');

INSERT INTO gk_moves (id, geokret, author, country, waypoint, position, moved_on_datetime, move_type)
VALUES (926400022, 926400010, 926400002, 'de', 'GC264B', coords2position(52.52000, 13.40500), '2020-01-03 10:00:00+00', 5);

SELECT is((SELECT count(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 926400010), 2::bigint, 'country changes still append a new country-history row');
SELECT is((SELECT departed_at FROM stats.gk_country_history WHERE geokrety_id = 926400010 AND country_code = 'PL' AND move_id = 926400020), '2020-01-03 10:00:00+00'::timestamptz, 'country changes close the previous active country interval');
SELECT is((SELECT country_code FROM stats.gk_country_history WHERE geokrety_id = 926400010 AND departed_at IS NULL), 'DE'::char(2), 'country changes open the new active country interval');
SELECT is((SELECT visit_count FROM stats.gk_cache_visits gv JOIN stats.waypoints w ON w.id = gv.waypoint_id WHERE gv.gk_id = 926400010 AND w.waypoint_code = 'GC264B'), 1::bigint, 'new waypoint visits still create a new GeoKret cache visit row');

INSERT INTO gk_moves (id, geokret, author, country, waypoint, position, moved_on_datetime, move_type)
VALUES (926400019, 926400010, 926400002, 'cz', 'GC264C', coords2position(50.07550, 14.43780), '2020-01-01 12:00:00+00', 5);

SELECT is((SELECT previous_move_id FROM gk_moves WHERE id = 926400021), 926400019::bigint, 'backdated inserts still trigger a full previous-move refresh for later rows');
SELECT is((SELECT previous_position_id FROM gk_moves WHERE id = 926400021), 926400019::bigint, 'backdated inserts still trigger a full previous-position refresh for later rows');
SELECT is((SELECT count(*)::bigint FROM stats.gk_country_history WHERE geokrety_id = 926400010), 4::bigint, 'backdated inserts still rebuild the complete country history when needed');
SELECT is((SELECT departed_at FROM stats.gk_country_history WHERE geokrety_id = 926400010 AND country_code = 'CZ'), '2020-01-02 09:00:00+00'::timestamptz, 'backdated inserts still rebuild later country transitions correctly');

SELECT * FROM finish();
ROLLBACK;
