<?php

require_once '__sentry.php';
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
$validationService = new \Geokrety\Service\ValidationService();

if (count($_GET) == 0 || !isset($_GET['newsid']) || !$_GET['newsid'] || !ctype_digit($_GET['newsid'])) {
    danger(_('Invalid parameters'));
    header('Location: /');
    die();
} // without parameters, we leave immediately

$smarty->assign('content_template', 'forms/news_comment.tpl');
$smarty->append('javascript', CDN_JQUERY_VALIDATION_JS);

$TYTUL = _('Comments');

$p_comment = $validationService->noHtml($_POST['comment']);
// $p_comment_esc = $_POST['comment_esc'];
// $p_icon = $_POST['icon'];
// $p_newsid = $_POST['newsid'];
$p_subscribe = $_POST['subscribe'];

$g_delete = $_GET['delete'];
$g_mode = $_GET['mode'];
$g_newsid = $_GET['newsid'];

// -----------------------------------------------------------------------------------------------

$newsR = new Geokrety\Repository\NewsRepository(\GKDB::getLink());
$news = $newsR->getById($_GET['newsid']);
$smarty->assign('news', $news);

if (is_null($news)) {
    danger(_('News post not found.'), $redirect = true);
}

$newsComments = $news->getComments();
$smarty->assign('newsComments', $newsComments);

// Save last view, so we don't notify in daily mails
if ($_SESSION['isLoggedIn']) {
    $newsSubscriptionR = new Geokrety\Repository\NewsSubscriptionRepository(\GKDB::getLink());
    $newsSubscription = $newsSubscriptionR->getByNewsIdUserId($news->id, $_SESSION['currentUser']);
    if (is_null($newsSubscription)) {
        $newsSubscription = new \Geokrety\Domain\NewsSubscription();
        $newsSubscription->newsId = $news->id;
        $newsSubscription->userId = $_SESSION['currentUser'];
        $newsSubscription->subscribed = '0';
        if (!$newsSubscription->insert()) {
            danger(_('Failed to update NewsSubscription…'));
            include_once 'smarty.php';
            die();
        }
    } else {
        if (!$newsSubscription->update()) {
            danger(_('Failed to update NewsSubscription…'));
            include_once 'smarty.php';
            die();
        }
    }
    $smarty->assign('newsSubscription', $newsSubscription);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    loginFirst();
}

// Delete news comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    if (!ctype_digit($_POST['delete'])) {
        danger(_('Invalid parameters'));
        include_once 'smarty.php';
        die();
    }
    $newsCommentR = new Geokrety\Repository\NewsCommentRepository(\GKDB::getLink());
    $newsComment = $newsCommentR->getById($_POST['delete']);

    if (is_null($newsComment)) {
        danger(_('Comment is not found!'));
        include_once 'smarty.php';
        die();
    }
    if (!$newsComment->isAuthor()) {
        danger(_('Cannot delete not your own comment'));
        include_once 'smarty.php';
        die();
    }
    if ($newsComment->delete()) {
        success(_('Comment deleted'));

        // Update News parameters
        $newsR = new Geokrety\Repository\NewsRepository(\GKDB::getLink());
        $newsR->updateNewsCountAndLastCommentDate($g_newsid);
    } else {
        danger(_('Failed to delete comment'));
    }
}

// Manage subscription
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['subscribe']) || isset($_POST['comment']))) {
    $oldSubscription = $newsSubscription->subscribed;
    // Save user's subscription
    $newsSubscription->subscribed = $p_subscribe == 'on' ? '1' : '0';
    if ($oldSubscription != $newsSubscription->subscribed) {
        if (!$newsSubscription->update()) {
            danger(_('Failed to update NewsSubscription…'));
            include_once 'smarty.php';
            die();
        }
        if ($p_subscribe == 'on') {
            success(_('You have been subscribed to the news.'));
        } else {
            success(_('You have been unsubscribed from the news.'));
        }
    }
}

// Save news comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    if ($validationService->is_whitespace($p_comment)) {
        danger(_('No comment found!'));
        include_once 'smarty.php';
        die();
    }

    // Save News comment
    $newsComment = new \Geokrety\Domain\NewsComment();
    $newsComment->newsId = $g_newsid;
    $newsComment->userId = $_SESSION['currentUser'];
    $newsComment->comment = $p_comment;
    $newsComment->icon = 0;
    if (!$newsComment->insert()) {
        danger(_('Failed to save NewsComment…'));
        include_once 'smarty.php';
        die();
    }

    // Update News counters
    $newsR = new Geokrety\Repository\NewsRepository(\GKDB::getLink());
    $newsR->updateNewsCountAndLastCommentDate($g_newsid);

    success(_('News comment saved.'));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Location: /newscomments.php?newsid='.$news->id);
    die();
}

// --------------------------------------------------------------- S

require_once 'smarty.php';
