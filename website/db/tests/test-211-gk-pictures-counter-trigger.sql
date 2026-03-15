BEGIN;
SELECT plan(16);

CREATE TEMP TABLE gk_pictures_counter_baseline AS
SELECT entity, shard, cnt
FROM stats.entity_counters_shard
WHERE entity = 'gk_pictures'
   OR entity LIKE 'gk_pictures_type_%';

INSERT INTO gk_users (id, username, registration_ip)
VALUES (8111, 'pictures-counter-user', '127.0.0.1');

INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (8210, 'Pictures counter avatar GK', 0, 8111, 8111, '2020-03-01 00:00:00+00');
INSERT INTO gk_geokrety (id, name, type, owner, holder, created_on_datetime)
VALUES (8211, 'Pictures counter move GK', 0, 8111, 8111, '2020-03-02 00:00:00+00');

INSERT INTO gk_moves (id, geokret, author, position, moved_on_datetime, move_type)
VALUES (8311, 8211, 8111, coords2position(52.22968, 21.01223), '2020-03-02 08:00:00+00', 0);

SELECT has_function('geokrety', 'fn_gk_pictures_counter', ARRAY[]::text[], 'fn_gk_pictures_counter function exists');
SELECT function_returns('geokrety', 'fn_gk_pictures_counter', ARRAY[]::text[], 'trigger', 'fn_gk_pictures_counter returns trigger');
SELECT has_trigger('geokrety', 'gk_pictures', 'tr_gk_pictures_after_counter', 'tr_gk_pictures_after_counter trigger exists');

INSERT INTO gk_pictures (id, author, geokret, type, created_on_datetime)
VALUES (8416, 8111, 8210, 0, '2020-03-01 09:00:00+00');

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_pictures'), (SELECT SUM(cnt)::bigint FROM gk_pictures_counter_baseline WHERE entity = 'gk_pictures'), 'draft picture rows stay out of the counters until uploaded');

UPDATE gk_pictures
SET uploaded_on_datetime = '2020-03-01 12:00:00+00'
WHERE id = 8416;

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_pictures'), (SELECT SUM(cnt)::bigint + 1 FROM gk_pictures_counter_baseline WHERE entity = 'gk_pictures'), 'upload finalization increments the total gk_pictures counter');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_pictures_type_0'), (SELECT SUM(cnt)::bigint + 1 FROM gk_pictures_counter_baseline WHERE entity = 'gk_pictures_type_0'), 'upload finalization increments the avatar type counter');
SELECT is((SELECT pictures_uploaded_total FROM stats.daily_activity WHERE activity_date = '2020-03-01'), 1::bigint, 'upload finalization refreshes daily_activity.pictures_uploaded_total for the upload day');

INSERT INTO gk_pictures (id, author, move, geokret, type, created_on_datetime, uploaded_on_datetime)
VALUES (8417, 8111, 8311, 8211, 1, '2020-03-02 10:00:00+00', '2020-03-02 10:30:00+00');

SELECT is((SELECT pictures_uploaded_move FROM stats.daily_activity WHERE activity_date = '2020-03-02'), 1::bigint, 'move picture insert increments pictures_uploaded_move');

INSERT INTO gk_pictures (id, author, "user", type, created_on_datetime, uploaded_on_datetime)
VALUES (8418, 8111, 8111, 2, '2020-03-03 11:00:00+00', '2020-03-03 11:30:00+00');

SELECT is((SELECT pictures_uploaded_user FROM stats.daily_activity WHERE activity_date = '2020-03-03'), 1::bigint, 'user picture insert increments pictures_uploaded_user');

DELETE FROM gk_pictures WHERE id = 8417;

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_pictures'), (SELECT SUM(cnt)::bigint + 2 FROM gk_pictures_counter_baseline WHERE entity = 'gk_pictures'), 'delete decrements the total counter');
SELECT is((SELECT pictures_uploaded_total FROM stats.daily_activity WHERE activity_date = '2020-03-02'), 0::bigint, 'delete refreshes the old day back to zero');

UPDATE gk_pictures
SET geokret = NULL,
    "user" = 8111,
    type = 2,
   uploaded_on_datetime = '2020-03-04 12:00:00+00'
WHERE id = 8416;

SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_pictures'), (SELECT SUM(cnt)::bigint + 2 FROM gk_pictures_counter_baseline WHERE entity = 'gk_pictures'), 'type/date update keeps the total counter stable');
SELECT is((SELECT pictures_uploaded_avatar FROM stats.daily_activity WHERE activity_date = '2020-03-01'), 0::bigint, 'update reclassifies the old day avatar bucket down to zero');
SELECT is((SELECT pictures_uploaded_user FROM stats.daily_activity WHERE activity_date = '2020-03-04'), 1::bigint, 'update reclassifies the new day user bucket up to one');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_pictures_type_0'), (SELECT SUM(cnt)::bigint FROM gk_pictures_counter_baseline WHERE entity = 'gk_pictures_type_0'), 'update removes the old type contribution');
SELECT is((SELECT SUM(cnt)::bigint FROM stats.entity_counters_shard WHERE entity = 'gk_pictures_type_2'), (SELECT SUM(cnt)::bigint + 2 FROM gk_pictures_counter_baseline WHERE entity = 'gk_pictures_type_2'), 'update applies the new type contribution');

SELECT * FROM finish();
ROLLBACK;
