BEGIN;
SELECT plan(23);

SELECT has_table('stats', 'daily_entity_counts', 'stats.daily_entity_counts table exists');
SELECT col_is_pk('stats', 'daily_entity_counts', ARRAY['count_date', 'entity'], 'daily_entity_counts primary key is (count_date, entity)');
SELECT col_type_is('stats', 'daily_entity_counts', 'count_date', 'date', 'count_date column is date');
SELECT col_type_is('stats', 'daily_entity_counts', 'entity', 'character varying(32)', 'entity column is varchar(32)');
SELECT col_type_is('stats', 'daily_entity_counts', 'cnt', 'bigint', 'cnt column is bigint');
SELECT col_not_null('stats', 'daily_entity_counts', 'count_date', 'count_date is NOT NULL');
SELECT col_not_null('stats', 'daily_entity_counts', 'entity', 'entity is NOT NULL');
SELECT col_not_null('stats', 'daily_entity_counts', 'cnt', 'cnt is NOT NULL');
SELECT col_default_is('stats', 'daily_entity_counts', 'cnt', '0', 'cnt default is 0');
SELECT columns_are('stats', 'daily_entity_counts', ARRAY['count_date', 'entity', 'cnt'], 'daily_entity_counts exposes the canonical column set in order');
SELECT is(
    (SELECT obj_description('stats.daily_entity_counts'::regclass, 'pg_class')),
    'Daily cumulative entity counts for trend charts; populated by nightly snapshot job',
    'daily_entity_counts table comment matches the spec'
);
SELECT is(
    (
        SELECT col_description('stats.daily_entity_counts'::regclass, ordinal_position)
        FROM information_schema.columns
        WHERE table_schema = 'stats' AND table_name = 'daily_entity_counts' AND column_name = 'entity'
    ),
    'Entity name matching entity_counters_shard.entity',
    'entity column comment matches the spec'
);
SELECT is(
    (
        SELECT col_description('stats.daily_entity_counts'::regclass, ordinal_position)
        FROM information_schema.columns
        WHERE table_schema = 'stats' AND table_name = 'daily_entity_counts' AND column_name = 'cnt'
    ),
    'Entity count snapshot value for count_date',
    'cnt column comment matches the spec'
);
SELECT lives_ok(
    $$INSERT INTO stats.daily_entity_counts (count_date, entity, cnt) VALUES ('2025-06-15', 'gk_moves', 6000000)$$,
    'insert succeeds for a valid daily entity snapshot row'
);
SELECT is((SELECT cnt FROM stats.daily_entity_counts WHERE count_date = '2025-06-15' AND entity = 'gk_moves'), 6000000::bigint, 'read-back returns the inserted cnt value');
SELECT lives_ok(
    $$INSERT INTO stats.daily_entity_counts (count_date, entity) VALUES ('2025-06-16', 'gk_users')$$,
    'minimal insert succeeds and uses the cnt default'
);
SELECT is((SELECT cnt FROM stats.daily_entity_counts WHERE count_date = '2025-06-16' AND entity = 'gk_users'), 0::bigint, 'minimal insert defaults cnt to zero');
SELECT throws_ok(
    $$INSERT INTO stats.daily_entity_counts (count_date, entity, cnt) VALUES ('2025-06-15', 'gk_moves', 6000001)$$,
    '23505',
    NULL,
    'duplicate (count_date, entity) raises a primary key violation'
);
SELECT throws_ok(
    $$INSERT INTO stats.daily_entity_counts (count_date, entity, cnt) VALUES ('2025-06-17', NULL, 1)$$,
    '23502',
    NULL,
    'NULL entity raises a not-null violation'
);
SELECT throws_ok(
    $$INSERT INTO stats.daily_entity_counts (count_date, entity, cnt) VALUES (NULL, 'gk_pictures', 1)$$,
    '23502',
    NULL,
    'NULL count_date raises a not-null violation'
);
SELECT throws_ok(
    $$INSERT INTO stats.daily_entity_counts (count_date, entity, cnt) VALUES ('2025-06-18', 'gk_moves', -1)$$,
    '23514',
    NULL,
    'negative cnt raises a check violation'
);
SELECT lives_ok(
    $$INSERT INTO stats.daily_entity_counts (count_date, entity, cnt)
      VALUES ('2025-06-15', 'gk_moves', 7000000)
      ON CONFLICT (count_date, entity) DO UPDATE SET cnt = EXCLUDED.cnt$$,
    'ON CONFLICT upsert updates an existing snapshot row'
);
SELECT is((SELECT cnt FROM stats.daily_entity_counts WHERE count_date = '2025-06-15' AND entity = 'gk_moves'), 7000000::bigint, 'upsert updates the existing cnt value');

SELECT * FROM finish();
ROLLBACK;
