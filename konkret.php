<?php

require_once '__sentry.php';

// this page shows details of a geokret śćńółźłśśóś

if (count($_GET) == 0) {
    exit;
} //bez parametow od razu wychodzimy

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

//foreach ($_GET as $key => $value) { $_GET[$key] = mysqli_real_escape_string(strip_tags($value));}
//foreach ($_POST as $key => $value) { $_POST[$key] = mysqli_real_escape_string(strip_tags($value));}

$kret_gk = $_GET['gk'];
// autopoprawione...
$kret_id = $_GET['id'];
// autopoprawione...import_request_variables('g', 'kret_');

require 'templates/konfig.php';    // config
require_once 'czy_obserwowany.php';
require_once 'waypoint_info.php';

$userid_longin = $longin_status['userid'];

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

$link = DBConnect();

$result = mysqli_query($link,
    "SELECT id, nr, nazwa, opis, owner, us.user, data, typ, droga, skrzynki, zdjecia, avatarid
FROM `gk-geokrety` gk
LEFT JOIN `gk-users` us ON gk.owner = us.userid
WHERE gk.id='$kret_id' LIMIT 1"
);

// jak nie ma takiego kreta to lepiej zakonczyc dzialanie :)
if (mysqli_num_rows($result) == 0) {
    include_once 'defektoskop.php';
    $TRESC = defektoskop(_('No such GeoKret!'), true, 'nie ma takiego kreta', 3, 'WRONG_DATA');
    include_once 'smarty.php';
    exit;
}

list($id, $nr, $nazwa, $opis, $userid, $user, $data, $krettyp, $droga_total, $skrzynki, $zdjecia, $avatar_id) = mysqli_fetch_array($result);
mysqli_free_result($result);

$social_url = $config['adres']."konkret.php?id=$kret_id"; // for social networks
$social_name = ("Geokrety: $nazwa");

//policz ilosc obrazkow
// $result = mysqli_query($link, "SELECT COUNT(*) FROM `gk-obrazki` WHERE `id_kreta` = '$id' LIMIT 1");
// $row = mysqli_fetch_row($result);
// mysqli_free_result($result);
// $zdjecia = $row[0];

