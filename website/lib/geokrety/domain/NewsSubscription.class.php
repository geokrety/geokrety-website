<?php

namespace Geokrety\Domain;

class NewsSubscription extends AbstractObject {
    public $newsId;
    public $userId;
    public $read;
    public $post;
    public $subscribed;


    public function insert() {
        $newsSubscriptionR = new \Geokrety\Repository\NewsSubscriptionRepository(\GKDB::getLink());
        return $newsSubscriptionR->insertNewsSubscription($this);
    }

    public function update() {
        $newsSubscriptionR = new \Geokrety\Repository\NewsSubscriptionRepository(\GKDB::getLink());
        return $newsSubscriptionR->updateNewsSubscription($this);
    }
}
