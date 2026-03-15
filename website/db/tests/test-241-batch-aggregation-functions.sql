BEGIN;
SELECT plan(12);

DELETE FROM stats.hourly_activity;
DELETE FROM stats.country_pair_flows;
DELETE FROM stats.job_log
WHERE job_name IN ('fn_snapshot_hourly_activity', 'fn_snapshot_country_pair_flows');

SELECT has_function('stats', 'fn_snapshot_hourly_activity', ARRAY[]::text[], 'fn_snapshot_hourly_activity function exists');
SELECT function_returns('stats', 'fn_snapshot_hourly_activity', ARRAY[]::text[], 'bigint', 'fn_snapshot_hourly_activity returns bigint');
SELECT has_function('stats', 'fn_snapshot_country_pair_flows', ARRAY[]::text[], 'fn_snapshot_country_pair_flows function exists');
SELECT function_returns('stats', 'fn_snapshot_country_pair_flows', ARRAY[]::text[], 'bigint', 'fn_snapshot_country_pair_flows returns bigint');

INSERT INTO gk_users (id, username, registration_ip) VALUES (24101, 'snapshot-batch-user-1', '127.0.0.1');
INSERT INTO gk_users (id, username, registration_ip) VALUES (24102, 'snapshot-batch-user-2', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (24110, 'Snapshot batch GK 1', 0, 24101, 24101, '2020-11-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (24111, 'Snapshot batch GK 2', 0, 24102, 24102, '2020-11-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24120, 24110, 24101, coords2position(52.22968, 21.01223), '2020-11-01 10:15:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24121, 24110, 24101, coords2position(52.52000, 13.40500), '2020-11-01 11:05:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24122, 24110, 24101, coords2position(50.07550, 14.43780), '2020-12-02 12:10:00+00', 5);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24123, 24111, 24102, coords2position(52.22968, 21.01223), '2020-11-01 10:45:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24124, 24111, 24102, coords2position(52.52000, 13.40500), '2020-11-01 13:10:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, moved_on_datetime, move_type)
VALUES (24125, 24111, 24102, '2020-11-01 13:30:00+00', 2);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24126, 24110, 24101, coords2position(52.22968, 21.01223), '2020-11-01 12:00:00+00', 0);
INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (24127, 24110, 24101, coords2position(52.52000, 13.40500), '2020-11-01 14:00:00+00', 0);

SELECT lives_ok($$SELECT stats.fn_snapshot_hourly_activity();$$, 'fn_snapshot_hourly_activity executes successfully');
SELECT lives_ok($$SELECT stats.fn_snapshot_country_pair_flows();$$, 'fn_snapshot_country_pair_flows executes successfully');

SELECT results_eq(
  $$
  SELECT activity_date, hour_utc, move_type, move_count
  FROM stats.hourly_activity
  WHERE activity_date IN ('2020-11-01', '2020-12-02')
  ORDER BY activity_date, hour_utc, move_type
  $$,
  $$
  VALUES
    ('2020-11-01'::date, 10::smallint, 0::smallint, 2::bigint),
    ('2020-11-01'::date, 11::smallint, 0::smallint, 1::bigint),
    ('2020-11-01'::date, 12::smallint, 0::smallint, 1::bigint),
    ('2020-11-01'::date, 13::smallint, 0::smallint, 1::bigint),
    ('2020-11-01'::date, 13::smallint, 2::smallint, 1::bigint),
    ('2020-11-01'::date, 14::smallint, 0::smallint, 1::bigint),
    ('2020-12-02'::date, 12::smallint, 5::smallint, 1::bigint)
  $$,
  'hourly snapshot produces the expected UTC date/hour/type buckets'
);

SELECT results_eq(
  $$
  SELECT year_month, from_country::text, to_country::text, move_count, unique_gk_count
  FROM stats.country_pair_flows
  ORDER BY year_month, from_country, to_country
  $$,
  $$
  VALUES
    ('2020-11-01'::date, 'DE'::text, 'PL'::text, 1::bigint, 1::bigint),
    ('2020-11-01'::date, 'PL'::text, 'DE'::text, 3::bigint, 2::bigint),
    ('2020-12-01'::date, 'DE'::text, 'CZ'::text, 1::bigint, 1::bigint)
  $$,
  'country pair snapshot produces only the expected cross-country month buckets'
);

CREATE TEMP TABLE hourly_snapshot_first AS
SELECT activity_date, hour_utc, move_type, move_count
FROM stats.hourly_activity
ORDER BY activity_date, hour_utc, move_type;

CREATE TEMP TABLE country_pair_snapshot_first AS
SELECT year_month, from_country::text AS from_country, to_country::text AS to_country, move_count, unique_gk_count
FROM stats.country_pair_flows
ORDER BY year_month, from_country, to_country;

SELECT stats.fn_snapshot_hourly_activity();
SELECT stats.fn_snapshot_country_pair_flows();

SELECT results_eq(
  $$SELECT activity_date, hour_utc, move_type, move_count FROM stats.hourly_activity ORDER BY activity_date, hour_utc, move_type$$,
  $$SELECT activity_date, hour_utc, move_type, move_count FROM hourly_snapshot_first ORDER BY activity_date, hour_utc, move_type$$,
  're-running fn_snapshot_hourly_activity is idempotent'
);

SELECT results_eq(
  $$SELECT year_month, from_country::text, to_country::text, move_count, unique_gk_count FROM stats.country_pair_flows ORDER BY year_month, from_country, to_country$$,
  $$SELECT year_month, from_country, to_country, move_count, unique_gk_count FROM country_pair_snapshot_first ORDER BY year_month, from_country, to_country$$,
  're-running fn_snapshot_country_pair_flows is idempotent'
);

SELECT ok((SELECT COUNT(*) = 2 FROM stats.job_log WHERE job_name = 'fn_snapshot_hourly_activity' AND status = 'completed'), 'fn_snapshot_hourly_activity writes one completed job_log row per execution');
SELECT ok((SELECT COUNT(*) = 2 FROM stats.job_log WHERE job_name = 'fn_snapshot_country_pair_flows' AND status = 'completed'), 'fn_snapshot_country_pair_flows writes one completed job_log row per execution');

SELECT * FROM finish();
ROLLBACK;
