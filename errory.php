<?php

require_once '__sentry.php';

//  wyswietla zawartosc tabeli gk-errory i umozliwia proste operacje na jej rekordach.

require_once 'wybierz_jezyk.php'; // choose the user's language

$TYTUL = _('errors');

$kret_changesev = $_POST['changesev'];
// autopoprawione...
$kret_changevalue = $_POST['changevalue'];
// autopoprawione...
$kret_clear = $_POST['clear'];
// autopoprawione...
$kret_clearall = $_POST['clearall'];
// autopoprawione...
$kret_clearsev = $_POST['clearsev'];
// autopoprawione...
$kret_deletevalue = $_POST['deletevalue'];
// autopoprawione...
$kret_from_id = $_POST['from_id'];
// autopoprawione...
$kret_from_sev = $_POST['from_sev'];
// autopoprawione...
$kret_only_code = $_POST['only_code'];
// autopoprawione...
$kret_select = $_POST['select'];
// autopoprawione...import_request_variables('p', 'kret_');

$g_action = $_GET['action'];
// autopoprawione...
$g_from_id = $_GET['from_id'];
// autopoprawione...
$g_from_sev = $_GET['from_sev'];
// autopoprawione...
$g_only_code = $_GET['only_code'];
// autopoprawione...import_request_variables('g', 'g_');
require 'templates/konfig.php';
$link = DBConnect();

require_once 'longin_chceck.php';
$longin_status = longin_chceck();
$userid = $longin_status['userid'];
//$userid='6262';
$autoryzowany_uzytkownik = (in_array($userid, $config['superusers']) or ($_SERVER['REMOTE_ADDR'] == '127.0.0.1'));

if (!$autoryzowany_uzytkownik) {
    exit;
}

if (count($_GET) == 0) {
    $result = mysqli_query($link, 'SELECT ID FROM `gk-errory` ORDER BY ID DESC LIMIT 1');
    list($last_id) = mysqli_fetch_row($result);

    $result = mysqli_query($link,
        'select
				(select count(id) FROM `gk-errory` where severity >= 4),
				(select count(id) FROM `gk-errory` where severity >= 5),
				(select count(id) FROM `gk-errory` where severity >= 6),
				(select count(id) FROM `gk-errory` where severity >= 7),
				(select count(id) FROM `gk-errory` where severity >= 8),
				(select count(id) FROM `gk-errory` where severity >= 9)
				'
    );
    list($count4, $count5, $count6, $count7, $count8, $count9) = mysqli_fetch_row($result);

    $TRESC .= 'Last error ID: <a href=errory.php?action=view&from_id='.($last_id)."&from_sev=0'>$last_id</a><br/>";
    $TRESC .= '<br/>';
    $TRESC .= "<a href='errory.php?action=view&from_id=".($last_id - 100)."&from_sev=0'>View last 100</a>&nbsp;&nbsp;&nbsp;<a href='errory.php?action=view&from_id=".($last_id - 100)."&from_sev=2'>View last 100, sev 2+</a><br/>";
    $TRESC .= "<a href='errory.php?action=view&from_id=".($last_id - 1000)."&from_sev=0'>View last 1000</a>&nbsp;&nbsp;&nbsp;<a href='errory.php?action=view&from_id=".($last_id - 1000)."&from_sev=2'>View last 1000, sev 2+</a><br/>";
    $TRESC .= '<br/>';
    $TRESC .= "<a href='errory.php?action=view&from_sev=4'>View all sev 4+ (dev debug 1)</a> [$count4]<br/>";
    $TRESC .= "<a href='errory.php?action=view&from_sev=5'>View all sev 5+ (dev debug 2)</a> [$count5]<br/>";
    $TRESC .= "<a href='errory.php?action=view&from_sev=6'>View all sev 6+ (warning low)</a> [$count6]<br/>";
    $TRESC .= "<a href='errory.php?action=view&from_sev=7'>View all sev 7+ (warning high)</a> [$count7]<br/>";
    $TRESC .= "<a href='errory.php?action=view&from_sev=8'>View all sev 8+ (defektoskop/error!)</a> [$count8]<br/>";
    $TRESC .= "<a href='errory.php?action=view&from_sev=9'>View all sev 9  (error!!)</a> [$count9]<br/>";

    echo "<?xml version='1.0' encoding='UTF-8'?><html xmlns='http://www.w3.org/1999/xhtml' xml:lang='pl' lang='pl'><head>$HEAD</head><body>$TRESC</body></html>";

    exit;
}

