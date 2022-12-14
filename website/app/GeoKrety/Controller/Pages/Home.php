<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;
use GeoKrety\Model\News;
use GeoKrety\Model\Picture;
use GeoKrety\Model\SiteStats;
use GeoKrety\Service\Smarty;

class Home extends Base {
    public function get() {
        // Load statistics
        $siteStats = new SiteStats();
        $result = $siteStats->find(['name LIKE ?', 'stat_%'], null, GK_SITE_CACHE_TTL_STATS);
        $statistics = [];
        foreach ($result ?: [] as $item) {
            $statistics[$item['name']] = $item['value'];
        }
        Smarty::assign('stats', $statistics);

        // Load latest news
        $news = new News();
        // See https://github.com/ikkez/f3-cortex/issues/97
        $filter = ["fresher_than(created_on_datetime, ?, 'DAY')", GK_SITE_NEWS_DISPLAY_DAYS_VALIDITY];
        $news = $news->find($filter, ['order' => 'created_on_datetime DESC', 'limit' => GK_HOME_COUNT_NEWS], GK_SITE_CACHE_TTL_LATEST_NEWS);
        Smarty::assign('news', $news);

        // Load latest moves
        $move = new Move();
        $filter = ['created_on_datetime >= now() - cast(? as interval)', '10'.' DAY'];
        $moves['subset'] = $move->find($filter, ['order' => 'moved_on_datetime DESC', 'limit' => GK_HOME_COUNT_MOVES], GK_SITE_CACHE_TTL_LATEST_MOVED_GEOKRETY);
        Smarty::assign('moves', $moves);

        // Load latest GeoKrety
        $geokret = new Geokret();
        $filter = ['created_on_datetime >= now() - cast(? as interval) AND owner != ?', '10'.' DAY', null];
        $geokrety = $geokret->find($filter, ['order' => 'created_on_datetime DESC', 'limit' => GK_HOME_COUNT_RECENT_GEOKRETY], GK_SITE_CACHE_TTL_LATEST_GEOKRETY);
        Smarty::assign('geokrety', $geokrety);

        // Load latest pictures
        $picture = new Picture();
        $filter = ['uploaded_on_datetime != ? AND uploaded_on_datetime >= now() - cast(? as interval)', null, '30'.' DAY'];
        $pictures = $picture->find($filter, ['order' => 'created_on_datetime DESC', 'limit' => GK_HOME_COUNT_RECENT_PICTURES], GK_SITE_CACHE_TTL_LATEST_PICTURES);
        Smarty::assign('pictures', $pictures);

        Smarty::render('pages/home.tpl');
    }
}
