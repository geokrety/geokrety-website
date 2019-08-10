<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\News;
use GeoKrety\Model\NewsComment;
use GeoKrety\Model\NewsSubscription;
use GeoKrety\Validation\NewsCommentValidator;

class NewsDetails extends Base {
    public function get(\Base $f3) {
        // Load news
        $news = new News();
        $news->load(array('id = ?', $f3->get('PARAMS.newsid')));
        $news->comments->orderBy('id DESC');
        Smarty::assign('news', $news);

        // Save last view
        if (CURRENT_USER) {
            $subscription = $this->loadSubscription($f3);
            $subscription->last_read_datetime = NewsSubscription::now();
            $subscription->save();
            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to save visit.'), 'danger');
            }
            Smarty::assign('news_last_read_datetime', $subscription->last_read_datetime);
        }

        Smarty::render('pages/news_details.tpl');
    }

    public function post(\Base $f3, $params) {
        // Create the comment
        $comment = new NewsComment();
        $comment->news = $f3->get('PARAMS.newsid');
        $comment->author = CURRENT_USER;
        $comment->content = $f3->get('POST.comment');
        $comment->icon = 0;

        // Create the subscription
        $subscription = $this->loadSubscription($f3);
        $subscription->subscribed = filter_var($f3->get('POST.subscribe'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0';

        // Validate
        $commentValidator = new NewsCommentValidator();
        if ($commentValidator->validate($comment)) {
            // Save
            $subscription->save();
            $comment->save();

            if ($f3->get('ERROR')) {
                \Flash::instance()->addMessage(_('Failed to create comment.'), 'danger');
            } else {
                \Flash::instance()->addMessage(_('Your comment has been saved.'), 'success');
                $f3->reroute('news_details', 'newsid='.$f3->get('PARAMS.newsid'));
            }
        }

        // Show form
        $this->get($f3);
    }

    private function loadSubscription(\Base $f3) {
        $subscription = new NewsSubscription();
        $subscription->load(array('news = ? AND user = ?', $f3->get('PARAMS.newsid'), CURRENT_USER));

        if ($subscription->dry()) {
            $subscription->news = $f3->get('PARAMS.newsid');
            $subscription->user = CURRENT_USER;
        }

        return $subscription;
    }
}
