<?php

require_once '__sentry.php';

// this page registeres a new GeoKret śćńółż

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = '1,2,3,5,8... Generatorek :-)';
$OGON = '<script type="text/javascript" src="'.$config['funkcje.js'].'"></script>';     // character counters

$kret_count = $_POST['count'];
// autopoprawione...
$kret_counter_symbol = $_POST['counter_symbol'];
// autopoprawione...
$kret_id = $_POST['id'];
// autopoprawione...
$kret_nazwa = $_POST['nazwa'];
// autopoprawione...
$kret_oc_alphabet = $_POST['oc_alphabet'];
// autopoprawione...
$kret_oc_prefix = $_POST['oc_prefix'];
// autopoprawione...
$kret_oc_random_length = $_POST['oc_random_length'];
// autopoprawione...
$kret_oc_suffix = $_POST['oc_suffix'];
// autopoprawione...
$kret_opis = $_POST['opis'];
// autopoprawione...
$kret_owner = $_POST['owner'];
// autopoprawione...
$kret_padding = $_POST['padding'];
// autopoprawione...
$kret_start = $_POST['start'];
// autopoprawione...
$kret_tc_alphabet = $_POST['tc_alphabet'];
// autopoprawione...
$kret_tc_prefix = $_POST['tc_prefix'];
// autopoprawione...
$kret_tc_random_length = $_POST['tc_random_length'];
// autopoprawione...
$kret_tc_suffix = $_POST['tc_suffix'];
// autopoprawione...
$kret_typ = $_POST['typ'];
// autopoprawione...import_request_variables('p', 'kret_');

$userid = $longin_status['userid'];
if (!in_array($userid, $config['superusers'])) {
    exit;
}

//----------- FORM -------------- //

