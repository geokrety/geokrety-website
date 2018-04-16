<?php

require_once '__sentry.php';

if (count($_GET) == 0) {
    exit;
} //bez parametow od razu wychodzimy

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

function aktualizuj_ilosc_komentarzy_dla_newsa($news_id)
{
    // ----- Check if db object is present, if not create one -----
    if (is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------
    $sql = "UPDATE `gk-news` ns
	SET komentarze = (SELECT count(*) FROM `gk-news-comments` co WHERE co.news_id = '$news_id')
	WHERE ns.news_id='$news_id'";
    $db->exec_num_rows($sql, $num_rows, 0);
}
function aktualizuj_ostatni_komentarz_dla_newsa($news_id)
{
    // ----- Check if db object is present, if not create one -----
    if (is_object($GLOBALS['db']) && get_class($GLOBALS['db']) === 'db') {
        $db = $GLOBALS['db'];
    } else {
        include_once 'db.php';
        $db = new db();
    }
    // ------------------------------------------------------------
    $sql = "UPDATE `gk-news` ns
	SET ostatni_komentarz = (SELECT co.date FROM `gk-news-comments` co WHERE co.news_id = '$news_id' ORDER BY comment_id DESC LIMIT 1)
	WHERE ns.news_id='$news_id'";
    $db->exec_num_rows($sql, $num_rows, 0);
}

$userid = $longin_status['userid'];

require_once 'db.php';
$db = new db();

$TYTUL = _('Comments');
$OGON = '<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
<script type="text/javascript" src="'.$config['funkcje.js'].'"></script>'."\n";     // character counters

// niezalogowani out?
// if( $longin_status['plain'] == NULL )
// {
    // $errors[] = "<a href='/longin.php'>". _('Please login.') ."</a>";
    // include_once("defektoskop.php");
    // $TRESC = defektoskop($errors, false);
    // include_once('smarty.php');
    // exit;
// }
$loggedin = ($longin_status['plain'] != null);

$p_comment = $_POST['comment'];
// autopoprawione...
$p_comment_esc = $_POST['comment_esc'];
// autopoprawione...
$p_icon = $_POST['icon'];
// autopoprawione...
$p_newsid = $_POST['newsid'];
// autopoprawione...
$p_subscribed = $_POST['subscribed'];
// autopoprawione...import_request_variables('p', 'p_');

$g_delete = $_GET['delete'];
// autopoprawione...
$g_mode = $_GET['mode'];
// autopoprawione...
$g_newsid = $_GET['newsid'];
// autopoprawione...import_request_variables('g', 'g_');

// -----------------------------------------------------------------------------------------------
// jezeli znajdziemy jakies nieznane  parametry to logujemy informacje do errorow
// to tak dla bezpieczenstwa, za jakis czas mozna to wyremowac :)
$allowed_request_variables = array('NEWSID', 'COMMENT', 'SUBSCRIBED', 'SUBMIT', 'MODE', 'DELETE', 'CONFIRMED');
foreach ($_GET as $var => $value) {
    if (!in_array(strtoupper($var), $allowed_request_variables)) {
        $bad_request_variables[] = "G|$var|";
    }
}
foreach ($_POST as $var => $value) {
    if (!in_array(strtoupper($var), $allowed_request_variables)) {
        $bad_request_variables[] = "P|$var|";
    }
}
if (isset($bad_request_variables)) {
    $bad_request_variables[] = '<b><u>BAD_REQUEST_VARIABLES!!</u></b>';
    include_once 'defektoskop.php';
    defektoskop($bad_request_variables);
}
unset($var, $value);
// -----------------------------------------------------------------------------------------------

if (isset($p_comment)) {
    $p_comment = trim($p_comment);
}

// dla zwyklego wejscia ale takze dla postowania:
if (ctype_digit($g_newsid)) {
    $now = date('Y-m-d H:i:s');

    //najpierw pobieramy informacje o newsie
    $sql = "SELECT DATE(`date`), `tresc`, `tytul`, `who`, `userid`, `komentarze`
			FROM `gk-news`
			WHERE `news_id` = '$g_newsid' LIMIT 1";

    $row = $db->exec_fetch_row($sql, $num_rows, 0, 'news post not found', 7, 'NEWS_NOT_FOUND');
    if ($num_rows <= 0) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop(_('News post not found.').' [#'.__LINE__.']', false);
        include_once 'smarty.php';
        exit;
    }
    list($f_date, $f_tresc, $f_tytul, $f_who, $f_userid, $f_komentarze) = $row;

    // ------------------------------

    // sprawdzamy czy istnieje wpis dla danego usera i jaka wartosc ma pole subskrybcji
    $user_row_exists = false;
    $user_is_subscribed = false;
    if ($loggedin) {
        $sql = "SELECT subscribed FROM `gk-news-comments-access` WHERE news_id='$g_newsid' AND user_id='$userid'";
        $row = $db->exec_fetch_row($sql, $num_rows, 1, '', 7, 'ERROR');
        if ($num_rows > 0) {
            $user_row_exists = true;
            $user_is_subscribed = ($row[0] == '1');
        }
    }

    // obsluga dodawania komentarzy
    if ($loggedin) {
        if (isset($p_comment)) {
            include_once 'czysc.php';
            $p_comment_esc = parse_bbcode($p_comment);
        }
        if (($p_newsid == $g_newsid) && (!empty($p_comment_esc))) {
            $p_icon = 0;
            if (empty($p_subscribed)) {
                $p_subscribed = 0;
            } // empty jest jak checkbox jest pusty

            $sql = "INSERT INTO `gk-news-comments` (`news_id`, `user_id`, `date`, `comment`, `icon`)
					  VALUES ('$p_newsid', '$userid', '$now', '$p_comment_esc', '$p_icon')";
            if ($db->exec_num_rows($sql) <= 0) {
                include_once 'defektoskop.php';
                $TRESC = defektoskop('Error.'.' [#'.__LINE__.']', true);
                include_once 'smarty.php';
                exit;
            }

            aktualizuj_ilosc_komentarzy_dla_newsa($p_newsid);
            aktualizuj_ostatni_komentarz_dla_newsa($p_newsid);

            // aktualizacja czasow przegladania/postowania
            if ($user_row_exists) {
                $sql = "UPDATE `gk-news-comments-access`
						SET `read`='$now', `subscribed`='$p_subscribed'
						WHERE news_id='$p_newsid' AND user_id='$userid'";
                $db->exec_num_rows($sql, $num_rows, 0);
            } else { //user's first post under this news post
                $sql = "INSERT INTO `gk-news-comments-access`
						(`news_id` ,`user_id` ,`read`, `subscribed`)
						VALUES ('$p_newsid', '$userid', '$now', '$p_subscribed')";
                $db->exec_num_rows($sql, $num_rows, 0);
            }

            // pobieramy nowa ilosc komentarzy
            $sql = "SELECT `komentarze` FROM `gk-news` WHERE `news_id` = $p_newsid LIMIT 1";
            $f_komentarze = $db->exec_scalar($sql, $num_rows, 0);

            // uaktualniamy zmienna subscribed
            $user_is_subscribed = $p_subscribed;

            //BIG BROTHER (chwilowy)
            include_once 'defektoskop.php';
            errory_add('NEW NEWS COMMENT', 7);
        } else {
            if ($g_mode == 'subscribe') {
                $p_subscribed = '1';
                $set_subs = ", `subscribed`='1'";
            }
            if ($g_mode == 'unsubscribe') {
                $p_subscribed = '0';
                $set_subs = ", `subscribed`='0'";
            }
            if ($user_row_exists) {
                $sql = "UPDATE `gk-news-comments-access`
						SET `read`='$now' $set_subs
						WHERE news_id='$g_newsid' AND user_id='$userid'";
                $db->exec_num_rows($sql, $num_rows, 0);

                if ($g_mode == 'unsubscribe') {
                    // $sql="SELECT count(news_id) FROM `gk-news-comments`
                    // WHERE news_id='$g_newsid' AND user_id='$userid'";
                    // if ($db->exec_num_rows($sql,$num_rows,1),0)==0)
                    // {
                    $sql = "DELETE FROM `gk-news-comments-access`
								WHERE news_id='$g_newsid' AND user_id='$userid'";
                    $db->exec_num_rows($sql, $num_rows, 1);
                    //}
                }
            } else {
                if ($g_mode == 'subscribe') { //jezeli nie ma jeszcze wpisu to dodajemy tylko gdy ktos wejdzie przez link subskrybuj
                    $sql = "INSERT INTO `gk-news-comments-access`
							(`news_id` ,`user_id` ,`read` ,`subscribed`)
							VALUES ('$g_newsid', '$userid', '$now', '$p_subscribed')";
                    $db->exec_num_rows($sql, $num_rows, 0);
                }
            }

            if (isset($set_subs)) {
                header("Location: newscomments.php?newsid=$g_newsid");
                exit;
            }
        }
    } //if $loggedin

    // ------------------------------

    if ($user_is_subscribed) {
        $subscribe_or_not1 = 'unsubscribe';
        $subscribe_or_not2 = _('Unsubscribe from this news post');
    } else {
        $subscribe_or_not1 = 'subscribe';
        $subscribe_or_not2 = _('Subscribe to this news post');
    }
    $subscribe_link = "<div style='width:100%;text-align:center;'><a href='newscomments.php?newsid=$g_newsid&mode=$subscribe_or_not1'>$subscribe_or_not2</a></div>";

    $TRESC .= "<div class='alignleft50'><span class='news_title'>$f_tytul</span></div><div class='alignright50 xs'>Comments ($f_komentarze) - <i>$f_date (<a href='mypage.php?userid=$f_userid'>$f_who</a>)</i></div><div class='news_body'>$f_tresc</div>";

    $TRESC .= '<div align="right"><g:plusone size="medium" href="/newscomments.php?newsid='.$g_newsid.'"></g:plusone></div>';

    if ($user_is_subscribed) {
        $checked = 'checked';
    }

    if ($loggedin) {
        $TRESC .= "$subscribe_link";

        $TRESC .= "<div style='padding:0.5em 0 1em 0;'>"._('Write new comment').":<form name='newscomment' action='".$_SERVER['PHP_SELF']."?newsid=$g_newsid' method='post' >
		<textarea id='poledoliczenia' name='comment' rows='3' style='width:100%' onkeyup='zliczaj(1000)'></textarea>
		<input type='hidden' name='newsid' value='$g_newsid' />
		<span style='float:right'>"._('characters left').": <input id='licznik' disabled='disabled' type='text' size='3' name='licznik' /></span>
		<input type='checkbox' name='subscribed' value='1' $checked /> "._('Subscribe to this news post')."<br />
		<input type='submit' name='submit' value='"._('Post comment')."' />
		</div><br/>";
    } else {
        $TRESC .= "<div style='width:100%;text-align:center;margin:2em;'><b><a href='/longin.php'>You must be logged in to post comments.</a></b></div>";
    }

    // ------------------------------

    include_once 'recent_newscomments_fn.php';
    $tmp = recent_newscomments("WHERE co.news_id='$g_newsid'", '', '', 0, 0);

    if (empty($tmp)) {
        $tmp = _('There are no comments for this post.');
    }
    $TRESC .= $tmp;

    $TRESC .= file_get_contents('ribbon_beta.html');
} elseif (ctype_digit($g_delete)) {
    if (!$loggedin) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop("<a href='/longin.php'>"._('Please login.').'</a>', false);
        include_once 'smarty.php';
        exit;
    }

    $sql = "SELECT news_id, user_id, comment FROM `gk-news-comments` WHERE `comment_id` = '$g_delete' LIMIT 1";
    $row = $db->exec_fetch_row($sql, $num_rows, 0);
    if ($num_rows <= 0) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop('Comment not found.'.' [#'.__LINE__.']', true);
        include_once 'smarty.php';
        exit;
    }

    list($f_news_id, $f_user_id, $f_comment) = $row;

    if ($userid == $f_user_id || in_array($userid, $config['superusers'])) {
        if ($db->exec_num_rows("DELETE FROM `gk-news-comments` WHERE `comment_id` = '$g_delete' LIMIT 1") > 0) {
            aktualizuj_ilosc_komentarzy_dla_newsa($f_news_id);
            aktualizuj_ostatni_komentarz_dla_newsa($f_news_id);
        }
    } else {
        include_once 'defektoskop.php';
        errory_add('<b>No permision to delete this newscomment</b>', 100);
    }

    //BIG BROTHER (chwilowy)
    include_once 'defektoskop.php';
    errory_add("News comment deleted: [$f_comment]", 0);

    header("Location: newscomments.php?newsid=$f_news_id");
    exit;
} else {
    include_once 'defektoskop.php';
    errory_add('<b>so how did this poor thing get here?</b>', 7, 'HOW?');
}

// --------------------------------------------------------------- S

require_once 'smarty.php';
