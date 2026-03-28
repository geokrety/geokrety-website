BEGIN;

SELECT plan(12);

\set nice '\''0101000020E6100000F6285C8FC2F51C405C8FC2F528DC4540'\''
\set paris '\''0101000020E610000066666666666602406666666666664840'\''
\set berlin '\''0101000020E61000009A99999999992A400000000000404A40'\''
\set warsaw '\''0101000020E610000000000000000035409A99999999194A40'\''
\set moscow '\''0101000020E6100000CDCCCCCCCCCC42409A99999999D94B40'\''
\set pula '\''0101000020E61000009A99999999992B406666666666664640'\''

INSERT INTO gk_users (id, username, registration_ip)
VALUES (926800001, 'test-268-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, gkid, tracking_code, name, type, created_on_datetime)
VALUES
  (926800010, 926800010, 'T2680010', 'Legacy distance update GK', 0, '2020-01-01 00:00:00+00'),
  (926800011, 926800011, 'T2680011', 'Legacy distance reorder GK', 0, '2020-01-01 00:00:00+00'),
  (926800012, 926800012, 'T2680012', 'Legacy distance source GK', 0, '2020-01-01 00:00:00+00'),
  (926800013, 926800013, 'T2680013', 'Legacy distance target GK', 0, '2020-01-01 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES
  (926800020, 926800010, 926800001, :nice, '2020-01-01 00:00:00+00', 0),
  (926800021, 926800010, 926800001, :paris, '2020-01-01 01:00:00+00', 0),
  (926800022, 926800010, 926800001, :berlin, '2020-01-01 02:00:00+00', 0),

  (926800030, 926800011, 926800001, :nice, '2020-01-01 00:00:00+00', 0),
  (926800031, 926800011, 926800001, :paris, '2020-01-01 01:00:00+00', 0),
  (926800032, 926800011, 926800001, :berlin, '2020-01-01 02:00:00+00', 0),
  (926800033, 926800011, 926800001, :warsaw, '2020-01-01 03:00:00+00', 0),
  (926800034, 926800011, 926800001, :moscow, '2020-01-01 04:00:00+00', 0),
  (926800035, 926800011, 926800001, :pula, '2020-01-01 12:00:00+00', 0),

  (926800040, 926800012, 926800001, :nice, '2020-01-01 00:00:00+00', 0),
  (926800041, 926800012, 926800001, :paris, '2020-01-01 01:00:00+00', 0),
  (926800042, 926800012, 926800001, :berlin, '2020-01-01 02:00:00+00', 0),
  (926800043, 926800012, 926800001, :warsaw, '2020-01-01 03:00:00+00', 0);

UPDATE gk_moves
SET move_type = 2,
    position = NULL
WHERE id = 926800021;

SELECT is((SELECT distance FROM gk_moves WHERE id = 926800020), 0, 'move-type change keeps the first legacy distance at zero');
SELECT is((SELECT distance FROM gk_moves WHERE id = 926800021), NULL::integer, 'move-type change clears legacy distance for non-counting moves');
SELECT is((SELECT distance FROM gk_moves WHERE id = 926800022), 1074, 'move-type change rewires the immediate successor legacy distance');

UPDATE gk_moves
SET moved_on_datetime = '2020-01-01 01:30:00+00'
WHERE id = 926800035;

SELECT is((SELECT distance FROM gk_moves WHERE id = 926800031), 680, 'date change keeps the unaffected predecessor legacy distance');
SELECT is((SELECT distance FROM gk_moves WHERE id = 926800035), 980, 'date change recomputes the moved row legacy distance');
SELECT is((SELECT distance FROM gk_moves WHERE id = 926800032), 857, 'date change rewires the immediate successor legacy distance');
SELECT is((SELECT distance FROM gk_moves WHERE id = 926800033), 524, 'date change leaves later successors untouched');
SELECT is((SELECT distance FROM gk_moves WHERE id = 926800034), 1150, 'date change preserves the downstream legacy chain');

UPDATE gk_moves SET geokret = 926800013 WHERE id = 926800041;

SELECT is((SELECT distance FROM gk_moves WHERE id = 926800041), 0, 'cross-geokret move becomes the first legacy segment in the target chain');
SELECT is((SELECT distance FROM gk_moves WHERE id = 926800042), 1074, 'cross-geokret move rewires the old-chain successor legacy distance');

UPDATE gk_moves SET geokret = 926800013 WHERE id = 926800043;

SELECT is((SELECT distance FROM gk_moves WHERE id = 926800042), 1074, 'moving a later row keeps the remaining source-chain legacy distance stable');
SELECT is((SELECT distance FROM gk_moves WHERE id = 926800043), 1371, 'moving a later row appends the target-chain legacy distance from Paris to Warsaw');

SELECT * FROM finish();
ROLLBACK;