if ($longin_status['plain'] == null) {
    $TRESC = _('Please login.');
} elseif ((!isset($kret_count))) { //--------------------  if NOT all required variables are set
    $TRESC = '<form action="'.$_SERVER['PHP_SELF'].'" method="post" />
<table>
<tr>
<td width="30%">How many GeoKrets to create?</td>
<td><input type="text" name="count" size="5" /></td>
</tr>
<tr>
<td>Owner ID:</td>
<td><input type="text" name="owner" size="5" value="0"/></td>
</tr>
<tr>
<td>Common Name:</td>
<td><input type="text" name="nazwa" size="40" maxlength="45" /> Counter: <input type="text" name="counter_symbol" size="1" value="{c}" /> Start at: <input type="text" name="start" size="1" value="1" /> Digits: <input type="text" name="padding" size="1" value="0" /></td>
</tr>

<tr>
<td colspan="2"><hr noshade="noshade" size="2" /></td>
</tr>

<tr>
<td><b>Tracking Code Format:</b></td>
<td><b>[prefix-string][N random characters from alphabet][suffix-string]</b></td>
</tr>
<tr>
<td>Tracking Code Prefix:</td>
<td><input type="text" name="tc_prefix" size="5" /></td>
</tr>
<tr>
<td>Tracking Code N-length:</td>
<td><input type="text" name="tc_random_length" size="5" value="6"/></td>
</tr>
<tr>
<td>Tracking Code Alphabet *:<br/><small>* or final TC, only if creating 1 gk!</small></td>
<td><input type="text" name="tc_alphabet" size="60" value="a b c d e f g h i j k l m n p q r s t u v w x y z 1 2 3 4 5 6 7 8 9"/></td>
</tr>
<tr>
<td>Tracking Code Suffix:</td>
<td><input type="text" name="tc_suffix" size="5" maxlength="45" /></td>
</tr>

<tr>
<td colspan="2"><hr noshade="noshade" size="2" /></td>
</tr>

<tr>
<td><b>Owner Code Format:</b></td>
<td><b>[prefix-string][N random characters from alphabet][suffix-string]</b></td>
</tr>
<tr>
<td>Owner Code Prefix:</td>
<td><input type="text" name="oc_prefix" size="5" /></td>
</tr>
<tr>
<td>Owner Code N-length:</td>
<td><input type="text" name="oc_random_length" size="5" value="6"/></td>
</tr>
<tr>
<td>Owner Code Alphabet *:<br/><small>* or final OC (max 20 chars)</small></td>
<td><input type="text" name="oc_alphabet" size="60" value="0 1 2 3 4 5 6 7 8 9"/></td>
</tr>
<tr>
<td>Owner Code Suffix:</td>
<td><input type="text" name="oc_suffix" size="5" maxlength="45" /></td>
</tr>

<tr>
<td colspan="2"><hr noshade="noshade" size="2" /></td>
</tr>

<tr>
<td>Geokret type</td>
<td>
<select size="1" name="typ">
<option value="0">'._('Traditional').'</option>
<option value="1">'._('A book/CD/DVD...').'</option>
<option value="2">'._('A human').'</option>
<option value="3">'._('A coin').'</option>
</select>
</td>
</tr>
<tr>
<td>'._('Comment').':</td>
<td><textarea class="raz" name="opis" rows="7" cols="40" maxlength="5120" id="poledoliczenia" onkeyup="zliczaj(5120)"></textarea><br />
<span class="szare"><input id="licznik" disabled="disabled" type="text" size="3" name="licznik" /> '._('characters left').'</span></td>
</tr>
</table>
<input type="submit" value=" go! " /></form>
';
}
//=============================  if NOT all required variables are set ====================
else {
    // ------------- Almost everything is ok, proceed (create a new geokret)

    $link = DBConnect();

    include_once 'random_string.php';
    include_once 'czysc.php';

    $nazwa = czysc($kret_nazwa);
    $opis = czysc($kret_opis);
    $owner = $longin_status['userid'];

    include_once 'register.fn.php';
    include_once 'owner_code.fn.php';
    $TRESC .= '<pre>NO;URL;REFERENCE;TRACKING CODE;OWNER CODE;NAME<br/>';
    for ($i = 1; $i <= $kret_count; ++$i) {
        $j = str_pad($i - 1 + $kret_start, $kret_padding, '0', STR_PAD_LEFT);
        $nazwa = str_replace($kret_counter_symbol, $j, $kret_nazwa);

        if ($kret_count == '1' and strpos($kret_tc_alphabet, ' ') === false) {
            $tc = $kret_tc_alphabet;
        } else {
            $tc = '';
        }

        if ($kret_owner == '0') {
            $kret_id = registerNewGeoKret($nazwa, $kret_opis, $kret_owner, $kret_typ, false, $tc, $kret_tc_prefix, $kret_tc_random_length, $kret_tc_suffix, $kret_tc_alphabet);

            if (strpos($kret_oc_alphabet, ' ') === false) {
                $ac = $kret_oc_alphabet;
            } else {
                $ac = '';
            }

            // ($ac=="") -> jezeli podalismy nasz ac to wtedy wylaczamy sprawdzanie duplikatow bo mozemy ustalic jeden ac dla wszystkich nowych kretow.
            // ale jezeli podany jest alfabet to $ac jest puste i nie chcemy duplikatow.
            $ac = createNewOwnerCode($kret_id, ($ac == ''), $ac, $kret_oc_prefix, $kret_oc_random_length, $kret_oc_suffix, $kret_oc_alphabet);
        } else {
            $kret_id = registerNewGeoKret($nazwa, $kret_opis, $kret_owner, $kret_typ, true, $tc, $kret_tc_prefix, $kret_tc_random_length, $kret_tc_suffix, $kret_tc_alphabet);
        }

        $TRESC .= "$i;".$config['adres']."konkret.php?id=$kret_id;".sprintf('GK%04X', $kret_id).";$tc;$ac;$nazwa
";
    }

    $TRESC .= '</pre>';

    // if ($kret_id <> 0) header("Location: konkret.php?id=$kret_id");
        // else $TRESC="Error, please try again later...";
} //if all required variables are set

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
