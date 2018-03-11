<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Join the Race');
$kret_raceTitle = $_POST['raceTitle'];

if ($longin_status['plain'] == null) {
    $TRESC = _('Please login.');
} elseif ($_POST['join'] != 'joined') {
    $link = DBConnect();

    $rn = strval($_GET['rn']);
    $raceid = intval($_GET['raceid']);
    $haslo = strval($_GET['pass']);

    $sql = "SELECT `raceid`, `created`, `raceOwner`, `private`, `haslo`, `raceTitle`, `raceend`, `opis`, `raceOpts`, `wpt`, `targetlat`, `targetlon`, `targetDist`, `targetCaches`, `status` FROM `gk-races` WHERE `status` = '0' ORDER BY `created` DESC ";

    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $private = (($row['private'] == 1) ? '(private)' : '');
        $raceSelect .= '<option '.(($row['raceid'] == $raceid) ? "selected='selected'" : '')." value=' ".$row['raceid']."' label='".$row['raceTitle']."'>".$row['raceTitle']." $private</option>\n";
    }

    // ---------------------------------------------------------------------------------------------- //
    // --------------------------------------- FORMULARZ ------------------------------------------- //
    // ---------------------------------------------------------------------------------------------- //

    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="formularz" />
<input type="hidden" name="join" value="joined" />

<table>

<tr>
<td>Geokret Reference number:</td>
<td><input type="text" name="gk" size="6" maxlength="6" value="'.$rn.'" /><br />
<span class="szare">'._('eg.').' GK020A</span></td>
</tr>

<tr>
<td>'._('The race to join').':</td>
<td><select name="raceid">'.$raceSelect.'</select></td>
</tr>

<tr>
<td>'._('Password for private races').':</td>
<td><input type="text" name="haslo" size="16" maxlength="16" value="'.$haslo.'"/><br />
<span class="szare">'._('If you are the race owner, you don\'t need to enter password').'</span></td>
</tr>

</table>


<p style="width: 200px; margin-top: 40px;"><img src="'.CONFIG_CDN_ICONS.'/info.png" width="22" height="22" alt="warn icon" /> '._('Please read this race rules before joining!').'.</p>
<p><input type="submit" value=" '.$TYTUL.' →" /></p>

</form>

';
} // FORMA koniec

else { // dane z FORMY
    $link = DBConnect();
    foreach ($_POST as $key => $value) {
        $_POST[$key] = mysqli_real_escape_string($link, $value);
    }

    $kret_raceid = intval($_POST['raceid']);
    $kret_gk = strval($_POST['gk']);
    $kret_haslo = intval($_POST['haslo']);

    //print_r($_POST); die();
    if ($kret_raceid == '') {
        $errors[] = _('No race selected');
    } elseif ($kret_gk == '') {
        $errors[] = _('No reference number');
    }

    if (isset($errors)) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors, true, '', 3, 'race join');
        include_once 'smarty.php';
        exit;
    }

    $id = hexdec(substr($kret_gk, 2, 4));

    $userid = $longin_status['userid'];

    // weryfikacja ownera
    $sql = "SELECT `owner`, `nazwa`, `droga`, `skrzynki` FROM `gk-geokrety` WHERE `id` = '$id' LIMIT 1";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_row($result);

    list($gkowner, $gknazwa, $gkdroga, $gkskrzynki) = $row;
    mysqli_free_result($result);
    if ($gkowner != $userid) {
        $errors[] = _('This is not your geokret');
    }

    // weryfikacja rajdu

    $sql = "SELECT `raceOwner`, `private`, `haslo`, `raceTitle`, `raceend`, `opis` FROM `gk-races` WHERE `status` = '0' and `raceid` = '$kret_raceid' LIMIT 1";

    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);
    list($raceOwner, $private, $haslo, $raceTitle, $raceend, $opis) = $row;

    if ($private == 1 and $kret_haslo == '' and ($raceOwner != $userid)) {
        $errors[] = _('This race is private. Please provide the password');
    } elseif ($private == 1 and $kret_haslo != $haslo) {
        $errors[] = _('Wrong password');
    }

    if (isset($errors)) {
        include_once 'defektoskop.php';
        $TRESC = defektoskop($errors, true, '', 3, 'race join');
        include_once 'smarty.php';
        exit;
    }

    // no dobra, wszystko w porządalu chyba, lecimy dalej!

    $sql = "INSERT INTO `gk-races-krety` (
`raceid` ,
`geokretid` ,
`joined`
)
VALUES (
'$kret_raceid', '$id', NOW( )
);
";

    mysqli_query($link, $sql);
    header("Location: /race.php?raceid=$kret_raceid");
    exit();
} // parsowanie danych z formy

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
