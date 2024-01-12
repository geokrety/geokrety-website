<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Base;
use GeoKrety\Model\News;
use GeoKrety\Service\Smarty;

class NewsFormBase extends Base {
    protected News $news;

    public function get(\Base $f3) {
        Smarty::render('admin/pages/news_create.tpl');
    }
}
