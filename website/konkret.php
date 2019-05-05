<?php

require_once '__sentry.php';

// this page shows details of a GeoKret

if (count($_GET) == 0) {
    header('Location: /');
}

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$kret_gk = $_GET['gk'];
$kret_id = $_GET['id'];
$kret_nocache = $_GET['nocache'];
$page = ctype_digit($_GET['page']) ? $_GET['page'] : 1;

// -------------------------------------- filtering --------------------------
if (isset($kret_gk)) {
    if (preg_match('#^[a-f0-9]{4,5}$#i', substr($kret_gk, 2, 5))) {
        $kret_id = hexdec(substr($kret_gk, 2, 5));
    } else {
        include_once 'defektoskop.php';
        $TRESC = defektoskop(_('No such GeoKret!'), true, 'bad value of GK parameter', 6, 'BAD_INPUT');
        include_once 'smarty.php';
        exit;
    }
}

// ------------------------------------ geokret details ---------------------

if (!ctype_digit($kret_id)) {
    include_once 'defektoskop.php';
    $TRESC = defektoskop(_('No such GeoKret!'), true, 'bad value of ID parameter', 6, 'BAD_INPUT');
    include_once 'smarty.php';
    exit;
}

$link = GKDB::getLink();
$kret_gk = sprintf('GK%04X', $kret_id);
require_once 'czy_obserwowany.php'; //geokret_watchers

$gk = new \Geokrety\Repository\KonkretRepository($link);
$geokret = $gk->getById($kret_id, true);
if (is_null($geokret)) {
    include_once 'defektoskop.php';
    $TRESC = defektoskop(_('No such GeoKret!'), true, 'there is no such mole', 3, 'WRONG_DATA');
    include_once 'smarty.php';
    exit;
}
$smarty->assign('geokret_details', $geokret);

// Is owner?
$smarty->assign('isGeokretOwner', $userid_longin != null && $userid_longin == $geokret->ownerId);

