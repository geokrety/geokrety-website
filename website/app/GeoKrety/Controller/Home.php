<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\SiteStats;
use GeoKrety\Model\News;
use GeoKrety\Model\Move;
use GeoKrety\Model\Geokret;

class Home extends Base {
    public function get($f3) {
        // Load statistics
        $siteStats = new SiteStats();
        $result = $siteStats->find(array('name LIKE ?', 'stat_%'));
        foreach ($result as $item) {
            $statistics[$item['name']] = $item['value'];
        }
        Smarty::assign('stats', $statistics);

        // Load latest news
        $news = new News();
        $news = $news->find(null, ['order' => 'created_on_datetime DESC', 'limit' => GK_HOME_COUNT_NEWS], GK_SITE_CACHE_TTL_LATEST_NEWS);
        Smarty::assign('news', $news);

        // Load latest moves
        $move = new Move();
        $moves = $move->find(null, ['order' => 'created_on_datetime DESC', 'limit' => GK_HOME_COUNT_MOVES], GK_SITE_CACHE_TTL_LATEST_MOVED_GEOKRETY);
        Smarty::assign('moves', $moves);

        // Load latest moves
        $geokret = new Geokret();
        $geokrety = $geokret->find(null, ['order' => 'created_on_datetime DESC', 'limit' => GK_HOME_COUNT_RECENT_GEOKRETY], GK_SITE_CACHE_TTL_LATEST_GEOKRETY);
        Smarty::assign('geokrety', $geokrety);

        Smarty::render('pages/home.tpl');
    }
}
