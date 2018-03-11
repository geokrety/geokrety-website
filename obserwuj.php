<?php

require_once '__sentry.php';

// śćńółż

$g_id = $_GET['id'];
// autopoprawione...
$g_list = $_GET['list'];
// autopoprawione...import_request_variables('g', 'g_');

// --------- obsluga dodawania/usuwania obserwatorow ---------
if (ctype_digit($g_id)) {
    include 'templates/konfig.php';
    include 'longin_chceck.php';

    $longin_status = longin_chceck();
    $userid = $longin_status['userid'];
    if ($longin_status['plain'] == null) {
        // $smarty_cache_this_page=0; // this page should be cached for n seconds
        // include_once("smarty_start.php");
        setcookie('longin_fwd', base64_encode("/obserwuj.php?id=$g_id"), time() + 120);
        header('Location: /longin.php');
        exit;
        //$errors[] = "<a href='/longin.php?goto=$goto'>". _('Please login.') ."</a>";
        //include_once("defektoskop.php"); $TRESC = defektoskop($errors, false); include_once('smarty.php'); exit;
    }

    include 'czy_obserwowany.php';
    $czy_obserwowany = czy_obserwowany($g_id, $userid);

    $link = DBConnect();

    if ($czy_obserwowany['plain'] == 0) {
        $sql = "INSERT INTO `gk-obserwable` ( `userid`,`id` ) VALUES ('$userid', '$g_id')";
    } elseif ($czy_obserwowany['plain'] == 1) {
        $sql = "DELETE FROM `gk-obserwable` WHERE `userid` = '$userid' AND `id` = '$g_id' LIMIT 1";
    }

    if (isset($sql)) {
        mysqli_query($link, $sql);
        //if($czy_obserwowany['plain'] == 0) $TRESC = "Added to watchlist.";
        //elseif($czy_obserwowany['plain'] == 1) $TRESC = "Removed from watchlist.";

        if (strstr($_SERVER['HTTP_REFERER'], 'mypage.php') && strstr($_SERVER['HTTP_REFERER'], 'co=2')) {
            header('Location: '.$_SERVER['HTTP_REFERER']);
        } else {
            header("Location: konkret.php?id=$g_id");
        }

        exit;
    } else {
        // jezeli wlasciciel probuje "obserwowac" kreta to wroc do strony kreta
        header("Location: konkret.php?id=$g_id");
        exit;
    }
}

// --------- obsluga listowania obserwatorow ---------
else {
    if (ctype_digit($g_list)) {
        include 'templates/konfig.php';
        $link = DBConnect();

        $TRESC = "<div style='background:#ececec; width:400px; padding:15px;'>
	<div style='padding-bottom:0.5em'><strong>"._('Users watching this GeoKret:').'</strong></div>';

        $TRESC .= "<div style='max-height:200px;overflow:auto;'><ul style='margin:0;'>";
        $result = mysqli_query($link,
        "	SELECT ob.userid, us.user
							FROM `gk-obserwable` ob
							LEFT JOIN `gk-users` us ON (ob.userid = us.userid)
							WHERE `id`='$g_list'
							ORDER BY us.user"
    );
        while ($row = mysqli_fetch_array($result)) {
            list($f_user_id, $f_username) = $row;
            $TRESC .= "<li style='padding:0'><a href='mypage.php?userid=$f_user_id' onclick='$.fn.colorbox.close(); setTimeout(function(){parent.location.href=\"mypage.php?userid=$f_user_id\";},400); return false;'>$f_username</a></li>";
        }
        $TRESC .= '</ul></div></div>';
        mysqli_free_result($result);

        echo $TRESC;
        exit;
    } else {
        include_once 'defektoskop.php';
        errory_add('a cio my tu mamy??', 7, 'obserwuj');
        exit;
    }
}
