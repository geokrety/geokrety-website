<?php

namespace GeoKrety\Controller\Admin;

use GeoKrety\Model\News;
use GeoKrety\Service\Smarty;

class NewsCreate extends NewsFormBase {
    use \CurrentUserLoader;

    public function _beforeRoute(\Base $f3) {
        $this->news = new News();
        Smarty::assign('news', $this->news);
    }

    public function post(\Base $f3) {
        $f3->get('DB')->begin();
        $this->news->title = $f3->get('POST.title');
        $this->news->content = $f3->get('POST.content');
        $this->news->author = $this->currentUser;
        $this->news->author_name = $this->currentUser->username;

        $this->checkCsrf();

        if ($this->news->validate()) {
            try {
                $this->news->save();
            } catch (\Exception $e) {
                \Flash::instance()->addMessage(_('Failed to create the News.'), 'danger');
                $this->get($f3);
                exit;
            }
            $f3->get('DB')->commit();

            \Flash::instance()->addMessage(_('Your News has been created.'), 'success');
            $f3->reroute('@admin_news_list');
        }

        $this->get($f3);
    }
}
