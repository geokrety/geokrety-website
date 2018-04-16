<?php

require_once '__sentry.php';

// Main page of GeoKrety śćńółżł
// parametry:
// debugecho=1 -> wyswietla stopery

$speedtest_index = $_GET['debugecho'];
$speedtest_index_maxtime = 5;
$debugecho_index = $_GET['debugecho'];
if ($speedtest_index) {
    include_once 'speedtest.php';
    $st_index = new SpeedTest();
    include_once 'defektoskop.php';
}

// ***************
if ($speedtest_index) {
    $ST1 = 'start';
    $ST2 = $st_index->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_index_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_index) {
        echo $ST3.'<br/>';
    }
}
// ***************

// smarty cache
$smarty_cache_this_page = ($_GET['debugecho'] == 1 ? 0 : 600); // this page should be cached for n seconds, unless debugecho=1 parameter exists
$smarty_cache_this_page = 0;
require_once 'smarty_start.php';

$TYTUL = _('Home');
$HEAD = '<meta http-equiv="Cache-Control" content="max-age=600"/>';

require_once 'szukaj_kreta.php';
require_once 'recent_moves.php';
require_once 'recent_pictures.php';
require_once 'ulicznik.php'; //counter

// ***************
if ($speedtest_index) {
    $ST1 = 'includy';
    $ST2 = $st_index->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_index_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_index) {
        echo $ST3.'<br/>';
    }
}
// ***************

$link = DBConnect();

$ulicznik = ulicznik('index');

$OGON .= '<script type="text/javascript" src="'.$config['ajaxtooltip.js'].'"></script>';
$OGON .= '<script type="text/javascript" src="'.CONFIG_CDN_LIBRARIES.'/lytebox/lytebox.min.js"></script>';
$HEAD .= '<link rel="stylesheet" href="'.CONFIG_CDN_LIBRARIES.'/lytebox/lytebox.css" type="text/css" media="screen" />';
$HEAD .= '<style type="text/css">.temptip TD{font-size: 8pt; padding:0px 2px 0px 2px; background: lightyellow;}</style>';

// -------------------------------------- statystyki podstawowe ------------------------------- //

$result = mysqli_query($link, "SELECT * FROM `gk-wartosci` WHERE `name` LIKE 'stat_%'");
while ($row = mysqli_fetch_assoc($result)) {
    $statystyka[$row['name']] = $row['value'];
}
mysqli_free_result($result);

$OGON .= "<script>
$(function () {
  $('#nr[maxlength]').maxlength({
    warningClass: \"label label-danger\",
    limitReachedClass: \"label label-success\",
  });
})
</script>\n";

$TRESC .= '
<h2>'._('Welcome to GeoKrety.org!').'</h2>
<div class="row">
  <div class="col-md-9">
    <div class="panel panel-default">
      <div class="panel-body">
        '._('This service is similar to TravelBug(TM) or GeoLutins and aims at tracking things you put to geocache containers... <a href="help.php#about">read more...</a>').'
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-body">
        <strong>'.$statystyka['stat_geokretow'].'</strong>'.
        _('registered GeoKrets').', <strong>'.$statystyka['stat_geokretow_zakopanych'].'</strong> '._('GeoKrets hidden').' <strong>'.$statystyka['stat_userow'].'</strong> '._('users').
        '<br />'.sprintf(_('<strong>%d km</strong> done by all GeoKrets (it is %.2f x distance from the Earth to the Moon, %.2f x the Earth equatorial circumference and %.5f x the distance from the Earth to the Sun).'), $statystyka['stat_droga'], $statystyka['stat_droga_ksiezyc'], $statystyka['stat_droga_obwod'], $statystyka['stat_droga_slonce']).'
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="panel panel-default">
      <div class="panel-body">
        <strong>'._('If you found a GeoKret, enter the tracking code here:').'</strong>
        <form name="formularz" action="ruchy.php" method="get">
          <input type="text" name="nr" id="nr" size="6" maxlength="6">
          <input type="submit" value="'._('Go!').'">
        </form>
        <span class="male">('._('no need to register!').')</span>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-body">
        <a href="https://sharetodiaspora.github.io/?title=Geokrety&amp;url='.$config['adres'].'" target="_blank" title="Share with Diaspora*" class="btn">
          <img src="'.CONFIG_CDN_IMAGES.'/icons/diaspora.png" style="border: 0px solid;" alt="diaspora" />
        </a>
        <a href="https://plus.google.com/share?url='.$config['adres'].'" target="_blank" title="Share on Google+" class="btn">
          <img src="'.CONFIG_CDN_IMAGES.'/icons/gplus-16.png" alt="Share on Google+"/>
        </a>
        <a href="https://pinterest.com/pin/create/button/?url='.$config['adres'].'&amp;description=Geokrety" target="_blank" title="Share on Pinterest" class="btn">
          <img src="'.CONFIG_CDN_IMAGES.'/icons/pinterest16.png" alt="Share on Pinterest"/>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u='.$config['adres'].'&amp;title=Geokrety" target="_blank" title="Share with Facebook - not sure if this works" class="btn">
          <img src="'.CONFIG_CDN_IMAGES.'/icons/facebook16.png" style="border: 0px solid;" alt="fb" />
        </a>
      </div>
    </div>
  </div>
</div>
';

