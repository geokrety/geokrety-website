<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\News;

class NewsList extends Base {
    public function get($f3) {
        $news = new News();
        $news = $news->find(null, ['order' => 'updated_on_datetime DESC'], GK_SITE_LATEST_NEWS_CACHE_TTL);
        Smarty::assign('news', $news);
        Smarty::render('pages/news_list.tpl');
    }
}
