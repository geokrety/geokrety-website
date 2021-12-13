<?php

namespace GeoKrety\Controller\Devel;

use Exception;

/**
 * Class DatabaseReset.
 */
class DatabaseReset extends Base {
    const TABLES = [
        'gk_account_activation', 'gk_audit_logs', 'gk_awards_won', 'gk_email_activation',
        'gk_geokrety', 'gk_geokrety_rating', 'gk_mails',
        'gk_moves', 'gk_moves_comments', 'gk_news', 'gk_news_comments',
        'gk_news_comments_access', 'gk_owner_codes', 'gk_password_tokens', 'gk_pictures',
        'gk_races', 'gk_races_participants',
        'gk_statistics_counters', 'gk_statistics_daily_counters', 'gk_users',
        'gk_users_social_auth', 'gk_watched', 'gk_waypoints_gc',
        'gk_waypoints_oc', 'gk_waypoints_sync', 'sessions',
    ];

    const SEQUENCES = [
        'account_activation_id_seq', 'badges_id_seq', 'email_activation_id_seq',
        'geokrety_id_seq', 'geokrety_rating_id_seq',
        'gk_statistics_counters_id_seq',
        'gk_statistics_daily_counters_id_seq', 'gk_users_social_auth_id_seq',
        'mails_id_seq', 'move_comments_id_seq', 'moves_id_seq',
        'news_comments_access_id_seq', 'news_comments_id_seq', 'news_id_seq',
        'owner_codes_id_seq', 'password_tokens_id_seq', 'pictures_id_seq',
        'races_id_seq', 'races_participants_id_seq',
        'users_id_seq', 'watched_id_seq', 'waypoints_oc_id_seq', 'waypoints_gc_id_seq',
    ];

    /**
     * Reset the database only if in debug mode.
     *
     * @throws Exception
     */
    public function get(\Base $f3) {
        header('Content-Type: text');
        $db = $f3->get('DB');
        foreach (self::TABLES as $table) {
            $db->exec(sprintf('TRUNCATE "%s" CASCADE', $table));
        }
        foreach (self::SEQUENCES as $table) {
            $db->exec(sprintf('ALTER SEQUENCE "%s" RESTART WITH 1', $table));
        }
        echo 'OK';
    }
}
