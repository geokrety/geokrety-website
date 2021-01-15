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

    $TRESC = '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" name="formularz" />
<input type="hidden" name="join" value="joined" />

<table>

<tr>
<td>Geokret Reference number:</td>
<td><input type="text" name="gk" size="7" maxlength="7" value="'.$rn.'" /><br />
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
    try {
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
            throw new Exception();
        } elseif ($kret_gk == '') {
            $errors[] = _('No reference number');
            throw new Exception();
        }

        $id = hexdec(substr($kret_gk, 2));

        $userid = $longin_status['userid'];

        // weryfikacja ownera
        $sql = "SELECT `owner`, `nazwa`, `droga`, `skrzynki` FROM `gk-geokrety` WHERE `id` = '$id' LIMIT 1";
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_row($result);

        if (!$row) {
            $errors[] = _('Unknown geokret');
            throw new Exception();
        }

        list($gkowner, $gknazwa, $gkdroga, $gkskrzynki) = $row;
        mysqli_free_result($result);
        if ($gkowner != $userid) {
            $errors[] = _('This is not your geokret');
            throw new Exception();
        }

        // weryfikacja rajdu

        $sql = "SELECT `raceOwner`, `private`, `haslo`, `raceTitle`, `raceend`, `opis`, `status` FROM `gk-races` WHERE `raceid` = '$kret_raceid' LIMIT 1";

        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_row($result);
        mysqli_free_result($result);
        list($raceOwner, $private, $haslo, $raceTitle, $raceend, $opis, $status) = $row;

        if (!$row) {
            $errors[] = _('Unknown race');
            throw new Exception();
        } elseif ($private == 1 and $kret_haslo == '' and ($raceOwner != $userid)) {
            $errors[] = _('This race is private. Please provide the password');
            throw new Exception();
        } elseif ($private == 1 and $kret_haslo != $haslo) {
            $errors[] = _('Wrong password');
            throw new Exception();
        } elseif ($status != 0) {
            $errors[] = _('This race already started. Please select another race.');
            throw new Exception();
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

        $result = mysqli_query($link, $sql);
        if (!$result) {
            $errors[] = _('Internal error, please report');
            error_log('race_join sql:'.$sql.' error:'.mysqli_error($link), 0);
            throw new Exception();
        }

        header("Location: /race.php?raceid=$kret_raceid");
        exit();
    } catch (Exception $e) {
        if (isset($errors) && (count($errors) > 0)) {
            include_once 'defektoskop.php';
            $TRESC = defektoskop($errors, true, '', 3, 'race join');
            include_once 'smarty.php';
        }
    }
} // parsowanie danych z formy

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
