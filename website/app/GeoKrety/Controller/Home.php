<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\SiteStats;
use GeoKrety\Model\News;

class Home extends Base {
    public function get($f3) {
        // Load statistics
        $siteStats = new SiteStats();
        $result = $siteStats->find(array('name LIKE ?', 'stat_%'));
        foreach ($result as $item) {
            $statystyka[$item['name']] = $item['value'];
        }
        Smarty::assign('stats', $statystyka);

        // Load latest news
        $news = new News();
        $news = $news->find(null, ['order' => 'updated_on_datetime DESC', 'limit' => 3], GK_SITE_LATEST_NEWS_CACHE_TTL);
        Smarty::assign('news', $news);

        Smarty::render('pages/home.tpl');
        // \Flash::instance()->addMessage(print_r($statystyka, true), 'success');
        // \Flash::instance()->addMessage(sprintf('It worked! %s', date('Y-m-d H:i:s')), 'success');
    }
}
