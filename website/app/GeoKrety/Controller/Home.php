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
            $statistics[$item['name']] = $item['value'];
        }
        Smarty::assign('stats', $statistics);

        // Load latest news
        $news = new News();
        $news = $news->find(null, ['order' => 'updated_on_datetime DESC', 'limit' => 3], GK_SITE_CACHE_TTL_LATEST_NEWS);
        Smarty::assign('news', $news);

        Smarty::render('pages/home.tpl');
        // \Flash::instance()->addMessage(print_r($statistics, true), 'success');
        // \Flash::instance()->addMessage(sprintf('It worked! %s', date('Y-m-d H:i:s')), 'success');
    }
}