// if this is owner logged in
if ($userid == $userid_longin) {
    $edytuj_kreta = '<a href="edit.php?co=geokret&amp;id='.$id.'" title="'._('Edit description').'"><img src="'.CONFIG_CDN_ICONS.'/edit.png" alt="[Edit_comment]" width="16" height="16" border="0"/></a>';
    $dodaj_obrazek = '<a href="imgup.php?typ=0&amp;id='.$id.'"><img src="'.CONFIG_CDN_ICONS.'/image.png" alt="[Add_photo]" title="'._('Add photo').'" width="16" height="16" border="0" /></a>';
    $tracking_code = '<tr><td class="tresc1">Tracking Code: </td><td><strong>'.$nr.'</strong></td></tr>';
    //$view_gallery = '<img src="'.CONFIG_CDN_ICONS.'/photos.png" class="textalign16" alt="" width="16" height="16" border="0"/> <a href="geokret_gallery.php?id='.$id.'">' ._("View photo gallery and set avatar"). "</a> <span class='xs' title='". _('Number of photos in the gallery') ."'>($zdjecia)</span>";
    $drukuj_etykiete = '<img src="'.CONFIG_CDN_ICONS.'/printer.png" class="textalign16" alt="*" width="16" height="16" border="0"/> <a href="labels.php?id='.$id.'&nr='.$nr.'">'._('Print a label for this geokret').'</a>';
    $archiwizuj_kreta = '<img src="'.CONFIG_CDN_ICONS.'/archiwizuj.png" class="textalign16" alt="*" width="16" height="16" border="0"/> <a href="ruchy.php?nr='.$nr.'&amp;type=archive" title="Archive">'._('Archive this geokret').'</a>';
    $ownerIsLogged = 1; // owner is logged in and browsing
} else {
    //$view_gallery = '<img src="templates/photos.png" class="textalign16" alt="" width="16" height="16" border="0"/> <a href="geokret_gallery.php?id='.$id.'">' ._("View photo gallery"). "</a> <span class='xs' title='". _('Number of photos in the gallery') ."'>($zdjecia)</span>";
}

    $write_note = '<img src="'.CONFIG_CDN_ICONS.'/note16.png" class="textalign16" alt="*" width="16" height="16" border="0"/> <a href="ruchy.php?type=note&amp;id='.$id.'">'._('Write a note').'</a>';
    $view_stat = '<img src="'.CONFIG_CDN_ICONS.'/stat.png" class="textalign16" alt="*" width="16" height="16" border="0" /> <a href="/gk_stat.php?id='.$kret_id.'"> '._('Statistics').'</a>';
    $view_gallery = '<img src="'.CONFIG_CDN_ICONS.'/photos.png" class="textalign16" alt="*" width="16" height="16" border="0"/> <a href="geokret_gallery.php?id='.$id.'">'._('View photo gallery')."</a> <span class='xs' title='"._('Number of photos in the gallery')."'>($zdjecia)</span>";

    //$report_missing = '<img src="'.CONFIG_CDN_ICONS.'/flag_add.png" class="textalign16" alt="Archive" width="16" height="16" border="0"/> <a href="ruchy.php?type=missing&amp;id='.$id.'">'. _('Report missing'). '</a>';

    $link1a = htmlentities('[url='.$config['adres'].'konkret.php?id='.$id.']'.$nazwa.'[/url]', ENT_QUOTES, 'UTF-8', false);
    $link2a = htmlentities('<a href="'.$config['adres'].'konkret.php?id='.$id.'">'.$nazwa.'</a>', ENT_QUOTES, 'UTF-8', false);
    $link1 = "<input onclick='select()' type='text' name='link1' value='$link1a' size='14' style='height: 1.0em; border: 1px solid #d5d5d5'/>";
    $link2 = "<input onclick='select()' type='text' name='link2' value='$link2a' size='14' style='height: 1.0em; border: 1px solid #d5d5d5'/>";

// link do ruchy
// if user is logged in
if ($userid_longin != null) {
    // jeśli gość miał już w ręce tego kreta, to śmiało może poznać jego numerek
    $result = mysqli_query($link, "SELECT `user` FROM `gk-ruchy`  WHERE `id`='$kret_id' AND `user`='$userid_longin' AND `logtype`<>'2' LIMIT 1");
    $row = mysqli_fetch_array($result);
    mysqli_free_result($result);

    if (!empty($row) or ($userid == $userid_longin)) {
        $ruchy_tego_kreta = '<img src="'.CONFIG_CDN_ICONS.'/flag_add.png" class="textalign16" alt="*" width="16" height="16" /> '."<a href=\"/ruchy.php?nr=$nr\">"._('Log this GeoKret').'</a>';   // link do łatwej edycji
        $drukuj_etykiete_recreate = '<img src="'.CONFIG_CDN_ICONS.'/printer.png" class="textalign16" alt="*" width="16" height="16" border="0"/> <a href="labels.php?id='.$id.'&nr='.$nr.'">'._('Recreate a label for this geokret').'</a>';
        $currentUserKnowsTC = 1; // currently logged user knows TC and can perform some operations with it
    }
}

    if (!empty($ruchy_tego_kreta)) {
        $write_note = '';
    }

//-------------------------------------------- OBRAZKI ------------------------------- //
$result = mysqli_query($link, "SELECT obrazekid, plik, opis, typ FROM `gk-obrazki` WHERE id='$kret_id' AND (typ='0') ORDER BY `obrazekid` DESC LIMIT 30");

