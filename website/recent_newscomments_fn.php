<?php

// display recent news comments

function recent_newscomments($where = '', $title = '', $zapytanie = '', $shownewstitles = 0, $emailversion = 0)
{
    include 'templates/konfig.php';    // config

    $word_comments = _('View all comments');

    if ($title != '') {
        $title = "<h2>$title</h2>";
    }

    if ($emailversion) {
        $prefix_adresu = $config['adres'];
    } else {
        include_once 'longin_chceck.php';
        $longin_status = longin_chceck();
        $userid = $longin_status['userid'];
        $prefix_adresu = '';
    }

    if ($zapytanie == '') {
        $zapytanie = "SELECT co.comment_id, co.news_id, co.icon, co.comment, co.date, us.userid, us.user, news.tytul, news.userid, news.who, news.komentarze, DATE(news.date)
				FROM `gk-news-comments` co
				LEFT JOIN `gk-users` us ON (co.user_id = us.userid)
				LEFT JOIN `gk-news` news ON (co.news_id = news.news_id)
				$where
				ORDER BY news_id DESC, comment_id ASC";
    }

    $link = DBConnect();
    $result = mysqli_query($link, $zapytanie);

    while ($row = mysqli_fetch_array($result)) {
        list($f_comment_id, $f_news_id, $f_type, $f_comment, $f_date, $f_user_id, $f_username, $f_title, $f_news_userid, $f_who, $f_komentarze, $f_news_date) = $row;

        $imgicon = "<img src='".CONFIG_CDN_IMAGES."/note10.png' alt='*'>";
        $datetime = strftime('%Y-%m-%d %H:%M', strtotime($f_date));

        // strip long comments:
        $comment = $f_comment;
        //if(mb_strlen($comment) > 550) { $comment = mb_substr($comment, 0, 500) . "(...)"; }

        //if ($f_user_id==1 || $f_user_id==6262) $link_class = "class='bold'" ; else $link_class='';
        $link_class = '';

        $newstitle = '';
        if ($shownewstitles && ($news_id != $f_news_id)) {
            $news_id = $f_news_id;
            if ($shownewstitles) {
                $newstitle = "\n<div style='margin-top:30px'><div class='alignleft50'><span class='news_title'>$f_title</span></div><div class='alignright50 xs'><a href='$prefix_adresu/newscomments.php?newsid=$f_news_id' $styl>$word_comments ($f_komentarze)</a> - <i>$f_news_date (<a href='$prefix_adresu/mypage.php?userid=$f_news_userid'>$f_who</a>)</i></div></div><br/><br/>";
            }
        }

        if ($emailversion) {
            $TRESC .= $newstitle;
            $TRESC .= "\n<div class='comment_title'><div class='alignleft50'>$imgicon <a $link_class href='$prefix_adresu/mypage.php?userid=$f_user_id'>$f_username</a></div><div class='alignright50 xs szare'>$datetime $delete_link</div><div style='clear:both';></div></div>";
            $TRESC .= "\n<div class='comment_body'>$comment</div>";
        } else {
            if (in_array($userid, $config['superusers']) || $userid == $f_user_id) {
                $delete_link = "&nbsp;<a href='$prefix_adresu/newscomments.php?delete=$f_comment_id' onclick='return CzySkasowac(this, \"this comment?\")'><img src='".CONFIG_CDN_IMAGES."/delete10.png' class='textalign10' alt='delete' width='10' height='10' border='0' /></a>";
            } else {
                $delete_link = '';
            }

            $TRESC .= $newstitle;
            // $TRESC .= "\n<div class='xs'>$imgicon <i>Comment by: <a $link_class href='$prefix_adresu/mypage.php?userid=$f_user_id'>$f_username</a> - $f_date</i>$delete_link</div>";
            // $TRESC .= "\n<div class='news_body'>$comment</div>";

            $TRESC .= "\n<div class='comment_title'><div class='alignleft50'>$imgicon <a $link_class href='$prefix_adresu/mypage.php?userid=$f_user_id'>$f_username</a></div><div class='alignright50 xs szare'>$datetime $delete_link</div><div style='clear:both';></div></div>";
            $TRESC .= "\n<div class='comment_body'>$comment</div>";
        }
    }

    return "\n$TRESC";
}