// Load country track
$sql = 'SELECT  country, COUNT(*) as count
FROM (
  SELECT  @r := @r + (@country != country) AS gn,
          @country := country AS sn,
          s.*
  FROM (SELECT @r := 0, @country := \'\') vars,
       `gk-ruchy` as s
  WHERE id = '.$kret_id.'
  AND s.lat is not null
  AND s.lon is not null
  ORDER BY data_dodania asc, data
) q
GROUP BY gn';
$result = mysqli_query($link, $sql);
$smarty->assign('country_track', mysqli_fetch_all($result, MYSQLI_ASSOC));

// Load avatar
$sql = 'SELECT ob.typ as type, ob.id, ob.id_kreta as gk_id, ob.user as user_id, ob.plik as filename, ob.opis as legend
FROM `gk-obrazki` ob
LEFT JOIN `gk-geokrety` gk ON (ob.obrazekid = gk.avatarid)
WHERE gk.id = '.$kret_id.'
LIMIT 1';
$result = mysqli_query($link, $sql);
if (mysqli_num_rows($result)) {
    $avatar = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
    $smarty->assign('geokret_avatar', $avatar);
} else {
    $smarty->assign('geokret_avatar', false);
}

$jquery = <<<'EOD'
$('.collapse').on('shown.bs.collapse', function(){
  $(this).parent().find(".glyphicon-plus").removeClass("glyphicon-plus").addClass("glyphicon-minus");
}).on('hidden.bs.collapse', function(){
  $(this).parent().find(".glyphicon-minus").removeClass("glyphicon-minus").addClass("glyphicon-plus");
});
EOD;
$smarty->append('jquery', $jquery);

// link to ruchy
// if user is logged in
$smarty->assign('geokret_already_seen', false);
if ($userid_longin != null) {
    // if the guest already had the mole in his hand, he can confidently know his number
    $result = mysqli_query($link, "SELECT user FROM `gk-ruchy`
      WHERE id='$kret_id'
      AND user='$userid_longin'
      AND logtype <> '2'
      LIMIT 1");
    $row = mysqli_fetch_array($result);
    mysqli_free_result($result);
    $smarty->assign('geokret_already_seen', !empty($row) or ($userid == $userid_longin));
}

//-------------------------------------------- OBRAZKI / PHOTOS ------------------------------- //

$result = mysqli_query($link, "SELECT obrazekid AS picture_id, id AS id, user AS user_id, id_kreta AS geokret_id,
  plik AS filename, opis AS legend, typ AS type
  FROM `gk-obrazki`
  WHERE id_kreta='$kret_id'
  ORDER BY obrazekid DESC
  LIMIT 30");
$smarty->assign('geokret_pictures', mysqli_fetch_all($result, MYSQLI_ASSOC));

// altitude
if (is_file("templates/wykresy/$kret_id-m.png") and is_file("templates/wykresy/$kret_id-m.png")) {
    $smarty->assign('geokret_altitude_profile', true);
}

//-------------------------------------------- OBRAZKI / PHOTOS : end ------------------------------- //

// Watchers
$smarty->assign('geokret_watchers', czy_obserwowany($kret_id, $userid_longin));

// how many moves in total
$result = mysqli_query($link, "SELECT COUNT(*) FROM `gk-ruchy` WHERE id = '$kret_id'");
$total_move_count = mysqli_fetch_array($result)[0];
$smarty->assign('total_move_count', $total_move_count);
$max_page = ceil($total_move_count / 20);
if ($page > $max_page) {
    $page = $max_page;
}

//-------------------------------------------- MAP ------------------------------- //

if ($geokret->cachesCount > 0) {
    $smarty->append('css', CDN_LEAFLET_CSS);
    $smarty->append('javascript', CDN_LEAFLET_JS);
    $smarty->append('javascript', '/konkret.js');

    $onLoadErrorHtmlMessage = '<center><b>'._('unable to initialize map').'</b></center>';
    $lastSeenMessage = '<b>'._('last seen here !').'</b>';
    $jquery = <<<EOD
let onLoadErrorHtmlMessage = "$onLoadErrorHtmlMessage";
let lastSeenMessage = "$lastSeenMessage";
let mapGeokretyId = $kret_id;
initMapForGeokrety(mapGeokretyId, onLoadErrorHtmlMessage, lastSeenMessage);
EOD;
    $smarty->append('jquery', $jquery);

    // manage map data cache and files
    $mapDirectory = 'mapki/';
    if (isset($config['mapki'])) {
        $mapDirectory = $config['mapki'];
    }
    $tripService = new \Geokrety\Service\TripService($mapDirectory);
    if (isset($kret_nocache)) { // we enforce to regenerate cache
        $tripService->evictTripCache($kret_id);
    }
    $tripService->ensureGeneratedFiles($kret_id);
    $smarty->assign('trip_gpx', $tripService->getTripGpxFilename($kret_id));
    $smarty->assign('trip_csv', $tripService->getTripCsvFilename($kret_id));
}

// ----------------------------------------------Ruchy-----------------------------

$move_start = ($page - 1) * 20;
// Moves
$sql = "SELECT ru.ruch_id AS id, ru.data AS date,
ru.koment AS comment, ru.zdjecia AS pictures_count,
ru.komentarze AS comments_count, ru.logtype As logtype,
ru.user AS author_id, us.user AS username, ru.username AS username_anonymous,
ru.lat, ru.lon, ru.waypoint,
ru.country, ru.alt, ru.droga AS distance,
ru.app, ru.app_ver
FROM `gk-ruchy` ru
LEFT JOIN `gk-users` AS us ON (ru.user = us.userid)
WHERE ru.id = $kret_id
ORDER BY ru.data DESC
LIMIT $move_start, 20";
$result = mysqli_query($link, $sql);
$moves = mysqli_fetch_all($result, MYSQLI_ASSOC);
$smarty->assign('moves', $moves);

// Extract moves ids
$moves_ids = array_map(function ($move) {
    return $move['id'];
}, $moves);

// Moves comments
$sql = "SELECT co.comment_id, co.ruch_id, co.user_id, co.data_dodania, co.comment, co.type, us.user
FROM (`gk-ruchy-comments` co)
LEFT JOIN `gk-users` AS us ON (co.user_id = us.userid)
WHERE co.kret_id='$kret_id'
AND co.ruch_id IN (".implode(',', $moves_ids).')
ORDER BY co.ruch_id, co.comment_id ASC';
$result = mysqli_query($link, $sql);
$moves_comments = mysqli_fetch_all($result, MYSQLI_ASSOC);
$smarty->assign('moves_comments', $moves_comments);

$sql = "SELECT obrazekid AS picture_id, id AS id, user AS user_id, id_kreta AS geokret_id,
  plik AS filename, opis AS legend, typ AS type
  FROM `gk-obrazki`
  WHERE id_kreta='$kret_id'
  AND id IN (".implode(',', $moves_ids).')
  ORDER BY timestamp DESC';
$result = mysqli_query($link, $sql);
$moves_pictures = mysqli_fetch_all($result, MYSQLI_ASSOC);
$smarty->assign('moves_pictures', $moves_pictures);

// ----------------------------------------------JSON-LD---------------------------
// schema used: http://schema.org/Sculpture

$gkName = $config['adres'];
$gkUrl = $config['adres']."konkret.php?id=$kret_id";
$gkOwnerUrl = $config['adres'].'mypage.php?id='.$geokret->ownerId;
$gkLogoUrl = $config['cdn_url'].'/images/banners/geokrety.png';
$gkkeywords = $config['keywords'].",$kret_gk,".$cotozakret[$geokret->type];

$ldHelper = new LDHelper($gkName, $config['adres'], $gkLogoUrl);
$konkret = new \Geokrety\Domain\Konkret();
$konkret->name = $kret_gk.' - '.$geokret->name;
$konkret->description = $geokret->description;
$konkret->url = $gkUrl;
// $konkret->author = $geokret['username']; // TODO
$konkret->authorUrl = $gkOwnerUrl;
$konkret->datePublished = date('c', strtotime($geokret->datePublished));
// if (isset($avatar)) {
//     $konkret->imageUrl = CONFIG_CDN_OBRAZKI_MALE.'/'.$avatar['filename']; // TODO
// }
//$konkret->keywords = $gkkeywords;
// // rate
// if (isset($ratingCount) && $ratingCount > 0) {
//     $konkret->ratingCount = $ratingCount;
//     $konkret->ratingAvg = $ratingAvg;
// }
// comments
// TODO
if ($konkretLogsCount > 0) {
    $konkret->konkretLogs = $konkretLogs;
}
$smarty->assign('ldjson', $ldHelper->helpKonkret($konkret));

// ----------------------------------------------JSON-LD-(end)---------------------

$TYTUL = $geokret->name;

$baseContent = '<div class="modal-body"><div class="center-block" style="width: 45px;"><img src="https://cdn.geokrety.house.kumy.net/images/loaders/rings.svg" /></div></div>';
$jquery = <<<EOD
$('#modal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget)
  var typeName = button.data('type');
  var modal = $(this);
  modal.find('.modal-content').html('$baseContent');

  if (typeName == 'move-comment') {
    var commentType = button.data('move-comment-type');
    var gkid = button.data('gkid');
    var ruchid = button.data('ruchid');
    modal.find('.modal-content').load('comment.php?gkid='+gkid+'&ruchid='+ruchid+'&type='+commentType);
  } else if (typeName == 'move-delete') {
    var id = button.data('id');
    modal.find('.modal-content').load('_dialog_move_delete.php?id='+id);
  } else if (typeName == 'move-comment-delete') {
    var id = button.data('id');
    modal.find('.modal-content').load('_dialog_move_comment_delete.php?id='+id);
  }
})
EOD;
$smarty->append('jquery', $jquery);

$smarty->assign('content_template', 'geokret.tpl');

require_once 'smarty.php';
