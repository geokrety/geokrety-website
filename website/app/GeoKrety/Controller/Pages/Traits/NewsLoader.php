<?php

namespace GeoKrety\Traits;

use GeoKrety\Model\News;
use GeoKrety\Service\Smarty;

trait NewsLoader {
    protected News $news;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);

        if (!is_numeric($f3->get('PARAMS.newsid'))) {
            $f3->error(404, _('This news does not exist.'));
        }

        $news = new News();
        $news->load(['id = ?', $f3->get('PARAMS.newsid')]);
        if ($news->dry()) {
            $f3->error(404, _('This news does not exist.'));
        }
        $this->news = $news;
        Smarty::assign('news', $news);

        if (method_exists($this, '_beforeRoute')) {
            $this->_beforeRoute($f3);
        }
    }
}
