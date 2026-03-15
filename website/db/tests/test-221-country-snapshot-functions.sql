BEGIN;
SELECT plan(35);

SELECT has_function('stats', 'fn_snapshot_daily_country_stats', ARRAY['daterange'], 'fn_snapshot_daily_country_stats function exists');
SELECT has_function('stats', 'fn_snapshot_user_country_stats', ARRAY['daterange'], 'fn_snapshot_user_country_stats function exists');
SELECT has_function('stats', 'fn_snapshot_gk_country_stats', ARRAY['daterange'], 'fn_snapshot_gk_country_stats function exists');
SELECT function_returns('stats', 'fn_snapshot_daily_country_stats', ARRAY['daterange'], 'bigint', 'fn_snapshot_daily_country_stats returns bigint');
SELECT function_returns('stats', 'fn_snapshot_user_country_stats', ARRAY['daterange'], 'bigint', 'fn_snapshot_user_country_stats returns bigint');
SELECT function_returns('stats', 'fn_snapshot_gk_country_stats', ARRAY['daterange'], 'bigint', 'fn_snapshot_gk_country_stats returns bigint');

INSERT INTO gk_users (id, username, registration_ip) VALUES (12101, 'country-snapshot-user-1', '127.0.0.1');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (12101, 'Country snapshot GK 1', 0, '2020-09-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, created_on_datetime) VALUES (12102, 'Country snapshot GK 2', 0, '2020-09-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, country, position, moved_on_datetime, move_type)
VALUES (12110, 12101, 12101, 'pl', coords2position(52.22968, 21.01223), '2020-09-01 08:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, country, position, moved_on_datetime, move_type)
VALUES (12111, 12101, 12101, 'pl', coords2position(50.06143, 19.93658), '2020-09-01 09:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, username, country, position, moved_on_datetime, move_type)
VALUES (12112, 12102, 'country-snapshot-anon', 'de', coords2position(52.52000, 13.40500), '2020-10-01 08:00:00+00', 0);

DELETE FROM stats.country_daily_stats;
DELETE FROM stats.user_countries;
DELETE FROM stats.gk_countries_visited;

SELECT cmp_ok(stats.fn_snapshot_daily_country_stats(NULL), '>=', 2::bigint, 'full daily country snapshot writes at least two rows');
SELECT is((SELECT COUNT(*)::bigint FROM stats.country_daily_stats), 2::bigint, 'full daily country snapshot writes the expected row count');
SELECT is((SELECT unique_users FROM stats.country_daily_stats WHERE stats_date = '2020-09-01' AND country_code = 'PL'), 1::bigint, 'daily country snapshot computes unique_users exactly');
SELECT is((SELECT unique_gks FROM stats.country_daily_stats WHERE stats_date = '2020-09-01' AND country_code = 'PL'), 1::bigint, 'daily country snapshot computes unique_gks exactly');

SELECT cmp_ok(stats.fn_snapshot_user_country_stats(NULL), '>=', 1::bigint, 'full user country snapshot writes at least one row');
SELECT is((SELECT COUNT(*)::bigint FROM stats.user_countries), 1::bigint, 'user country snapshot skips anonymous moves');

SELECT cmp_ok(stats.fn_snapshot_gk_country_stats(NULL), '>=', 2::bigint, 'full GK country snapshot writes at least two rows');
SELECT is((SELECT first_move_id FROM stats.gk_countries_visited WHERE geokrety_id = 12101 AND country_code = 'PL'), 12110::bigint, 'GK country snapshot keeps the earliest move id');

UPDATE stats.country_daily_stats
SET moves_count = 99,
    unique_users = 99,
    km_contributed = 99.999
WHERE stats_date = '2020-09-01' AND country_code = 'PL';
INSERT INTO stats.country_daily_stats (stats_date, country_code, moves_count)
VALUES ('2020-11-01', 'ZZ', 1);

UPDATE stats.user_countries
SET move_count = 99,
    first_visit = '2020-09-02 00:00:00+00',
    last_visit = '2020-09-03 00:00:00+00'
WHERE user_id = 12101 AND country_code = 'PL';
INSERT INTO stats.user_countries (user_id, country_code, move_count, first_visit, last_visit)
VALUES (12199, 'ZZ', 1, '2020-11-01 00:00:00+00', '2020-11-01 00:00:00+00');

UPDATE stats.gk_countries_visited
SET first_visited_at = '2020-09-02 00:00:00+00',
    first_move_id = 12111,
    move_count = 99
WHERE geokrety_id = 12101 AND country_code = 'PL';
INSERT INTO stats.gk_countries_visited (geokrety_id, country_code, first_visited_at, first_move_id, move_count)
VALUES (12199, 'ZZ', '2020-11-01 00:00:00+00', 1, 1);

SELECT is(stats.fn_snapshot_daily_country_stats(NULL), 2::bigint, 'full daily country snapshot rewrites the canonical rows');
SELECT is((SELECT moves_count FROM stats.country_daily_stats WHERE stats_date = '2020-09-01' AND country_code = 'PL'), 2::bigint, 'full daily country snapshot repairs stale moves_count');
SELECT is((SELECT COUNT(*)::bigint FROM stats.country_daily_stats WHERE country_code = 'ZZ'), 0::bigint, 'full daily country snapshot removes ghost rows');
SELECT is(stats.fn_snapshot_user_country_stats(NULL), 1::bigint, 'full user country snapshot rewrites the canonical rows');
SELECT is((SELECT move_count FROM stats.user_countries WHERE user_id = 12101 AND country_code = 'PL'), 2::bigint, 'full user country snapshot repairs stale move_count');
SELECT is((SELECT COUNT(*)::bigint FROM stats.user_countries WHERE country_code = 'ZZ'), 0::bigint, 'full user country snapshot removes ghost rows');
SELECT is(stats.fn_snapshot_gk_country_stats(NULL), 2::bigint, 'full GK country snapshot rewrites the canonical rows');
SELECT is((SELECT first_move_id FROM stats.gk_countries_visited WHERE geokrety_id = 12101 AND country_code = 'PL'), 12110::bigint, 'full GK country snapshot repairs stale first_move_id');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_countries_visited WHERE country_code = 'ZZ'), 0::bigint, 'full GK country snapshot removes ghost rows');