while ($row = mysqli_fetch_row($result)) {
    list($obrazki_id, $obrazki_plik, $obrazki_opis, $obrazki_typ) = $row;

    //splits long words which would otherwise break the css design
    $obrazki_opis = preg_replace("/(([^\s\&]|(\&[\S]+\;)){10})/u", '$1&shy;', $obrazki_opis);

    ($obrazki_id == $avatar_id) ? $tmpclass = 'obrazek_hi' : $tmpclass = 'obrazek';
    $OBRAZKI_MAIN .= "<span class=\"$tmpclass\"><a href=\"".CONFIG_CDN_IMAGES."/obrazki/$obrazki_plik\" rel=\"cb\" title=\"$obrazki_opis\" ><img src=\"".CONFIG_CDN_IMAGES."/obrazki-male/$obrazki_plik\" border=\"0\" alt=\"$obrazki_opis\" title=\"$obrazki_opis\" width=\"100\" height=\"100\"/></a><br />$obrazki_opis";

    if ($userid == $userid_longin) {
        $OBRAZKI_MAIN .= ' <a href="imgup.php?typ='.$obrazki_typ.'&amp;id='.$id.'&amp;rename='.$obrazki_id.'" title="'._('Rename').'"><img src="'.CONFIG_CDN_ICONS.'/edit10.png" alt="rename" width="10" height="10" border="0" /></a> ';
        $OBRAZKI_MAIN .= ' <a href="edit.php?delete_obrazek='.$obrazki_id.'" onclick="return CzySkasowac(this, \'this photo?\')" title="'._('Delete photo').'"><img src="'.CONFIG_CDN_ICONS.'/delete10.png" alt="delete" width="10" height="10" border="0" /></a> ';
    }
    $OBRAZKI_MAIN .= '</span>';
}

// wykres

if (is_file("templates/wykresy/$kret_id-m.png") and is_file("templates/wykresy/$kret_id-m.png")) {
    $OBRAZKI_MAIN .= '<span class="obrazek"><a href="'.CONFIG_CDN_IMAGES."/wykresy/$kret_id.png\" rel=\"cb\" title=\""._('Altitude profile').'" ><img src="'.CONFIG_CDN_IMAGES."/wykresy/$kret_id-m.png\" border=\"0\" alt=\""._('Altitude profile')."\" title=\"$obrazki_opis\" width=\"100\" height=\"100\"/></a><br />"._('Altitude profile').' <a href="help.php#altitude"><img src="'.CONFIG_CDN_ICONS.'/help.png" alt="?" width="11" height="11" border="0" /></a>'.'</span>';
}

$OBRAZKI_MAIN = '<div id="obrazek_box">'.$OBRAZKI_MAIN.'</div>';
//-------------------------------------------- OBRAZKI: end ------------------------------- //

$czy_obserwowany = czy_obserwowany($kret_id, $userid_longin);
if ($czy_obserwowany['plain'] == 10) {
    $czy_obserwowany['html'] = '';
} else {
    $czy_obserwowany['html'] = '<img src="'.$czy_obserwowany['icon'].'" class="textalign16" alt="" width="16" height="16" /> '.$czy_obserwowany['html'];
}

// ile ruchów w sumie
$result = mysqli_query($link, "SELECT COUNT(`lat`) FROM `gk-ruchy` WHERE `id` = '$id'");
list($ile_ruchow) = mysqli_fetch_array($result);

require_once 'konkret-tabelka.php';
require_once 'konkret-country.php';

// jezeli wlasciciel zgodzil sie dostawac maile to przygotuj linki kontaktowe
// eeeee... wyremowac to, ale juz! :)
//$exists = mysqli_num_rows(mysqli_query($link, "SELECT `user` FROM `gk-users` WHERE `userid`='$userid' AND `email` != '' LIMIT 1"));
$exists = 1;
if (($userid_longin != null) and ($exists) and ($userid != $userid_longin)) {
    $wyslij_wiadomosc = "<a href='majluj.php?to=$userid&amp;re=$kret_id'><img src='".CONFIG_CDN_ICONS."/email.png' class='textalign16' alt='' title='".('Send a message to the user')."' width='16' height='16' border='0'/></a>";
    $email_owner = '<img src="'.CONFIG_CDN_ICONS.'/email.png" class="textalign16" alt="*" width="16" height="16" border="0"/> <a href="majluj.php?to='.$userid.'&amp;re='.$kret_id.'">'._('Email the owner').'</a>';
}

