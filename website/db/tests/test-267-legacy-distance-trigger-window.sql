BEGIN;

SELECT plan(9);

\set nice '\''0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540'\''
\set paris '\''0101000020E610000066666666666602406666666666664840'\''
\set berlin '\''0101000020E61000009A99999999992A400000000000404A40'\''
\set pula '\''0101000020E61000009A99999999992B406666666666664640'\''

INSERT INTO gk_users (id, username, registration_ip)
VALUES (926700001, 'test-267-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, gkid, tracking_code, name, type, created_on_datetime)
VALUES (926700010, 926700010, 'T2670001', 'Legacy distance window GK', 0, '2020-01-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES
  (926700020, 926700010, 926700001, :nice, '2020-01-01 00:00:00+00', 0),
  (926700021, 926700010, 926700001, :paris, '2020-01-01 01:00:00+00', 0),
  (926700022, 926700010, 926700001, :berlin, '2020-01-01 02:00:00+00', 0);

SELECT is((SELECT distance FROM gk_moves WHERE id = 926700020), 0, 'first counted move keeps a zero legacy distance');
SELECT is((SELECT distance FROM gk_moves WHERE id = 926700021), 680, 'second move keeps the Paris-from-Nice legacy distance');
SELECT is((SELECT distance FROM gk_moves WHERE id = 926700022), 877, 'third move keeps the Berlin-from-Paris legacy distance');
SELECT is((SELECT distance FROM gk_geokrety WHERE id = 926700010), 1557::bigint, 'geokret total distance matches the seeded legacy distances');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (926700025, 926700010, 926700001, :pula, '2020-01-01 01:30:00+00', 0);

SELECT is((SELECT distance FROM gk_moves WHERE id = 926700025), 980, 'retroactive insert computes the inserted move legacy distance');
SELECT is((SELECT distance FROM gk_moves WHERE id = 926700022), 857, 'retroactive insert rewires only the immediate successor legacy distance');
SELECT is((SELECT distance FROM gk_geokrety WHERE id = 926700010), 2517::bigint, 'geokret total distance reflects the inserted legacy segment');

DELETE FROM gk_moves WHERE id = 926700025;

SELECT is((SELECT distance FROM gk_moves WHERE id = 926700022), 877, 'deleting the retroactive move restores the successor legacy distance');
SELECT is((SELECT distance FROM gk_geokrety WHERE id = 926700010), 1557::bigint, 'geokret total distance returns to the original value after delete');

SELECT * FROM finish();
ROLLBACK;
