<?php

namespace GeoKrety\Controller;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class Metrics extends Base {
    public function get(\Base $f3) {
        $registry = CollectorRegistry::getDefault();

        $renderer = new RenderTextFormat();
        $result = $renderer->render($registry->getMetricFamilySamples());

        header('Content-type: '.RenderTextFormat::MIME_TYPE);
        echo $result;
    }

    public function database_counts() {
        \GeoKrety\Service\Metrics::getOrRegisterGauge('info', 'Information about GeoKrety environment', ['version'])
            ->set(1, [GK_APP_VERSION]);

        \GeoKrety\Service\Metrics::gauge_set_sql(
            'sessions_total',
            'Sessions statistics',
            <<<'SQL'
SELECT 'total' as label, COUNT(*) AS count
FROM "sessions"

UNION

SELECT 'connected' as label, COUNT(*) AS count
FROM "sessions"
WHERE data like '%IS_LOGGED_IN|b:1%'
SQL,
            ['status']
        );

        \GeoKrety\Service\Metrics::gauge_set_sql(
            'geokrety_total',
            'Geokrety statistics',
            <<<'SQL'
SELECT map_geokrety_types(type) as label, COUNT(*) AS count
FROM "gk_geokrety"
GROUP BY type
SQL,
            ['type']
        );

        \GeoKrety\Service\Metrics::gauge_set_sql(
            'accounts_total',
            'Accounts statistics',
            <<<'SQL'
SELECT map_account_status(account_valid) as label, COUNT(*) AS count
FROM "gk_users"
GROUP BY account_valid
SQL,
            ['status']
        );

        \GeoKrety\Service\Metrics::gauge_set_sql(
            'waypoints_total',
            'Waypoints statistics',
            <<<'SQL'
SELECT provider as label, COUNT(*) AS count
FROM "gk_waypoints_oc"
GROUP BY provider

UNION

SELECT 'GC' as label, COUNT(*) AS count
FROM "gk_waypoints_oc"
SQL,
            ['provider']
        );

        \GeoKrety\Service\Metrics::gauge_set_sql(
            'pictures_total',
            'Pictures statistics',
            <<<'SQL'
SELECT map_pictures_types(type) as label, COUNT(*) AS count
FROM "gk_pictures"
GROUP BY type
SQL,
            ['type']
        );

        \GeoKrety\Service\Metrics::gauge_set_sql(
            'moves_total',
            'Moves statistics',
            <<<'SQL'
SELECT map_move_types(move_type) as label, COUNT(*) AS count
FROM "gk_moves"
GROUP BY move_type
SQL,
            ['type']
        );

        \GeoKrety\Service\Metrics::gauge_set_sql(
            'moves_comments_total',
            'Moves comments statistics',
            <<<'SQL'
SELECT map_move_comments_types(type) as label, COUNT(*) AS count
FROM "gk_moves_comments"
GROUP BY type
SQL,
            ['type']
        );

        \GeoKrety\Service\Metrics::gauge_set_sql(
            'news_total',
            'News statistics',
            <<<'SQL'
SELECT COUNT(*) AS count
FROM "gk_news"
SQL,
        );

        \GeoKrety\Service\Metrics::gauge_set_sql(
            'news_comments_total',
            'News comments statistics',
            <<<'SQL'
SELECT COUNT(*) AS count
FROM "gk_news_comments"
SQL,
        );

        \GeoKrety\Service\Metrics::gauge_set_sql(
            'watched_total',
            'Watched GeoKrety count',
            <<<'SQL'
SELECT COUNT(*) AS count
FROM "gk_watched"
SQL,
        );

        \GeoKrety\Service\Metrics::gauge_set_sql(
            'scripts',
            'Total number of scripts status',
            sprintf(<<<'SQL'
SELECT 'locks' as label, COUNT(*) AS count
FROM "scripts"
WHERE (EXTRACT(EPOCH FROM (DATE_TRUNC('MINUTE', NOW()) - DATE_TRUNC('MINUTE', locked_on_datetime)))::integer/60) >= %d
SQL, GK_SITE_CRON_LOCKED_MINUTES),
            ['status']
        );

        \GeoKrety\Service\Metrics::gauge_set_sql(
            'scripts',
            'Total number of scripts status',
            <<<'SQL'
SELECT 'acks' as label, COUNT(*) AS count
FROM "scripts"
WHERE acked_on_datetime IS NOT NULL
SQL,
            ['status']
        );
    }
}

// Metrics::getOrRegisterGauge('cron_locked_scripts', 'Count of locked script')
//    ->set(sizeof($locked_scripts));