$claim_geokret = '';
if ($userid == 0) { // if we have an unclaimed geokret then prepare "claim it" text
    if ($userid_longin === null) { // anonim
        $claim_geokret = '<a href="longin.php">'._('Login to claim this GeoKret').'</a>';
        $claim_alert = '<div class="alert alert-info" role="alert">'
        .sprintf(_('This GeoKret is available for adoption. Please <a href="%s">login</a> first.'), 'longin.php').
        '</div>';
    } else {
        $claim_geokret = "<a href='claim.php'>"._('Claim this GeoKret').'</a>';
        $claim_alert = '<div class="alert alert-info" role="alert">'
        .sprintf(_('This GeoKret is available for adoption. You can <a href="%s">claim</a> this GeoKret.'), 'claim.php').
        '</div>';
    }
}

//$write_note ---- mozliwosc dodania komentarza przez osobe bez TC
if ($userid_longin === null) { // anonim
    $t11 = $view_gallery;
    $t12 = $claim_geokret;
    $t13 = '';
    $t21 = $view_stat;
    $t22 = '';
    $t23 = '';
    $t31 = '';
    $t32 = '';
    $t33 = '';
} else {
    if ($userid_longin == $userid) { //wlasciciel
        $t11 = $czy_obserwowany['html'];
        $t12 = $ruchy_tego_kreta;
        $t13 = $drukuj_etykiete;
        $t21 = $view_gallery;
        $t22 = '';
        $t23 = $archiwizuj_kreta;
        $t31 = $view_stat;
        $t32 = '';
        $t33 = '';
    } else { // kazdy inny zarejestrowany user
        $t11 = $czy_obserwowany['html'];
        $t12 = $ruchy_tego_kreta;
        $t13 = $email_owner;
        $t21 = $view_gallery;
        $t22 = $claim_geokret;
        $t23 = $drukuj_etykiete_recreate;
        $t31 = $view_stat;
        $t32 = '';
        $t33 = '';

        // if (!empty($ruchy_tego_kreta)) {$t12=$ruchy_tego_kreta; $t22=$write_note;}
    // else {$t12=$write_note;}
    }
}

// ------------------------- rating ---------------------- //
        // total rating
        $sql = "SELECT count(`rate`), avg(`rate`)  FROM `gk-geokrety-rating` WHERE `id`=$id LIMIT 1";
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_row($result);
        list($ratingCount, $ratingAvg) = $row;
        $ratingAvg = ($ratingAvg == '') ? 0 : $ratingAvg;

        // has user voted?
        $sql = "SELECT count(`rate`)  FROM `gk-geokrety-rating` WHERE `id`=$id and `userid` = '$userid_longin' LIMIT 1";
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_row($result); $userRated = $row[0];
if ($userRated == 0 and $currentUserKnowsTC == 1 and $ownerIsLogged != 1) {
    $userCanRateThisGK = _('You can rate this GeoKret');
    $ratingDisabled = 'false';
} elseif ($userRated == 1) {
    $userCanRateThisGK = _('You have already rated this GeoKret');
} elseif ($ownerIsLogged == 1) {
    $userCanRateThisGK = _("You can't rate your own GeoKret");
} else {
    $userCanRateThisGK = _("You can't rate this geokret");
}

        $ratingDisabled = ($ratingDisabled == 'false') ? 'false' : 'true';

        $ratingSha = sha1(date('ynj').$userid_longin.$config['jrating_token']);   // for proofing voting userid
// ------------------------- rating end ------------------ //

$TRESC = $claim_alert.'
<div itemscope itemtype="http://schema.org/Sculpture">
<table width="100%">
<tr><td class="heading1" colspan="2"><img src="'.CONFIG_CDN_IMAGES.'/log-icons/'.$krettyp.'/icon_25.jpg" alt="Info:" width="25" height="25" /> GeoKret <strong>'.$nazwa.'</strong> ('.$cotozakret[$krettyp].') '.
(($userid > 0) ? ('by <a href="mypage.php?userid='.$userid.'">'.$user.'</a>'.' '.$wyslij_wiadomosc) : ' - unclaimed').
'</td></tr>

