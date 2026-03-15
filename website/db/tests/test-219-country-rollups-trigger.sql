BEGIN;
SELECT plan(29);

SELECT has_function('geokrety', 'fn_gk_moves_country_rollups', ARRAY[]::text[], 'fn_gk_moves_country_rollups function exists');
SELECT function_returns('geokrety', 'fn_gk_moves_country_rollups', ARRAY[]::text[], 'trigger', 'fn_gk_moves_country_rollups returns trigger');
SELECT has_trigger('geokrety', 'gk_moves', 'tr_gk_moves_after_country_rollups', 'tr_gk_moves_after_country_rollups trigger exists');

INSERT INTO gk_users (id, username, registration_ip) VALUES (11901, 'country-rollups-user-1', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (11902, 'country-rollups-user-2', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (11903, 'country-rollups-user-3', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (11901, 'Country rollups GK 1', 0, '2020-07-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (11902, 'Country rollups GK 2', 0, '2020-07-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (11903, 'Country rollups GK 3', 0, '2020-07-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (11904, 'Country rollups GK 4', 0, '2020-07-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (11910, 11901, 11901, coords2position(52.22968, 21.01223), '2020-07-01 08:00:00+00', 0);

SELECT is((SELECT moves_count FROM stats.country_daily_stats WHERE stats_date = '2020-07-01' AND country_code = 'pl'), 1::bigint, 'insert with country updates country_daily_stats moves_count');
SELECT is((SELECT drops FROM stats.country_daily_stats WHERE stats_date = '2020-07-01' AND country_code = 'pl'), 1::bigint, 'insert with country updates country_daily_stats by move type');
SELECT is((SELECT move_count FROM stats.gk_countries_visited WHERE geokrety_id = 11901 AND country_code = 'pl'), 1::integer, 'insert updates gk_countries_visited');
SELECT is((SELECT move_count FROM stats.user_countries WHERE user_id = 11901 AND country_code = 'pl'), 1::bigint, 'insert with author updates user_countries');

INSERT INTO gk_moves (id, geokret, username, moved_on_datetime, move_type)
VALUES (11911, 11901, 'country-rollups-anon-null', '2020-07-02 08:00:00+00', 2);

SELECT is((SELECT COUNT(*)::bigint FROM stats.country_daily_stats WHERE stats_date = '2020-07-02'), 0::bigint, 'insert with NULL country creates no country_daily_stats row');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (11912, 11901, 11901, coords2position(50.06143, 19.93658), '2020-07-01 09:00:00+00', 0);

SELECT is((SELECT move_count FROM stats.gk_countries_visited WHERE geokrety_id = 11901 AND country_code = 'pl'), 2::integer, 'second insert in the same country increments gk_countries_visited.move_count');

UPDATE gk_moves
SET position = coords2position(52.52000, 13.40500)
WHERE id = 11912;

SELECT is((SELECT moves_count FROM stats.country_daily_stats WHERE stats_date = '2020-07-01' AND country_code = 'pl'), 1::bigint, 'updating country recomputes the old country/day aggregate');
SELECT is((SELECT moves_count FROM stats.country_daily_stats WHERE stats_date = '2020-07-01' AND country_code = 'de'), 1::bigint, 'updating country recomputes the new country/day aggregate');
SELECT is((SELECT move_count FROM stats.gk_countries_visited WHERE geokrety_id = 11901 AND country_code = 'pl'), 1::integer, 'updating country repairs the old GK-country aggregate');
SELECT is((SELECT move_count FROM stats.gk_countries_visited WHERE geokrety_id = 11901 AND country_code = 'de'), 1::integer, 'updating country creates the new GK-country aggregate');
SELECT is((SELECT move_count FROM stats.user_countries WHERE user_id = 11901 AND country_code = 'de'), 1::bigint, 'updating country creates the new user-country aggregate');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (11920, 11902, 11902, coords2position(52.22968, 21.01223), '2020-07-03 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (11921, 11902, 11902, coords2position(50.06143, 19.93658), '2020-07-03 09:00:00+00', 0);

SELECT is((SELECT first_move_id FROM stats.gk_countries_visited WHERE geokrety_id = 11902 AND country_code = 'pl'), 11920::bigint, 'initial earliest PL move is tracked in gk_countries_visited');

UPDATE gk_moves
SET position = coords2position(52.52000, 13.40500)
WHERE id = 11920;

SELECT is((SELECT first_move_id FROM stats.gk_countries_visited WHERE geokrety_id = 11902 AND country_code = 'pl'), 11921::bigint, 'earliest-move invalidation repairs first_move_id');
SELECT is((SELECT first_visited_at FROM stats.gk_countries_visited WHERE geokrety_id = 11902 AND country_code = 'pl'), '2020-07-03 09:00:00+00'::timestamptz, 'earliest-move invalidation repairs first_visited_at');
SELECT is((SELECT first_visit FROM stats.user_countries WHERE user_id = 11902 AND country_code = 'pl'), '2020-07-03 09:00:00+00'::timestamptz, 'earliest-move invalidation repairs user_countries.first_visit');
SELECT is((SELECT last_visit FROM stats.user_countries WHERE user_id = 11902 AND country_code = 'pl'), '2020-07-03 09:00:00+00'::timestamptz, 'earliest-move invalidation repairs user_countries.last_visit');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (11930, 11903, 11903, coords2position(52.22968, 21.01223), '2020-07-04 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (11931, 11903, 11903, coords2position(52.52000, 13.40500), '2020-07-04 09:00:00+00', 0);

SELECT is(
  (SELECT km_contributed FROM stats.country_daily_stats WHERE stats_date = '2020-07-04' AND country_code = 'de'),
  (SELECT COALESCE(SUM(km_distance), 0)::numeric FROM gk_moves WHERE moved_on_datetime::date = '2020-07-04' AND country = 'de'),
  'km_contributed matches the exact recomputed sum for the touched country/day'
);

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (11932, 11903, 11903, coords2position(50.06143, 19.93658), '2020-07-04 10:00:00+00', 0);

UPDATE gk_moves
SET move_type = 5
WHERE id = 11932;

SELECT is((SELECT drops FROM stats.country_daily_stats WHERE stats_date = '2020-07-04' AND country_code = 'pl'), 1::bigint, 'same-country same-day move_type updates repair the old type count');
SELECT is((SELECT dips FROM stats.country_daily_stats WHERE stats_date = '2020-07-04' AND country_code = 'pl'), 1::bigint, 'same-country same-day move_type updates repair the new type count');

UPDATE gk_moves
SET moved_on_datetime = '2020-07-04 07:30:00+00'
WHERE id = 11932;

SELECT is((SELECT first_move_id FROM stats.gk_countries_visited WHERE geokrety_id = 11903 AND country_code = 'pl'), 11932::bigint, 'same-country timestamp updates repair gk_countries_visited.first_move_id');
SELECT is((SELECT first_visit FROM stats.user_countries WHERE user_id = 11903 AND country_code = 'pl'), '2020-07-04 07:30:00+00'::timestamptz, 'same-country timestamp updates repair user_countries.first_visit');

DELETE FROM gk_moves WHERE id = 11931;

SELECT is((SELECT COUNT(*)::bigint FROM stats.country_daily_stats WHERE stats_date = '2020-07-04' AND country_code = 'de'), 0::bigint, 'delete removes the empty country/day aggregate');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_countries_visited WHERE geokrety_id = 11903 AND country_code = 'de'), 0::bigint, 'delete removes the empty GK-country aggregate');
SELECT is((SELECT COUNT(*)::bigint FROM stats.user_countries WHERE user_id = 11903 AND country_code = 'de'), 0::bigint, 'delete removes the empty user-country aggregate');

INSERT INTO gk_moves (id, geokret, username, position, moved_on_datetime, move_type)
VALUES (11940, 11904, 'country-rollups-anon-fr', coords2position(48.85660, 2.35220), '2020-07-05 08:00:00+00', 0);

SELECT is((SELECT COUNT(*)::bigint FROM stats.user_countries WHERE country_code = 'fr'), 0::bigint, 'anonymous move skips user_countries');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_countries_visited WHERE geokrety_id = 11904 AND country_code = 'fr'), 1::bigint, 'anonymous move still updates gk_countries_visited');

SELECT * FROM finish();
ROLLBACK;
