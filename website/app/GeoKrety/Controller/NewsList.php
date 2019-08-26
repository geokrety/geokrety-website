<?php

namespace GeoKrety\Controller;

use GeoKrety\Pagination;
use GeoKrety\Service\Smarty;
use GeoKrety\Model\News;

class NewsList extends Base {
    public function get($f3) {
        $news = new News();
        $filter = array();
        $option = array('order' => 'created_on_datetime DESC');
        $subset = $news->paginate(Pagination::findCurrentPage() - 1, GK_PAGINATION_NEWS, $filter, $option);
        Smarty::assign('news', $subset);

        $pages = new Pagination($subset['total'], $subset['limit']);
        Smarty::assign('pg', $pages);

        Smarty::render('pages/news_list.tpl');
    }
}
