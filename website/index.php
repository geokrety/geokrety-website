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

    $newsR = new Geokrety\Repository\NewsRepository($link);
    list($news, $newsCount) = $newsR->getRecent(0, $config['home_news_per_page']);
    $smarty->assign('news', $news);

    // -------------------------------------- recent moves ------------------------------- //

    $tripR = new Geokrety\Repository\TripRepository($link);
    $trip = $tripR->getRecentTrip($config['home_trip_per_page']);
    $smarty->assign('trip', $trip);

    // -------------------------------------- recent pictures ------------------------------- //

    $pictureR = new \Geokrety\Repository\PictureRepository($link);
    $pictures = $pictureR->getRecentPictures(18);
    $smarty->assign('recent_pictures', $pictures);

    // -------------------------------------- recent geokrety ------------------------------- //

    $geokretyR = new Geokrety\Repository\KonkretRepository($link);
    $geokrety = $geokretyR->getRecentCreation($config['home_geokrety_per_page']);
    $smarty->assign('recent_geokrety', $geokrety);

    // ---- kto online ----//

    $userR = new Geokrety\Repository\UserRepository($link);
    $users = $userR->getOnlineUsers($config['home_online_users_time']);
    $smarty->assign('online_users', $users);

    // ----------------------------------------------JSON-LD---------------------------
    $gkName = $config['adres'];
    $gkUrl = $config['adres'];
    $gkLogoUrl = $config['cdn_url'].'/images/the-mole.svg';
    $gkHeadline = _($config['punchline']);
    $gkDescription = str_replace('%1', $config['adres'].'/help.php#about', _($config['intro']));
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
} catch (Exception $exc) {
    echo 'Service unavailable - '.$exc->getMessage();
}
