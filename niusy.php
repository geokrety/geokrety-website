<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = ($_GET['clearcache'] == 1 ? 0 : 300); // this page should be cached for n seconds, unless refresh=1 parameter exists
require_once 'smarty_start.php';

$TYTUL = _('News');

$link = DBConnect();

$txt_view_comments = _('View comments');

$userid = $longin_status['userid'];

if ($longin_status['plain'] != null) {
    $sql = "SELECT n.news_id, DATE(n.date), n.tresc, n.tytul, n.who, n.userid, n.komentarze, n.ostatni_komentarz, acc.read
			FROM `gk-news` n
			LEFT JOIN `gk-news-comments-access` acc ON (acc.news_id=n.news_id AND acc.user_id=$userid)
			ORDER BY n.date DESC
			LIMIT 60";
    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_array($result)) {
        list($newsid, $date, $tresc, $tytul, $who, $userid, $komentarze, $ostatni_komentarz, $read) = $row;
        if (($read != null) && ($ostatni_komentarz > $read)) {
            $styl = "class='bold'";
        } else {
            $styl = '';
        }
        if ($userid != 0) {
            $author = "<a href='mypage.php?userid=$userid'>$who</a>";
        } else {
            $author = "<a href='kontakt.php'>$who</a>";
        }
        $TRESC .= "<div class='alignleft50'><span class='news_title'>$tytul</span></div><div class='alignright50 xs'><a href='newscomments.php?newsid=$newsid' $styl>$txt_view_comments ($komentarze)</a> - <i>$date ($author)</i></div><div class='news_body'>$tresc</div>";
    }
} else {
    $sql = 'SELECT n.news_id, DATE(n.date), n.tresc, n.tytul, n.who, n.userid, n.komentarze
			FROM `gk-news` n
			ORDER BY n.date DESC
			LIMIT 60';
    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_array($result)) {
        list($newsid, $date, $tresc, $tytul, $who, $userid, $komentarze) = $row;
        $TRESC .= "<div class='alignleft50'><span class='news_title'>$tytul</span></div><div class='alignright50 xs'><a href='newscomments.php?newsid=$newsid'>$txt_view_comments ($komentarze)</a> - <i>$date (<a href='mypage.php?userid=$userid'>$who</a>)</i></div><div class='news_body'>$tresc</div>";
    }
}

mysqli_close($link);
$link = null; // Prevent warning from smarty.php

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
