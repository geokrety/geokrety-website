<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
loginFirst();

$template = 'dialog/news_comment_delete.tpl';

$news_comment_id = $_GET['id'];
if (!ctype_digit($news_comment_id)) {
    echo _('Oops! Something went wrong.').' [#'.__LINE__.']';
    exit;
}
$newsCommentR = new Geokrety\Repository\NewsCommentRepository(\GKDB::getLink());
$newsComment = $newsCommentR->getById($news_comment_id);
$smarty->assign('comment', $newsComment);

require_once 'smarty.php';