CREATE TEMP TABLE snapshot_country_daily_stats AS
SELECT stats_date, country_code, moves_count, unique_users, unique_gks, km_contributed
FROM stats.country_daily_stats
ORDER BY stats_date, country_code;
CREATE TEMP TABLE snapshot_user_countries AS
SELECT user_id, country_code, move_count, first_visit, last_visit
FROM stats.user_countries
ORDER BY user_id, country_code;
CREATE TEMP TABLE snapshot_gk_countries_visited AS
SELECT geokrety_id, country_code, first_visited_at, first_move_id, move_count
FROM stats.gk_countries_visited
ORDER BY geokrety_id, country_code;

SELECT lives_ok($$SELECT stats.fn_snapshot_daily_country_stats(NULL);$$, 'daily country snapshot is safe to re-run');
SELECT results_eq(
  $$SELECT stats_date, country_code, moves_count, unique_users, unique_gks, km_contributed FROM stats.country_daily_stats ORDER BY stats_date, country_code$$,
  $$SELECT stats_date, country_code, moves_count, unique_users, unique_gks, km_contributed FROM snapshot_country_daily_stats ORDER BY stats_date, country_code$$,
  're-running the daily country snapshot is idempotent'
);
SELECT lives_ok($$SELECT stats.fn_snapshot_user_country_stats(NULL);$$, 'user country snapshot is safe to re-run');
SELECT results_eq(
  $$SELECT user_id, country_code, move_count, first_visit, last_visit FROM stats.user_countries ORDER BY user_id, country_code$$,
  $$SELECT user_id, country_code, move_count, first_visit, last_visit FROM snapshot_user_countries ORDER BY user_id, country_code$$,
  're-running the user country snapshot is idempotent'
);
SELECT lives_ok($$SELECT stats.fn_snapshot_gk_country_stats(NULL);$$, 'GK country snapshot is safe to re-run');
SELECT results_eq(
  $$SELECT geokrety_id, country_code, first_visited_at, first_move_id, move_count FROM stats.gk_countries_visited ORDER BY geokrety_id, country_code$$,
  $$SELECT geokrety_id, country_code, first_visited_at, first_move_id, move_count FROM snapshot_gk_countries_visited ORDER BY geokrety_id, country_code$$,
  're-running the GK country snapshot is idempotent'
);

DELETE FROM stats.country_daily_stats;
DELETE FROM stats.user_countries;
DELETE FROM stats.gk_countries_visited;

SELECT is(stats.fn_snapshot_daily_country_stats('[2020-09-01,2020-10-01)'::daterange), 1::bigint, 'daily country snapshot filters by p_period');
SELECT is(stats.fn_snapshot_user_country_stats('[2020-09-01,2020-10-01)'::daterange), 1::bigint, 'user country snapshot keeps exact full-source rows even when p_period is supplied');
SELECT is(stats.fn_snapshot_gk_country_stats('[2020-09-01,2020-10-01)'::daterange), 1::bigint, 'GK country snapshot writes only touched geokret-country keys when p_period is supplied');
SELECT is((SELECT COUNT(*)::bigint FROM stats.country_daily_stats), 1::bigint, 'period-limited daily snapshot only writes rows in range');
SELECT is((SELECT COUNT(*)::bigint FROM stats.user_countries), 1::bigint, 'user country snapshot remains exact for the current full-source fixture set');
SELECT is((SELECT COUNT(*)::bigint FROM stats.gk_countries_visited), 1::bigint, 'GK country snapshot remains exact for the touched-key fixture set');

SELECT * FROM finish();
ROLLBACK;
