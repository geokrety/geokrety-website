<?php

require_once '__sentry.php';

$smarty_cache_this_page = ($_GET['clearcache'] == 1 ? 0 : 300); // this page should be cached for n seconds, unless refresh=1 parameter exists
require_once 'smarty_start.php';

$TYTUL = _('News');

$smarty->assign('content_template', 'news.tpl');

$newsR = new Geokrety\Repository\NewsRepository(\GKDB::getLink());
$totalNews = $newsR->countTotalNews();
$smarty->assign('totalNews', $totalNews);

// Pagination total number of pages
$page = ctype_digit($_GET['page']) ? $_GET['page'] : 1;
$smarty->assign('newsPerPage', NEWS_PER_PAGE);
$max_page = ceil($totalNews / NEWS_PER_PAGE);
if ($page > $max_page) {
    $page = $max_page || 1;
}
$news_start = ($page - 1) * NEWS_PER_PAGE;

list($news, $newsCount) = $newsR->getRecent($news_start, NEWS_PER_PAGE);
$smarty->assign('news', $news);

require_once 'smarty.php';
