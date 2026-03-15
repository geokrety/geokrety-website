BEGIN;
SELECT plan(14);

SELECT has_table('stats', 'daily_active_users', 'stats.daily_active_users table exists');
SELECT col_is_pk('stats', 'daily_active_users', ARRAY['activity_date', 'user_id'], 'daily_active_users primary key is (activity_date, user_id)');
SELECT col_type_is('stats', 'daily_active_users', 'activity_date', 'date', 'activity_date column is date');
SELECT col_type_is('stats', 'daily_active_users', 'user_id', 'integer', 'user_id column is integer');
SELECT col_not_null('stats', 'daily_active_users', 'activity_date', 'activity_date is NOT NULL');
SELECT col_not_null('stats', 'daily_active_users', 'user_id', 'user_id is NOT NULL');
SELECT is((SELECT COUNT(*)::INT FROM information_schema.columns WHERE table_schema = 'stats' AND table_name = 'daily_active_users'), 2, 'daily_active_users has exactly 2 columns');
SELECT columns_are('stats', 'daily_active_users', ARRAY['activity_date', 'user_id'], 'daily_active_users has only the canonical presence columns in order');
SELECT is(
    (SELECT obj_description('stats.daily_active_users'::regclass, 'pg_class')),
    'Presence table for users active on a given day; one row per (activity_date, user_id)',
    'daily_active_users table comment matches the spec'
);
SELECT lives_ok(
    $$INSERT INTO stats.daily_active_users (activity_date, user_id) VALUES ('2025-06-15', 42)$$,
    'insert succeeds for a valid presence row'
);
SELECT is((SELECT user_id FROM stats.daily_active_users WHERE activity_date = '2025-06-15' AND user_id = 42), 42, 'read-back returns the inserted user_id');
SELECT throws_ok(
    $$INSERT INTO stats.daily_active_users (activity_date, user_id) VALUES ('2025-06-15', 42)$$,
    '23505',
    NULL,
    'duplicate (activity_date, user_id) raises a primary key violation'
);
SELECT throws_ok(
    $$INSERT INTO stats.daily_active_users (activity_date, user_id) VALUES ('2025-06-16', NULL)$$,
    '23502',
    NULL,
    'NULL user_id raises a not-null violation'
);
SELECT throws_ok(
    $$INSERT INTO stats.daily_active_users (activity_date, user_id) VALUES (NULL, 99)$$,
    '23502',
    NULL,
    'NULL activity_date raises a not-null violation'
);

SELECT * FROM finish();
ROLLBACK;
