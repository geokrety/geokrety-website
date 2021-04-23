-- Start transaction and plan the tests.
BEGIN;
SELECT plan(31);

-- Run the tests.
SELECT has_table( 'gk_moves' );
SELECT has_table( 'gk_users' );
SELECT has_table( 'gk_account_activation' );
SELECT has_table( 'gk_badges' );
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

-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
