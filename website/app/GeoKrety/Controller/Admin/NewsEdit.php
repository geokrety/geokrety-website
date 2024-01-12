<?php

namespace GeoKrety\Controller\Admin;

class NewsEdit extends NewsFormBase {
    use \GeoKrety\Traits\NewsLoader;

    public function post(\Base $f3) {
        $f3->get('DB')->begin();
        $this->news->title = $f3->get('POST.title');
        $this->news->content = $f3->get('POST.content');

        $this->checkCsrf();

        if ($this->news->validate()) {
            try {
                $this->news->save();
            } catch (\Exception $e) {
                \Flash::instance()->addMessage(_('Failed to update the News.'), 'danger');
                $this->get($f3);
                exit;
            }
            $f3->get('DB')->commit();

            \Flash::instance()->addMessage(_('Your News has been updated.'), 'success');
            $f3->reroute('@admin_news_list');
        }

        $this->get($f3);
    }
}