if (($g_action == 'view') and (isset($kret_clearall))) {
    $sql = 'DELETE FROM `gk-errory`';
    $result = mysqli_query($link, $sql);
    if ($result) {
        $TRESC .= 'Cleared All!<br/>';
    } else {
        $TRESC .= 'NOT Cleared All!<br/>';
    }
} else {
    if (($g_action == 'view') and (isset($kret_clear))) {
        $select .= "'".$kret_select[0]."'";
        for ($i = 1; $i < count($kret_select); ++$i) {
            $select .= ",'".$kret_select[$i]."'";
        }

        $sql = "DELETE FROM `gk-errory` WHERE id IN ($select)";
        $result = mysqli_query($link, $sql);
        if ($result) {
            $TRESC .= "Cleared: ($select)<br/>";
        } else {
            $TRESC .= "NOT Cleared: ($select)<br/>";
        }
    } else {
        if (($g_action == 'view') and (isset($kret_clearsev)) and (ctype_digit($kret_deletevalue))) {
            $sql = "DELETE FROM `gk-errory` WHERE severity='$kret_deletevalue'";
            $result = mysqli_query($link, $sql);
            if ($result) {
                $TRESC .= "Cleared severity=$kret_deletevalue<br/>";
            } else {
                $TRESC .= "NOT Cleared: severity=$kret_deletevalue<br/>";
            }
        } else {
            if (($g_action == 'view') and (isset($kret_changesev)) and (ctype_digit($kret_changevalue))) {
                $select .= "'".$kret_select[0]."'";
                for ($i = 1; $i < count($kret_select); ++$i) {
                    $select .= ",'".$kret_select[$i]."'";
                }

                $sql = "UPDATE `gk-errory` SET `severity`='$kret_changevalue' WHERE id IN ($select)";
                $result = mysqli_query($link, $sql);
                if ($result) {
                    $TRESC .= "Changed severity to $kret_changevalue for: ($select)<br/>";
                } else {
                    $TRESC .= "NOT changed: ($select)<br/>";
                }
            }
        }
    }
}

