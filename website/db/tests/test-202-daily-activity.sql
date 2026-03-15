BEGIN;
SELECT plan(30);

SELECT has_table('stats', 'daily_activity', 'stats.daily_activity table exists');
SELECT col_is_pk('stats', 'daily_activity', ARRAY['activity_date'], 'daily_activity primary key is activity_date');
SELECT col_type_is('stats', 'daily_activity', 'activity_date', 'date', 'activity_date column is date');
SELECT col_type_is('stats', 'daily_activity', 'km_contributed', 'numeric(14,3)', 'km_contributed column is numeric(14,3)');
SELECT col_type_is('stats', 'daily_activity', 'points_contributed', 'numeric(16,4)', 'points_contributed column is numeric(16,4)');
SELECT col_type_is('stats', 'daily_activity', 'users_registered', 'bigint', 'users_registered column is bigint');
SELECT col_default_is('stats', 'daily_activity', 'total_moves', '0', 'total_moves default is 0');
SELECT col_default_is('stats', 'daily_activity', 'km_contributed', '0', 'km_contributed default is 0');
SELECT col_default_is('stats', 'daily_activity', 'points_contributed', '0', 'points_contributed default is 0');
SELECT col_not_null('stats', 'daily_activity', 'activity_date', 'activity_date is NOT NULL');
SELECT col_not_null('stats', 'daily_activity', 'total_moves', 'total_moves is NOT NULL');
SELECT col_not_null('stats', 'daily_activity', 'users_registered', 'users_registered is NOT NULL');
SELECT is(
    (
        SELECT COUNT(*)::INT
        FROM information_schema.columns
        WHERE table_schema = 'stats' AND table_name = 'daily_activity'
    ),
    17,
    'daily_activity has 17 columns'
);
SELECT set_eq(
    $$SELECT column_name::text FROM information_schema.columns WHERE table_schema = 'stats' AND table_name = 'daily_activity'$$,
    $$VALUES
        ('activity_date'::text),
        ('total_moves'::text),
        ('drops'::text),
        ('grabs'::text),
        ('comments'::text),
        ('sees'::text),
        ('archives'::text),
        ('dips'::text),
        ('km_contributed'::text),
        ('points_contributed'::text),
        ('gk_created'::text),
        ('pictures_uploaded_total'::text),
        ('pictures_uploaded_avatar'::text),
        ('pictures_uploaded_move'::text),
        ('pictures_uploaded_user'::text),
        ('loves_count'::text),
        ('users_registered'::text)$$,
    'daily_activity exposes the exact Sprint 2 column set'
);
SELECT is(
    (SELECT obj_description('stats.daily_activity'::regclass, 'pg_class')),
    'Per-calendar-day aggregate activity metrics; one row per day',
    'daily_activity table comment matches the spec'
);
SELECT is(
    (
        SELECT col_description('stats.daily_activity'::regclass, ordinal_position)
        FROM information_schema.columns
        WHERE table_schema = 'stats' AND table_name = 'daily_activity' AND column_name = 'points_contributed'
    ),
    'Total gamification points awarded on this date; updated by points-awarder service (Sprint 4)',
    'points_contributed comment matches the spec'
);
SELECT is(
    (
        SELECT col_description('stats.daily_activity'::regclass, ordinal_position)
        FROM information_schema.columns
        WHERE table_schema = 'stats' AND table_name = 'daily_activity' AND column_name = 'gk_created'
    ),
    'New GeoKrety created on this date; updated by gk_geokrety trigger (Step 2.8)',
    'gk_created comment matches the spec'
);
SELECT is(
    (
        SELECT col_description('stats.daily_activity'::regclass, ordinal_position)
        FROM information_schema.columns
        WHERE table_schema = 'stats' AND table_name = 'daily_activity' AND column_name = 'pictures_uploaded_total'
    ),
    'Total pictures uploaded; updated by gk_pictures trigger (Step 2.9)',
    'pictures_uploaded_total comment matches the spec'
);
SELECT is(
    (
        SELECT col_description('stats.daily_activity'::regclass, ordinal_position)
        FROM information_schema.columns
        WHERE table_schema = 'stats' AND table_name = 'daily_activity' AND column_name = 'loves_count'
    ),
    'Loves given on this date; updated by loves trigger (Sprint 5)',
    'loves_count comment matches the spec'
);
SELECT is(
    (
        SELECT col_description('stats.daily_activity'::regclass, ordinal_position)
        FROM information_schema.columns
        WHERE table_schema = 'stats' AND table_name = 'daily_activity' AND column_name = 'users_registered'
    ),
    'New user registrations; updated by gk_users trigger (Step 2.10)',
    'users_registered comment matches the spec'
);
SELECT lives_ok(
    $$INSERT INTO stats.daily_activity (
        activity_date,
        total_moves,
        drops,
        grabs,
        comments,
        sees,
        archives,
        dips,
        km_contributed,
        points_contributed,
        gk_created,
        pictures_uploaded_total,
        pictures_uploaded_avatar,
        pictures_uploaded_move,
        pictures_uploaded_user,
        loves_count,
        users_registered
    ) VALUES (
        '2025-06-15',
        100,
        20,
        25,
        10,
        15,
        5,
        25,
        1234.567,
        4321.9876,
        12,
        8,
        2,
        5,
        1,
        7,
        9
    )$$,
    'insert succeeds for a fully populated daily_activity row'
);
SELECT is((SELECT total_moves FROM stats.daily_activity WHERE activity_date = '2025-06-15'), 100::bigint, 'read-back returns total_moves');
SELECT is((SELECT km_contributed FROM stats.daily_activity WHERE activity_date = '2025-06-15'), 1234.567::numeric, 'read-back returns km_contributed');
SELECT is((SELECT points_contributed FROM stats.daily_activity WHERE activity_date = '2025-06-15'), 4321.9876::numeric, 'read-back returns points_contributed');
SELECT lives_ok(
    $$INSERT INTO stats.daily_activity (activity_date) VALUES ('2025-06-16')$$,
    'minimal insert succeeds and relies on defaults for future-populated columns'
);
SELECT is((SELECT total_moves FROM stats.daily_activity WHERE activity_date = '2025-06-16'), 0::bigint, 'minimal insert defaults total_moves to zero');
SELECT is((SELECT pictures_uploaded_total FROM stats.daily_activity WHERE activity_date = '2025-06-16'), 0::bigint, 'minimal insert defaults pictures_uploaded_total to zero');
SELECT is((SELECT points_contributed FROM stats.daily_activity WHERE activity_date = '2025-06-16'), 0::numeric, 'minimal insert defaults points_contributed to zero');
SELECT is((SELECT users_registered FROM stats.daily_activity WHERE activity_date = '2025-06-16'), 0::bigint, 'minimal insert defaults users_registered to zero');
SELECT throws_ok(
    $$INSERT INTO stats.daily_activity (activity_date) VALUES ('2025-06-15')$$,
    '23505',
    NULL,
    'duplicate activity_date raises a primary key violation'
);

SELECT * FROM finish();
ROLLBACK;
