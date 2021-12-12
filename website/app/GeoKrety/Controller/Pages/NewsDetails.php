<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\News;
use GeoKrety\Model\NewsComment;
use GeoKrety\Model\NewsSubscription;
use GeoKrety\Service\Smarty;

class NewsDetails extends Base {
    private NewsComment $comment;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);

        $comment = new NewsComment();
        $this->comment = $comment;
        Smarty::assign('comment', $this->comment);
    }

    public function get(\Base $f3) {
        // Load news
        $news = new News();
        $news->filter('comments', null, ['order' => 'id DESC']);
        $news->load(['id = ?', $f3->get('PARAMS.newsid')]);
        Smarty::assign('news', $news);

        // Save last view
        if ($f3->get('SESSION.CURRENT_USER')) {
            $subscription = $this->loadSubscription($f3);
            $subscription->touch('last_read_datetime');
            $subscription->save();
            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to record read datetime.'), 'danger');
            }
            Smarty::assign('news_last_read_datetime', $subscription->last_read_datetime);
        }

        Smarty::render('pages/news_details.tpl');
    }

    public function post(\Base $f3) {
        // Create the comment
        $comment = $this->comment;
        $comment->news = $f3->get('PARAMS.newsid');
        $comment->author = $f3->get('SESSION.CURRENT_USER');
        $comment->content = $f3->get('POST.content');
        $comment->icon = 0;

        // Create the subscription
        $subscription = $this->loadSubscription($f3);
        $subscription->subscribed = filter_var($f3->get('POST.subscribe'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0';

        // Check Csrf
        $this->checkCsrf();

        // Validate
        if ($comment->validate()) {
            // Save
            $subscription->save();
            $comment->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Error while saving comment.'), 'danger');
            } else {
                \Flash::instance()->addMessage(_('Your comment has been saved.'), 'success');
                \Sugar\Event::instance()->emit('news-comment.created', $comment);
                $f3->reroute('@news_details', 'newsid='.$f3->get('PARAMS.newsid'));
            }
        }

        // Show form
        $this->get($f3);
    }

    private function loadSubscription(\Base $f3): NewsSubscription {
        $subscription = new NewsSubscription();
        $subscription->load(['news = ? AND author = ?', $f3->get('PARAMS.newsid'), $f3->get('SESSION.CURRENT_USER')]);

        if ($subscription->dry()) {
            $subscription->news = $f3->get('PARAMS.newsid');
            $subscription->author = $f3->get('SESSION.CURRENT_USER');
            $subscription->subscribed = false;
        }

        return $subscription;
    }
}