if ($g_action == 'view') {
    echo $pfile;
    $TRESC .= '';

    $HEAD = "<meta http-equiv='Content-Type' content='text/xml; charset=UTF-8' /><style>
body, .male, .bardzomale, h1, h2, h3, td {font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;}
body, td {font-size: 9pt; color: #000000;text-decoration: none;}
table { width: 100%; padding: 0px; border-spacing:1px; border: 1px solid #789DB3;}
table tr { padding : 10px; }
table td {
	border: none;
	background-color: #F4F4F4;
	vertical-align: middle;
	padding: 2px;  }
table tr.special_med td { border: 1px solid #ff9999; background-color: #eedddd; }
table tr.special_high td { border: 1px solid #ff0000; background-color: #ffaaaa; }
table tr.special_0 td { background-color: #f3f3f3; }
table tr.special_1 td { background-color: #C1E1B0; }
table tr.special_2 td { background-color: #CACACA; }
table tr.special_3 td { background-color: #E1CEA9; }
table tr.special_4 td { background-color: #F3B5F7; }
table tr.special_5 td { background-color: #AAD0F7; }
table tr.special_6 td { background-color: #F7F17E; }
table tr.special_7 td { background-color: #ffC800; }
table tr.special_8 td { background-color: #FF8737; }
table tr.special_9 td { background-color: #FF5E5E; }
td.mid {text-align: center}
.xs { font-size: 8pt;}
</style>";

    if (!empty($g_from_id)) {
        $kret_from_id = $g_from_id;
    }
    $kret_from_id = trim($kret_from_id);
    if (ctype_digit($kret_from_id)) {
        $kret_from_id = mysqli_escape_string($link, $kret_from_id);
        $from_id = "AND id>=$kret_from_id";
        $gg_from_id = "&from_id=$kret_from_id";
    }

    if (!empty($g_from_sev)) {
        $kret_from_sev = $g_from_sev;
    }
    $kret_from_sev = trim($kret_from_sev);
    if (ctype_digit($kret_from_sev)) {
        $kret_from_sev = mysqli_escape_string($link, $kret_from_sev);
        $from_sev = "AND severity>=$kret_from_sev";
        $gg_from_sev = "&from_sev=$kret_from_sev";
    }

    if (!empty($g_only_code)) {
        $kret_only_code = $g_only_code;
    }
    if (!empty($kret_only_code)) {
        $kret_only_code = mysqli_escape_string($link, $kret_only_code);
        $only_code = "AND uid='$kret_only_code'";
        $gg_only_code = "&only_code=$kret_only_code";
    }

    $sql = "SELECT id, uid, userid, ip, date, file, details, severity FROM `gk-errory` WHERE 1 $from_id $from_sev $only_code ORDER BY id DESC";

    if (!isset($kret_changevalue)) {
        $kret_changevalue = '99';
    }
    if (!isset($kret_deletevalue)) {
        $kret_deletevalue = '0';
    }

    $me = $_SERVER['PHP_SELF'].'?action=view';
    $TRESC .= "<form action='$me' method='post' />";

    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_row($result);
    list($najnowsze_id) = $row;
    if ($najnowsze_id == null) {
        $najnowsze_id = $g_from_id;
    }

    $TRESC .= "<a href='errory.php'>Home</a> ";
    $TRESC .= "<a href='errory.php?action=view&from_id=$najnowsze_id$gg_from_sev$gg_only_code'>Refresh new</a> ";
    $TRESC .= "<a href='errory.php?action=view$gg_from_id$gg_from_sev$gg_only_code'>This page</a> Top id=$najnowsze_id ";
    $TRESC .= '&nbsp;&nbsp;&nbsp;&nbsp;<b>FILTER</b> ';
    $TRESC .= "id: <input type='textbox' name='from_id' value='$kret_from_id' size='7'/>";
    $TRESC .= "&nbsp;&nbsp;code: <input type='textbox' name='only_code' value='$kret_only_code' size='15'/>";
    $TRESC .= "&nbsp;&nbsp;min severity: <input type='textbox' name='from_sev' value='$kret_from_sev' size='1'/>";
    $TRESC .= "&nbsp;&nbsp;<input type='submit' name='filter' value='Filter' />";
    $TRESC .= "<br/><input type='submit' name='changesev' value='Change Severity to:' />";
    $TRESC .= "<input type='textbox' name='changevalue' value='$kret_changevalue' size='1'/>";
    $TRESC .= "&nbsp;&nbsp;&nbsp;<input type='submit' name='clear' value='Delete Selected' />";
    $TRESC .= "&nbsp;&nbsp;&nbsp;<input type='submit' name='clearsev' value='Delete Severity:' />";
    $TRESC .= "<input type='textbox' name='deletevalue' value='$kret_deletevalue' size='1'/>";
    $TRESC .= " * <input type='submit' name='clearall' value='Delete All' /> * ";

    $TRESC .= "<br /><hr /><table><thead><tr>
	<th width='5%'>id</th>
	<th width='12%'>code</th>
	<th width='3%'>user</th>
	<th width='8%'>ip</th>
	<th width='10%'>time</th>
	<th width='25%'>file</th>
	<th width='34%'>details</th>
	<th width='3%'>sev.</th>
	</tr></thead>";

    $TRESC .= "
<tr class='special_0'>	<td></td><td>0 debug etc</td><td></td><td></td><td></td><td></td><td>taki zwykly debug print</td><td></td></tr>
<tr class='special_1'>	<td></td><td>1 success info</td><td></td><td></td><td></td><td></td><td>info o poprawnym wykonaniu operacji</td><td></td></tr>
<tr class='special_2'>	<td></td><td>2 timeout</td><td></td><td></td><td></td><td></td><td>timeouty</td><td></td></tr>
<tr class='special_3'>	<td></td><td>3 defektoskop low</td><td></td><td></td><td></td><td></td><td>domyslnie dla defektoskopa</td><td></td></tr>
<tr class='special_4'>	<td></td><td>4 dev debug 1</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr class='special_5'>	<td></td><td>5 dev debug 2</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr class='special_6'>	<td></td><td>6 warning low</td><td></td><td></td><td></td><td></td><td>dziwne..</td><td></td></tr>
<tr class='special_7'>	<td></td><td>7 warning high</td><td></td><td></td><td></td><td></td><td>..i dziwniejsze</td><td></td></tr>
<tr class='special_8'>	<td></td><td>8 defektoskop/error!</td><td></td><td></td><td></td><td></td><td>bledy/sytuacje ktore raczej nie powinny miec miejsca</td><td></td></tr>
<tr class='special_9'>	<td></td><td>9 error!!</td><td></td><td></td><td></td><td></td><td>masakra</td><td></td></tr>";

    $result = mysqli_query($link, $sql);

    $TRESC .= ' number of records: '.mysqli_num_rows($result);

    if ($g_action == 'view' && $result) {
        $TRESC .= '';
        while ($row = mysqli_fetch_array($result)) {
            list($f_id, $f_uid, $f_userid, $f_ip, $f_date, $f_file, $f_details, $f_severity) = $row;

            //splits long words which would otherwise break the table design
            //$f_file = preg_replace("/(([^\s\&]|(\<[\S]+\>)){10})/u", "$1&shy;",$f_file);

            if ($f_ip == '62.121.108.70') {
                $f_ip = 'geokrety.org';
            } elseif ($f_ip == '86.111.244.117') {
                $f_ip = 'opencaching.PL';
            } elseif ($f_ip == '212.2.32.87') {
                $f_ip = 'opencaching.DE';
            } elseif ($f_ip == '184.106.211.113') {
                $f_ip = 'opencaching.US';
            } elseif ($f_ip == '46.4.66.184') {
                $f_ip = 'opencaching.NL';
            } elseif ($f_ip == '74.117.232.69') {
                $f_ip = 'trekkingklub.com';
            }

            if ($f_severity >= 100) {
                $special = '9';
            } else {
                $special = $f_severity;
            }
            //else if ($f_severity>0) $special = 'special_med'; else $special = '';
            $user_link = "<a href='/mypage.php?userid=$f_userid'>$f_userid</a>";
            $f_details = "<div class='xs'>$f_details</div>";
            $TRESC .= "
<tr class='special_$special'>
	<td class='xs'><input type=checkbox name='select[]' value='$f_id'>$f_id</td>
	<td class='mid xs'>$f_uid</td>
	<td class='mid xs'>$user_link</td>
	<td class='mid xs'>$f_ip</td>
	<td class='mid xs'>$f_date</td>
	<td><div style='max-width:300px;min-height:50px;max-height:250px;overflow:auto'>$f_file</div></td>
	<td><div style='width:100%;max-height:250px;overflow:auto'>$f_details</div></td>
	<td class='mid'>$f_severity</td>
</tr>";
        }
    }
    $TRESC .= '</table><hr/>';

    $TRESC .= '</form>';

    //include_once('smarty.php');
    echo "<?xml version='1.0' encoding='UTF-8'?><html xmlns='http://www.w3.org/1999/xhtml' xml:lang='pl' lang='pl'><head>$HEAD</head><body>$TRESC</body></html>";

    exit;
}