<tr>
<td class="tresc1" style="width:10em">Reference Number:</td><td><strong>'.sprintf('GK%04X', $kret_id).'</strong></td></tr>
'.$tracking_code.'
<tr><td class="tresc1">'._('Total distance').': </td><td><strong>'.$droga_total.' km</strong></td></tr>
<tr><td class="tresc1">'._('Places visited').': </td><td><strong>'.$skrzynki.'</strong></td></tr>
<tr><td class="tresc1">'._('Forum links').': </td><td><form name="frm1">'.$link1.' '.$link2.'</form></td></tr>
<tr><td class="tresc1">'._('Country track').': </td><td>'.$cykl_flag.'</td></tr>
<tr><td class="tresc1">'._('Rating').': </td>
<td style="padding-top: 10px;"><div class="basic" id="'.$ratingAvg.'+'.$id.'+'.$userid_longin.'+'.$ratingSha.'+'.$lang.'"></div>
<span class="szare">'._('votes').': '.$ratingCount.', '._('average rating').
': '.$ratingAvg.'. '.$userCanRateThisGK.'.</span>
<span id="serverResponse"></span>
</td></tr>
<tr><td class="tresc1">'._('Share on').': </td><td>
<a href="http://sharetodiaspora.github.io/?title='.$social_name.'&amp;url='.$social_url.'" target="_blank" title="Share with Diaspora*"><img src="'.CONFIG_CDN_ICONS.'/diaspora.png" style="border: 0px solid;" /></a>
<a href="https://plus.google.com/share?url='.$social_url.'" target="_blank" title="Share on Google+"><img src="'.CONFIG_CDN_ICONS.'/gplus-16.png" alt="Share on Google+"/></a>
<a href="http://pinterest.com/pin/create/button/?url='.$social_url.'&amp;description='.$social_name.'" target="_blank" title="Share on Pinterest"><img src="'.CONFIG_CDN_ICONS.'/pinterest16.png" alt="Share on Pinterest"/></a>
<a href="https://www.facebook.com/sharer/sharer.php?u='.$social_url.'&amp;title='.$social_name.'" target="_blank" title="Share with Facebook - not sure if this works"><img src="'.CONFIG_CDN_ICONS.'/facebook16.png" style="border: 0px solid;" /></a>
</td></tr>
</table>

<table width="100%">
<tr><td class="heading1"><img src="'.CONFIG_CDN_ICONS.'/info.png" alt="Comment:" width="22" height="22" /></td></tr>

<tr><td class="tresc1" title="'._('Short description').'">'.$opis.'</td></tr>
<tr><td align="right">'.$dodaj_obrazek.' '.$edytuj_kreta.'</td></tr>
<tr><td class="tresc1">'.$OBRAZKI_MAIN.'</td></tr>

<tr><td class="heading1"><img src="'.CONFIG_CDN_ICONS.'/tool.png" alt="Links:" width="22" height="22" /></td></tr>

</table>
<table class="tresc1">
<col span="3" style="width: 33%" />
<tr><td>'.$t11.'</td><td>'.$t12.'</td><td>'.$t13.'</td></tr>
<tr><td>'.$t21.'</td><td>'.$t22.'</td><td>'.$t23.'</td></tr>
<tr><td>'.$t31.'</td><td>'.$t32.'</td><td>'.$t33.'</td></tr>
</table>';

// wczytanie mapek z pliczku wprost do nagłóweczka

// if number of points > 50, don't load dynamic map

