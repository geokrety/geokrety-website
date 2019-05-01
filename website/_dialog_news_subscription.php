<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
loginFirst();

$template = 'dialog/news_subscription.tpl';

$news_comment_id = $_GET['id'];
if (!ctype_digit($news_comment_id)) {
    echo _('Oops! Something went wrong.').' [#'.__LINE__.']';
    exit;
}
$newsR = new Geokrety\Repository\NewsRepository(\GKDB::getLink());
$news = $newsR->getById($news_comment_id);
$smarty->assign('news', $news);

if (is_null($news)) {
    echo _('Oops! Something went wrong.').' [#'.__LINE__.']';
    exit;
}

$newsSubscriptionR = new Geokrety\Repository\NewsSubscriptionRepository(\GKDB::getLink());
$newsSubscription = $newsSubscriptionR->getByNewsIdUserId($news->id, $_SESSION['currentUser']);
$smarty->assign('newsSubscription', $newsSubscription);

require_once 'smarty.php';
