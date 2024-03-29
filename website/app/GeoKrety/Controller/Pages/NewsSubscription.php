<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\NewsSubscription as NewsSubscriptionModel;
use GeoKrety\Service\Smarty;

class NewsSubscription extends Base {
    public function subscription(\Base $f3) {
        $subscription = $this->loadSubscription($f3);
        Smarty::assign('subscription', $subscription);
        Smarty::render('extends:full_screen_modal.tpl|dialog/news_subscription.tpl');
    }

    public function subscriptionFragment(\Base $f3) {
        $subscription = $this->loadSubscription($f3);
        Smarty::assign('subscription', $subscription);
        Smarty::render('extends:base_modal.tpl|dialog/news_subscription.tpl');
    }

    public function subscriptionToggle(\Base $f3) {
        $this->checkCsrf('subscription');
        $subscription = $this->loadSubscription($f3);
        $subscription->subscribed = abs((int) $subscription->subscribed - 1);
        try {
            $subscription->save();
            if ($subscription->subscribed) {
                \Sugar\Event::instance()->emit('news.subscribed', $subscription->news);
                \Flash::instance()->addMessage(_('You will now receive updates on new comments.'), 'success');
            } else {
                \Sugar\Event::instance()->emit('news.unsubscribed', $subscription->news);
                \Flash::instance()->addMessage(_('You will not receive updates anymore.'), 'success');
            }
        } catch (\Exception $e) {
            \Flash::instance()->addMessage(_('Failed to update your subscriptions preferences.'), 'danger');
        }
        $f3->reroute('@news_details', 'newsid='.$f3->get('PARAMS.newsid'));
    }

    private function loadSubscription(\Base $f3) {
        $subscription = new NewsSubscriptionModel();
        $subscription->load(['news = ? AND author = ?', $f3->get('PARAMS.newsid'), $f3->get('SESSION.CURRENT_USER')]);
        if ($subscription->dry()) {
            $subscription->news = $f3->get('PARAMS.newsid');
            $subscription->author = $f3->get('SESSION.CURRENT_USER');
        }

        return $subscription;
    }
}
