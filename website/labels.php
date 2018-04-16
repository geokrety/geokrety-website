<?php

require_once '__sentry.php';

// perform a search śćółżźńóó

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Print label');

$link = DBConnect();

$id = mysqli_real_escape_string($link, $_GET['id']);                        // id kreta
$nr = mysqli_real_escape_string($link, $_GET['nr']);
$userid_longin = $longin_status['userid'];

$result = mysqli_query($link,
    "SELECT `gk-geokrety`.`id`, `nr`, `gk-geokrety`.`nazwa`, `gk-geokrety`.`opis`, `gk-geokrety`.`owner`, `gk-users`.`user`, `gk-geokrety`.`data`, `gk-geokrety`.`typ`
FROM `gk-geokrety`
LEFT JOIN `gk-users` ON `gk-geokrety`.`owner` = `gk-users`.userid
WHERE `gk-geokrety`.`id`='$id' AND `gk-geokrety`.`nr`='$nr'
LIMIT 1"
);

list($id, $tracking, $nazwa, $opis, $userid, $owner, $data, $typ) = mysqli_fetch_array($result);
mysqli_free_result($result);

$opis = preg_replace("[\[<a href=\"(.+?)\">Link</a>\]]", '$1', $opis);

$conaco = array("\n" => ' ', '  ' => ' ');
$opis = strtr(strip_tags($opis), $conaco);
$id = sprintf('GK%04X', $id);

// ------ języki dostępne -----//

$jezyki_preferowane = array('en', 'pl');

foreach ($config_jezyk_nazwa as $jezyk_skrot => $jezyk) {
    if (in_array($jezyk_skrot, $jezyki_preferowane)) {
        $selected = 'selected="selected"';
    } else {
        $selected = '';
    }
    $jezyki .= "<option value=\"$jezyk_skrot\" $selected>$jezyk ($jezyk_skrot)</option>\n";
}

// ------------------------------------ //

$TRESC = '<form action="templates/labels/index.php" method="POST">
<table>
<tbody>
  <tr>
    <td>'._('GeoKret name').':</td><td>'.$nazwa.'</td>
  </tr>
  <tr>
    <td>'._('Owner').':</td><td>'.$owner.'</td>
  </tr>
  <tr>
    <td>Tracking Code:</td><td>'.$tracking.'</td>
  </tr>
  <tr>
    <td>Reference number:</td><td>'.$id.'</td>
  </tr>
  <tr>
    <td>'._('Comment').':</td><td>
		<span class="szare">'._('Edit this text to fit your needs as well as the label size').':</span>
		<textarea cols="50" rows="10" name="opis">'.rawurldecode($opis).'</textarea></td>
  </tr>
  <tr>
    <td>'._('Label template').':</td>
		<td><select id="szablon" name="szablon" size=6>
					<option value="0">Small</option>
					<option value="1">Medium</option>
					<option value="2">Normal</option>
					<option value="3" selected="selected">With QR Code</option>
					<option value="5">SVG classic (beta)</option>
					<option value="6">SVG circle (beta)</option>
					<option value="png1">Modern :: Wallson (beta)</option>
					<option value="png2">Classic :: filips (beta)</option>
					<option value="png3">Middle classic :: filips (beta)</option>
					<option value="png4">Modern :: Schrottie (beta)</option>
					<option value="7">SVG PostStamp (beta)</option>
					</select></td>
  </tr>
  <tr>
    <td>'._('Help languages').':</td>
		<td><span class="szare">'._('Please note: not all translations are avaliable. Translations may not work with some templates. English is added by default, so you may not select it.').'</span><br />
		<select multiple="multiple" id="helplang[]" name="helplang[]" size=10>
'.$jezyki.'
					</select></td>
  </tr>
</tbody>
</table>


<input TYPE="hidden" VALUE="'.$nazwa.'" NAME="nazwa">
<input TYPE="hidden" VALUE="'.$owner.'" NAME="owner">
<input TYPE="hidden" VALUE="'.$tracking.'" NAME="tracking">
<input TYPE="hidden" VALUE="'.$id.'" NAME="id">


<input type="submit" value="OK" />
</form>



';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
