<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Controller\Base;
use GeoKrety\Service\Smarty;
use GeoKrety\Traits\NewsLoader;

class NewsView extends Base {
    use NewsLoader;

    public function get(\Base $f3) {
        Smarty::render('dialog/admin_news_view.tpl');
    }
}
