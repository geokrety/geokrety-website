BEGIN;
SELECT plan(23);

SELECT has_table('stats', 'entity_counters_shard', 'stats.entity_counters_shard table exists');
SELECT col_is_pk('stats', 'entity_counters_shard', ARRAY['entity', 'shard'], 'entity_counters_shard primary key is (entity, shard)');
SELECT col_type_is('stats', 'entity_counters_shard', 'entity', 'character varying(32)', 'entity column is varchar(32)');
SELECT col_type_is('stats', 'entity_counters_shard', 'shard', 'integer', 'shard column is integer');
SELECT col_type_is('stats', 'entity_counters_shard', 'cnt', 'bigint', 'cnt column is bigint');
SELECT col_not_null('stats', 'entity_counters_shard', 'entity', 'entity is NOT NULL');
SELECT col_not_null('stats', 'entity_counters_shard', 'shard', 'shard is NOT NULL');
SELECT col_not_null('stats', 'entity_counters_shard', 'cnt', 'cnt is NOT NULL');
SELECT col_default_is('stats', 'entity_counters_shard', 'cnt', '0', 'cnt default is 0');
SELECT is((SELECT COUNT(*)::INT FROM stats.entity_counters_shard), 400, '400 shard rows are pre-initialized');
SELECT is((SELECT COUNT(DISTINCT entity)::INT FROM stats.entity_counters_shard), 25, '25 distinct entities are pre-initialized');
SELECT is((SELECT MIN(cnt_shards)::INT FROM (SELECT entity, COUNT(*) AS cnt_shards FROM stats.entity_counters_shard GROUP BY entity) AS shard_counts), 16, 'each entity has at least 16 shard rows');
SELECT is((SELECT MAX(cnt_shards)::INT FROM (SELECT entity, COUNT(*) AS cnt_shards FROM stats.entity_counters_shard GROUP BY entity) AS shard_counts), 16, 'each entity has at most 16 shard rows');
SELECT is((SELECT COUNT(*)::INT FROM stats.entity_counters_shard WHERE entity = 'gk_moves'), 16, 'gk_moves entity has 16 shards');
SELECT is((SELECT COUNT(*)::INT FROM stats.entity_counters_shard WHERE entity = 'gk_loves'), 16, 'gk_loves entity has 16 shards');
SELECT is((SELECT COUNT(*)::INT FROM stats.entity_counters_shard WHERE cnt < 0), 0, 'all shard counters remain nonnegative');
SELECT is((SELECT COALESCE(SUM(cnt), 0) FROM stats.entity_counters_shard), 0::numeric, 'all shard counters start at zero');
SELECT set_eq(
	$$SELECT DISTINCT entity::text FROM stats.entity_counters_shard$$,
	$$VALUES
		('gk_moves'::text),
		('gk_moves_type_0'::text),
		('gk_moves_type_1'::text),
		('gk_moves_type_2'::text),
		('gk_moves_type_3'::text),
		('gk_moves_type_4'::text),
		('gk_moves_type_5'::text),
		('gk_geokrety'::text),
		('gk_geokrety_type_0'::text),
		('gk_geokrety_type_1'::text),
		('gk_geokrety_type_2'::text),
		('gk_geokrety_type_3'::text),
		('gk_geokrety_type_4'::text),
		('gk_geokrety_type_5'::text),
		('gk_geokrety_type_6'::text),
		('gk_geokrety_type_7'::text),
		('gk_geokrety_type_8'::text),
		('gk_geokrety_type_9'::text),
		('gk_geokrety_type_10'::text),
		('gk_pictures'::text),
		('gk_pictures_type_0'::text),
		('gk_pictures_type_1'::text),
		('gk_pictures_type_2'::text),
		('gk_users'::text),
		('gk_loves'::text)$$,
	'the seeded entity catalog matches the Sprint 2 contract'
);
SELECT set_eq(
	$$SELECT DISTINCT shard FROM stats.entity_counters_shard$$,
	$$SELECT generate_series(0, 15)$$,
	'the seeded shard catalog is exactly 0 through 15'
);
SELECT throws_ok(
	$$INSERT INTO stats.entity_counters_shard (entity, shard, cnt) VALUES ('invalid_shard_entity', 16, 0)$$,
	'23514',
	NULL,
	'shard range check rejects values above 15'
);
SELECT throws_ok(
	$$INSERT INTO stats.entity_counters_shard (entity, shard, cnt) VALUES ('negative_cnt_entity', 0, -1)$$,
	'23514',
	NULL,
	'cnt check rejects negative values'
);
SELECT lives_ok(
	$$INSERT INTO stats.entity_counters_shard (entity, shard) VALUES ('default_cnt_entity', 0)$$,
	'cnt can be omitted on insert and uses its default'
);
SELECT is(
	(SELECT cnt FROM stats.entity_counters_shard WHERE entity = 'default_cnt_entity' AND shard = 0),
	0::bigint,
	'cnt defaults to zero when omitted from insert'
);

SELECT * FROM finish();
ROLLBACK;
