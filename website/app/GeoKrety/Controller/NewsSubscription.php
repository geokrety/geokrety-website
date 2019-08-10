<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\NewsSubscription as NewsSubscriptionModel;

class NewsSubscription extends Base {
    public function subscription(\Base $f3) {
        $subscription = $this->loadSubscription($f3);
        Smarty::assign('subscription', $subscription);
        Smarty::render('extends:base.tpl|dialog/news_subscription.tpl');
    }

    public function subscriptionFragment(\Base $f3) {
        $subscription = $this->loadSubscription($f3);
        Smarty::assign('subscription', $subscription);
        Smarty::render('dialog/news_subscription.tpl');
    }

    public function subscriptionToggle(\Base $f3) {
        $subscription = $this->loadSubscription($f3);
        $subscription->subscribed = abs((int) $subscription->subscribed - 1);
        $subscription->save();

        if ($f3->get('ERROR')) {
            \Flash::instance()->addMessage(_('Failed to update your subscriptions preferences.'), 'danger');
        }

        $f3->reroute('news_details', 'newsid='.$f3->get('PARAMS.newsid'));
    }

    private function loadSubscription(\Base $f3) {
        $subscription = new NewsSubscriptionModel();
        $subscription->load(array('news = ? AND user = ?', $f3->get('PARAMS.newsid'), CURRENT_USER));
        if ($subscription->dry()) {
            $subscription->news = $f3->get('PARAMS.newsid');
            $subscription->user = CURRENT_USER;
        }

        return $subscription;
    }
}
