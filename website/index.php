<?php

require_once '__sentry.php';

try {
    // smarty cache
    $smarty_cache_this_page = 0;
    require_once 'smarty_start.php';

    $TYTUL = _('Home');
    $HEAD = '<meta http-equiv="Cache-Control" content="max-age=600"/>';

    require_once 'szukaj_kreta.php';
    require_once 'recent_moves.php';
    require_once 'recent_pictures.php';
    require_once 'ulicznik.php'; //counter
    include_once 'waypoint_info.php';

    $link = GKDB::getLink();

    $smarty->assign('counter', ulicznik('index'));


    // -------------------------------------- statystyki podstawowe / basic statistics -------------- //

    $result = mysqli_query($link, "SELECT * FROM `gk-wartosci` WHERE `name` LIKE 'stat_%'");
    while ($row = mysqli_fetch_assoc($result)) {
        $statystyka[$row['name']] = $row['value'];
    }
    $smarty->assign('stats', $statystyka);
    mysqli_free_result($result);

    $jquery = <<<'EOD'
$('#nr[maxlength]').maxlength({
  warningClass: "label label-danger",
  limitReachedClass: "label label-success",
});
EOD;
    $smarty->append('jquery', $jquery);

    // -------------------------------------- news ------------------------------- //

    $sql = 'SELECT DATE(`date`) as date, `tresc` as content, `tytul` as title, `who`, `userid`, `komentarze` as comment_count, `news_id` FROM `gk-news` ORDER BY `date` DESC LIMIT 2';
    $result = mysqli_query($link, $sql);
    $smarty->assign('news', mysqli_fetch_all($result, MYSQLI_ASSOC));

    // -------------------------------------- recent moves ------------------------------- //

    $sql = 'SELECT ru.ruch_id, ru.id, ru.lat, ru.lon, ru.country, ru.waypoint, ru.droga, ru.data, ru.user, ru.koment, ru.logtype, ru.username, us.user, gk.nazwa, gk.typ, gk.owner, pic.plik, ru.zdjecia
      FROM (SELECT * FROM `gk-ruchy` r1 ORDER BY r1.ruch_id DESC LIMIT 50) ru
      INNER JOIN `gk-users` us ON (ru.user = us.userid)
      INNER JOIN `gk-geokrety` gk ON (ru.id = gk.id)
      LEFT JOIN `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
      WHERE (gk.typ != "2")
      ORDER BY ru.ruch_id DESC limit 7';
    $result = mysqli_query($link, $sql);
    $smarty->assign('recent_moves', mysqli_fetch_all($result, MYSQLI_ASSOC));

    // -------------------------------------- recent pictures ------------------------------- //

    $sql = 'SELECT ob.typ as type, ob.id, ob.id_kreta as gk_id, ob.user as user_id, ob.plik as filename, ob.opis as legend, gk.nazwa as gk_name, us.user as username, ru.country, ru.data as date
		FROM `gk-obrazki` ob
		LEFT JOIN `gk-geokrety` gk ON (ob.id_kreta = gk.id)
		LEFT JOIN `gk-users` us ON (ob.user = us.userid)
		LEFT JOIN `gk-ruchy` ru ON (ob.id = ru.ruch_id )
		ORDER BY `obrazekid` DESC
    LIMIT 18';
    $result = mysqli_query($link, $sql);
    $smarty->assign('recent_pictures', mysqli_fetch_all($result, MYSQLI_ASSOC));

    // -------------------------------------- recent geokrety ------------------------------- //

    $sql = 'SELECT `gk-geokrety`.id, nr as tracking_code,
      nazwa as name, `gk-geokrety`.opis as description,
      owner, DATE(data) as date,
      `gk-geokrety`.typ as type,
      `gk-users`.user as username, userid,
      plik as avatar_filename
    FROM `gk-geokrety`
    LEFT JOIN `gk-users` ON (owner = `gk-users`.userid)
    LEFT JOIN `gk-obrazki` ON (`gk-geokrety`.id = id_kreta AND `gk-obrazki`.typ = "0")
    WHERE owner > 0
    ORDER BY `gk-geokrety`.id DESC
    LIMIT 7';
    $result = mysqli_query($link, $sql);
    $smarty->assign('recent_geokrety', mysqli_fetch_all($result, MYSQLI_ASSOC));

    // ---- kto online ----//

    $link->query("SET time_zone = '+0:00'");
    $sql = 'SELECT user as username, userid as user_id FROM `gk-users` WHERE ostatni_login > DATE_SUB(NOW(), INTERVAL 5 MINUTE)';
    $result = mysqli_query($link, $sql);
    $smarty->assign('online_users', mysqli_fetch_all($result, MYSQLI_ASSOC));

    // ----------------------------------------------JSON-LD---------------------------
    $gkName = $config['adres'];
    $gkUrl = $config['adres'];
    $gkLogoUrl = $config['cdn_url'].'/images/banners/geokrety.png';
    $gkHeadline = _($config['punchline']);
    $gkDescription = _($config['intro']);
    $gkKeywords = $config['keywords'];
    $lastUpdate = filemtime(__FILE__);
    $dateModified = date('c', $helpLastUpdate);
    $datePublished = date('c', $helpLastUpdate);

    $ldHelper = new LDHelper($gkName, $gkUrl, $gkLogoUrl);
    $ldJSONWebSite = $ldHelper->helpWebSite(
      $gkHeadline,
      $gkDescription,
      $gkLogoUrl,
      $gkName,
      $gkUrl,
      $gkKeywords,
      $lang,
      $dateModified,
      $datePublished
    );
    $smarty->assign('ldjson', $ldJSONWebSite);

    $smarty->assign('content_template', 'home.tpl');

    // ----------------------------------------------JSON-LD-(end)---------------------
    require_once 'smarty.php';
    //include_once("gk_cache_end.php");
} catch (Exception $exc) {
    echo 'Service unavailable - '.$exc->getMessage();
}
