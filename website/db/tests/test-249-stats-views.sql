BEGIN;
SELECT plan(7);

DELETE FROM stats.user_related_users;
DELETE FROM stats.gk_related_users;
DELETE FROM stats.user_countries;
DELETE FROM stats.country_daily_stats;
DELETE FROM stats.gk_cache_visits;
DELETE FROM stats.first_finder_events;
DELETE FROM stats.gk_milestone_events;
DELETE FROM stats.country_pair_flows;
DELETE FROM stats.hourly_activity;

DELETE FROM stats.waypoints WHERE waypoint_code = 'GC249A';

INSERT INTO stats.waypoints (waypoint_code, source, country, first_seen_at)
VALUES ('GC249A', 'UK', 'PL', '2020-01-01 00:00:00+00');

INSERT INTO stats.user_related_users (user_id, related_user_id, shared_geokrety_count, first_seen_at, last_seen_at)
VALUES (24910, 24911, 3, '2020-01-01 00:00:00+00', '2020-01-03 00:00:00+00');
INSERT INTO stats.gk_related_users (geokrety_id, user_id, interaction_count, first_interaction, last_interaction)
VALUES (24920, 24910, 2, '2020-01-01 00:00:00+00', '2020-01-03 00:00:00+00');
INSERT INTO stats.user_countries (user_id, country_code, move_count, first_visit, last_visit)
VALUES (24910, 'pl', 4, '2020-01-01 00:00:00+00', '2020-01-04 00:00:00+00');
INSERT INTO stats.country_daily_stats (stats_date, country_code, moves_count, km_contributed)
VALUES ('2020-01-01', 'pl', 4, 123.456);
INSERT INTO stats.gk_cache_visits (gk_id, waypoint_id, visit_count, first_visited_at, last_visited_at)
VALUES (24920, (SELECT id FROM stats.waypoints WHERE waypoint_code = 'GC249A'), 5, '2020-01-01 00:00:00+00', '2020-01-05 00:00:00+00');
INSERT INTO stats.first_finder_events (gk_id, finder_user_id, move_id, move_type, hours_since_creation, found_at, gk_created_at)
VALUES (24920, 24910, 24999, 0, 12, '2020-01-02 00:00:00+00', '2020-01-01 12:00:00+00');
INSERT INTO stats.gk_milestone_events (id, gk_id, event_type, event_value, occurred_at)
VALUES (24930, 24920, 'km_100', 100, '2020-01-06 00:00:00+00');
INSERT INTO stats.country_pair_flows (year_month, from_country, to_country, move_count, unique_gk_count)
VALUES ('2020-01-01', 'PL', 'DE', 2, 1);
INSERT INTO stats.hourly_activity (activity_date, hour_utc, move_type, move_count)
VALUES ('2020-01-01', 10, 0, 4);

SELECT ok((SELECT COUNT(*) = 12 FROM pg_views WHERE schemaname = 'stats' AND viewname = ANY (ARRAY['v_uc1_country_activity','v_uc2_user_network','v_uc3_gk_circulation','v_uc4_user_continent_coverage','v_uc6_dormancy','v_uc7_country_flow','v_uc8_seasonal_heatmap','v_uc9_multiplier_velocity','v_uc10_cache_popularity','v_uc13_gk_timeline','v_uc14_first_finder_hof','v_uc15_distance_records'])), 'all 12 canonical Sprint 6 stats views exist');
SELECT ok((SELECT pg_get_viewdef('stats.v_uc2_user_network'::regclass) ILIKE '%stats.user_related_users%' AND pg_get_viewdef('stats.v_uc2_user_network'::regclass) NOT ILIKE '%gk_moves%'), 'v_uc2_user_network reads from precomputed relationship tables');
SELECT results_eq($$SELECT country_code, moves, km FROM stats.v_uc1_country_activity$$, $$VALUES ('pl'::bpchar, 4::bigint, 123.456::numeric)$$, 'v_uc1_country_activity exposes country rollups');
SELECT results_eq($$SELECT waypoint_code, total_gk_visits, distinct_gks FROM stats.v_uc10_cache_popularity WHERE waypoint_code = 'GC249A'$$, $$VALUES ('GC249A'::varchar, 5::bigint, 1::bigint)$$, 'v_uc10_cache_popularity exposes cache popularity rollups');
SELECT results_eq($$SELECT finder_user_id, first_finds FROM stats.v_uc14_first_finder_hof$$, $$VALUES (24910::integer, 1::bigint)$$, 'v_uc14_first_finder_hof exposes first-finder totals');
SELECT ok((SELECT COUNT(*) = 1 FROM stats.v_uc8_seasonal_heatmap WHERE activity_date = '2020-01-01' AND hour_utc = 10 AND move_type = 0 AND move_count = 4), 'v_uc8_seasonal_heatmap exposes hourly activity');
SELECT ok((SELECT to_regclass('stats.v_uc9_multiplier_velocity') IS NOT NULL), 'v_uc9_multiplier_velocity exists even when the points schema is absent');

SELECT * FROM finish();
ROLLBACK;