// display the map
if (($skrzynki <= 250 and $skrzynki > 0) and $droga_total < 100000) {
    $OGON .= '<script src="https://maps.google.com/maps?file=api&amp;v=2&amp;key='.$GOOGLE_MAP_KEY.'" type="text/javascript"></script>';
    if (!file_exists($config['mapki']."/map/GK-$id.map")) {
        include 'konkret-mapka.php';
        konkret_mapka($id);
    } // generuje plik z mapką krecika

    $HEAD .= file_get_contents($config['mapki']."/map/GK-$id.map");
    $BODY .= 'onload="load()" onunload="GUnload()"';
    $TRESC .= '<table width="100%">
<tr><td class="heading1"><a name="map"></a><img src="'.CONFIG_CDN_ICONS.'/mapa.png" alt="Map:" width="22" height="22" /></td></tr>
<tr><td class="tresc1">
<img src="'.CONFIG_CDN_PINS_ICONS.'/red.png" alt="[Red flag]" width="12" height="20" /> = '._('start').'
<img src="'.CONFIG_CDN_PINS_ICONS.'/yellow.png" alt="[Yellow flag]" width="12" height="20" /> = '._('trip points').'
<img src="'.CONFIG_CDN_PINS_ICONS.'/green.png" alt="[Green flag]" width="12" height="20" /> = '._('recently seen').'
</td></tr>

</table><div id="map0" class="gmapa"></div>';
}

// don't display the map

elseif ($skrzynki > 0) {
    $TRESC .= '<p><i>'._('The google map is not avaliable due to large numer of points.').'</i></p>';
} else {
    $TRESC .= '<p><i>'._('This geokret has not started yet').'</i></p>';
}

if ($skrzynki > 0) {
    $TRESC .= $TABELKA;
    $TRESC .= _('Download the track as:')." <a href='".CONFIG_CDN_MAPS."/gpx/GK-$kret_id.gpx'>GPX</a> | <a href='".CONFIG_CDN_MAPS."/csv/GK-$kret_id.csv.gz'>csv.gz</a>";
}

$TRESC .= '</div>'; // koniec początkowego wzkaźnika schema.org
$TRESC .= '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
<meta itemprop="worstRating" content="1">
<meta itemprop="bestRating" content="5">
<meta itemprop="ratingValue" content="'.$ratingAvg.'">
<meta itemprop="reviewCount" content="'.$ratingCount.'">
</span>
';

$TYTUL = $nazwa;
$OGON .= '<script type="text/javascript" src="'.$config['colorbox.js'].'?ver=1.1"></script>
<script type="text/javascript" src="'.$config['funkcje.js'].'"></script>
<script type="text/javascript" src="/templates/rating/jquery/jRating.jquery.js"></script>
<script type="text/javascript">
$(document).ready(function(){
      // more complex jRating call
      $(".basic").jRating({
         step:true,
         decimalLength: 1,
         rateMax: 5,
         length : 5,
      	isDisabled: '.$ratingDisabled.',
         onSuccess : function(){
         RatingResponse.innerHTML=json.message;
         ;
      }
   });
'."
   $('#infoModal').on('show.bs.modal', function (event) {
     var button = $(event.relatedTarget) // Button that triggered the modal
     var gkid = button.data('gkid') // Extract gkid from data-* attributes
     var ruchid = button.data('ruchid') // Extract ruchid from data-* attributes
     var typeName = button.data('type') // Extract action from data-* attributes

     var modal = $(this)
     modal.find('.modal-content').load( 'comment.php?gkid='+gkid+'&ruchid='+ruchid+'&type='+typeName );
   })
});".'
</script>
<link rel="stylesheet" type="text/css" href="/templates/rating/jquery/jRating.jquery.css?ver=1.2" media="screen" />
<link rel="stylesheet" type="text/css" href="'.$config['colorbox.css'].'?ver=1.2" media="screen"/>
';

$OGON .= '
<script>
	$(document).ready(function(){
	$("a[rel=\'cb\']").colorbox();
	$(".cb").colorbox({
	onComplete:function(){ var x = document.getElementById("text_field"); if (x!=null) {x.focus();} }
	});
	$(".cb2").colorbox();
	});
</script>';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
mysqli_close($link);
$link = null;

require_once 'smarty.php';