// ***************
if ($speedtest_index) {
    $ST1 = 'liczniki';
    $ST2 = $st_index->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_index_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_index) {
        echo $ST3.'<br/>';
    }
}
// ***************

// -------------------------------------- news ------------------------------- //

$TRESC .= '<h2>'._('News').'</h2>';

$sql = 'SELECT DATE(`date`), `tresc`, `tytul`, `who`, `userid`, `komentarze`, `news_id` FROM `gk-news` ORDER BY `date` DESC LIMIT 2';
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($date, $tresc, $tytul, $who, $userid, $komentarze, $newsid) = $row;
    $TRESC .= '<div class="panel panel-default">
      <div class="panel-heading">
        <div class="panel-title pull-left">
          <h3 class="panel-title">'.$tytul.'</h3>
        </div>
        <div class="panel-title pull-right">
          <a href="newscomments.php?newsid='.$newsid.'">'._('Comments').' <span class="badge">'.$komentarze.'</span></a>
          <i>
            '.$date.' ('.($userid == 0 ? $who : '<a href="mypage.php?userid='.$userid.'">'.$who.'</a>').')
          </i>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-body">'.$tresc.'</div>
    </div>';
}
unset($date, $tresc, $tytul, $who, $userid, $row);
// ***************
if ($speedtest_index) {
    $ST1 = 'news';
    $ST2 = $st_index->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_index_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_index) {
        echo $ST3.'<br/>';
    }
}
// ***************

// -------------------------------------- recent moves ------------------------------- //

//$TRESC .= recent_moves("WHERE (gk.typ = '0' OR gk.typ = '1')", 7);
$TRESC .= recent_moves(
    '', 7, '', "
		SELECT	ru.ruch_id, ru.id, ru.lat, ru.lon, ru.country, ru.waypoint, ru.droga, ru.data, ru.user,
				ru.koment, ru.logtype, ru.username, us.user, gk.nazwa, gk.typ, gk.owner, pic.plik, ru.zdjecia
		FROM
			(SELECT * FROM `gk-ruchy` r1 ORDER BY r1.ruch_id DESC LIMIT 50) ru
		INNER JOIN `gk-users` us ON (ru.user = us.userid)
		INNER JOIN `gk-geokrety` gk ON (ru.id = gk.id)
		LEFT JOIN `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
		WHERE (gk.typ != '2')
		ORDER BY ru.ruch_id DESC limit 7
"
);
$TRESC .= '<div style="margin-top: 10px; text-align: right">'."<a href='mapki/google_static_logs.png' class='att_js' title='ajax|2|mapki/google_static_logs.png'>"._('Recent logs on the map').'</a>'.'</div>';
// ***************
if ($speedtest_index) {
    $ST1 = 'recent moves';
    $ST2 = $st_index->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_index_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_index) {
        echo $ST3.'<br/>';
    }
}
// ***************

// -------------------------------------- recent pictures ------------------------------- //

$TRESC .= '<h2>'._('Recently uploaded images').'</h2><table><tr><td>';
$TRESC .= recent_pictures('LIMIT 10');
$TRESC .= '</td></tr><tr><td align="right"><a href="galeria.php">'._('Photo gallery').' >>></a></td></tr></table>';
// ***************
if ($speedtest_index) {
    $ST1 = 'pictures';
    $ST2 = $st_index->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_index_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_index) {
        echo $ST3.'<br/>';
    }
}
// ***************

// -------------------------------------- recent geokrets ------------------------------- //

$TRESC .= szukaj_kreta(' WHERE `gk-geokrety`.`owner` > 0 ', 7, _('Recently registered GeoKrets'), '');
// ***************
if ($speedtest_index) {
    $ST1 = 'geokrets';
    $ST2 = $st_index->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_index_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_index) {
        echo $ST3.'<br/>';
    }
}
// ***************

// ---- kto online ----//

$TRESC .= '<div style="margin-top: 1em;" class="xs">'._('Online users').': ';

$link->query("SET time_zone = '+0:00'");
$sql = 'SELECT `user`, `userid` FROM `gk-users` WHERE `ostatni_login` > DATE_SUB(NOW(), INTERVAL 5 MINUTE)';
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($user, $userid) = $row;
    $TRESC .= "<a href=\"/mypage.php?userid=$userid\">$user</a> ";
}

$TRESC .= '<p>
<img src="'.CONFIG_CDN_IMAGES.'/icons/question.png" alt="down" width="16" height="16" />
<a href="https://crwd.in/geokrety">'._('Join our translation team').'</a> | <a href="lang.php?lang=zu_ZA.UTF-8">'._('Suggest a better translation - inline')."</a> ($lang)</p><p class='male' align='right'>[$ulicznik[0]] [$ulicznik[1] "._('visits/day').']</p>';
$TRESC .= '</div>';
// ***************
if ($speedtest_index) {
    $ST1 = 'kto online';
    $ST2 = $st_index->stop_show_start();
    $ST3 = 'ST='.$ST2.'s - '.$ST1;
    if ($ST2 > $speedtest_index_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_index) {
        echo $ST3.'<br/>';
    }
}
// ***************

//$TRESC .= '<img src="http://achjoj.info/znak.php?nr=4" alt="x" border="0" height="1" width="1" />';
//$TRESC .= '<img src="'.CONFIG_CDN_IMAGES.'/log-icons/0/4.png" alt="" width="37" height="37" />';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
//include_once("gk_cache_end.php");
