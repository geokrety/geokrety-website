-- Start transaction and plan the tests.
BEGIN;
SELECT plan(47);

-- Run the tests.
SELECT has_schema('audit');
SELECT has_schema('geokrety');
SELECT has_schema('public');
SELECT has_schema('secure');
SELECT schemas_are(ARRAY[ 'audit', 'geokrety', 'public', 'secure', 'notify_queues' ]);

SELECT has_table( 'gk_moves' );
SELECT has_table( 'gk_users' );
SELECT has_table( 'gk_account_activation' );
SELECT has_table( 'gk_awards' );
SELECT has_table( 'gk_awards_won' );
SELECT has_table( 'gk_email_activation' );
SELECT has_table( 'gk_email_revalidate' );
SELECT has_table( 'gk_geokrety' );
SELECT has_table( 'gk_geokrety_rating' );
SELECT has_table( 'gk_mails' );
SELECT has_table( 'gk_moves' );
SELECT has_table( 'gk_moves_comments' );
SELECT has_table( 'gk_news' );
SELECT has_table( 'gk_news_comments' );
SELECT has_table( 'gk_news_comments_access' );
SELECT has_table( 'gk_owner_codes' );
SELECT has_table( 'gk_password_tokens' );
SELECT has_table( 'gk_pictures' );
SELECT has_table( 'gk_races' );
SELECT has_table( 'gk_races_participants' );
SELECT has_table( 'gk_statistics_counters' );
SELECT has_table( 'gk_statistics_daily_counters' );
SELECT has_table( 'gk_users' );
SELECT has_table( 'gk_watched' );
SELECT has_table( 'gk_waypoints_country' );
SELECT has_table( 'gk_waypoints_gc' );
SELECT has_table( 'gk_waypoints_oc' );
SELECT has_table( 'gk_waypoints_sync' );
SELECT has_table( 'gk_waypoints_types' );
SELECT has_table( 'phinxlog' );
SELECT has_table( 'scripts' );
SELECT has_table( 'sessions' );

SELECT has_table( 'audit'::name, 'actions_logs'::name );
SELECT has_table( 'audit'::name, 'posts'::name );
SELECT has_table( 'public'::name, 'countries'::name );
SELECT has_table( 'public'::name, 'spatial_ref_sys'::name );
SELECT has_table( 'public'::name, 'srtm'::name );
SELECT has_table( 'public'::name, 'timezones'::name );
SELECT has_table( 'secure'::name, 'gpg_keys'::name );

SELECT tables_are(
    'audit',
    ARRAY[ 'actions_logs', 'posts' ]
);

SELECT tables_are(
    'public',
    ARRAY[ 'countries', 'spatial_ref_sys', 'srtm', 'timezones' ]
);

SELECT tables_are(
    'secure',
    ARRAY[ 'gpg_keys' ]
);

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
