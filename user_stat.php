<?php

require_once '__sentry.php';

if (count($_GET) == 0) {
    exit;
} //bez parametow od razu wychodzimy

$userid = intval($_GET['userid']);

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

require 'templates/konfig.php';
$link = DBConnect();

$OGON = "<script type='text/javascript' src='https://www.google.com/jsapi'></script>";

// ------------------------------------------- statystyki różne ------------------------------ //

// -------------------------------------------------------- MEDIANA ----------------------------------- //
$medianaSQL = "SELECT t1.droga as median_val FROM (
SELECT @rownum:=@rownum+1 as `row_number`, d.droga
  FROM `gk-ruchy` as d,  (SELECT @rownum:=0) r
  WHERE d.droga > 0 and d.user = '$userid' and d.logtype in ('0', '5')
  ORDER BY d.droga
) as t1,
(
  SELECT count(*) as total_rows
  FROM `gk-ruchy` as d
  WHERE d.droga > 0 and d.user = '$userid' and d.logtype in ('0', '5')
) as t2
WHERE t1.row_number=floor(total_rows/2)+1;";

$result = mysqli_query($link, $medianaSQL);
$row = mysqli_fetch_row($result); $mediana = $row[0];

// -------------------------------------------------------- SREDNIA ----------------------------------- //

$sredniaSQL = "SELECT round(avg(droga)) FROM `gk-ruchy` WHERE droga > 0 and user = $userid and logtype in ('0', '5')";
$result = mysqli_query($link, $sredniaSQL);

$row = mysqli_fetch_row($result); $srednia = $row[0];

// ------ globalne śednie itp ------ //
$sql = "SELECT * FROM `gk-wartosci` WHERE `name` = 'droga_mediana' limit 1";
$result = mysqli_query($link, $sql); $row = mysqli_fetch_array($result); $global_mediana = $row[1];
$sql = "SELECT * FROM `gk-wartosci` WHERE `name` = 'droga_srednia' limit 1";
$result = mysqli_query($link, $sql); $row = mysqli_fetch_array($result); $global_srednia = $row[1];

// ---------------------------------------------------------------------- country stat ----------------------- //
$sql = "SELECT country, count(`ruch_id`) as ile FROM `gk-ruchy` WHERE  user = '$userid' group by `country` order by ile desc";

$result = mysqli_query($link, $sql);

// $KRAJE_HTML = "<table><tr><th>Country</th><th>#logs</th></tr>";
while ($row = mysqli_fetch_array($result)) {
    $KRAJE_MAPA .= "['".$row[0]."',\n".$row[1].'],';
    //  $KRAJE_HTML .="<tr><td>$row[0]</td><td>$row[1]</td></tr>";
}
// $KRAJE_HTML .= "</table>";

$OGON .= "<script type='text/javascript'>
     google.load('visualization', '1', {'packages': ['geochart']});
     google.setOnLoadCallback(drawRegionsMap);

      function drawRegionsMap() {
        var data = google.visualization.arrayToDataTable([
          ['Country', 'Logs'],
          ".$KRAJE_MAPA."
        ]);

        var options = {region: 'world', colors: ['orange', 'red']};

        var chart = new google.visualization.GeoChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    };
    </script>
";

// ---------------------------------------------------------------------- logtype stats ----------------------- //

$sql = "SELECT logtype, count(`ruch_id`) as ile FROM `gk-ruchy` WHERE  user = '$userid' group by `logtype` order by ile desc";

$result = mysqli_query($link, $sql);

$WYKRES1 = '';
// $HTML1 = "<table><tr><th>Logtype</th><th>#logs</th></tr>";
while ($row = mysqli_fetch_array($result)) {
    $typlogu = $cotozalog[$row[0]];
    $WYKRES1 .= "['".$typlogu."',".$row[1]."],\n";
    //  $HTML1 .="<tr><td>$typlogu</td><td>$row[1]</td></tr>";
}
// $HTML1 .= "</table>";

$OGON .= '   <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
        ["Logtype", "#logs"],
'.$WYKRES1.'
        ]);

        var chart = new google.visualization.PieChart(document.getElementById("chart_div2"));
        chart.draw(data);
      }
    </script>';

// ---------------------------------------------------------------------- statystyki roczne :: ruchy ----------------------- //

$sql = "SELECT year(data) as rok, count(`ruch_id`) as ile FROM `gk-ruchy` WHERE  user = '$userid' and `data` > '2007' group by `rok`";

$result = mysqli_query($link, $sql);

$WYKRES2 = '';
// $HTML2 = "<table><tr><th>Year</th><th>#logs</th></tr>";
while ($row = mysqli_fetch_array($result)) {
    $WYKRES2 .= "['".$row[0]."',".$row[1]."],\n";
    //  $HTML2 .="<tr><td>" . $row[0] . "</td><td>$row[1]</td></tr>";
}
// $HTML2 .= "</table>";

$OGON .= '   <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
        ["Year", "#logs"],
'.$WYKRES2.'
        ]);

        var chart = new google.visualization.BarChart(document.getElementById("chart_div3"));
        chart.draw(data);
      }
    </script>';

// ---------------------------------------------------------------------- statystyki roczne :: zarejestrowane ----------------------- //

$sql = "SELECT year(data) as rok, count(id) FROM `gk-geokrety` WHERE owner = '$userid' and data >= '2007-01-01' group by rok";

$result = mysqli_query($link, $sql);

$WYKRES3 = '';
// $HTML3 = "<table><tr><th>Year</th><th>#geokrets</th></tr>";
while ($row = mysqli_fetch_array($result)) {
    $WYKRES3 .= "['".$row[0]."',".$row[1]."],\n";
    //  $HTML3 .="<tr><td>" . $row[0] . "</td><td>$row[1]</td></tr>";
}
// $HTML3 .= "</table>";

$OGON .= '   <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
        ["Year", "#geokrets"],
'.$WYKRES3.'
        ]);
							var options = {colors: ["red"]};
        var chart = new google.visualization.BarChart(document.getElementById("chart_div4"));
        chart.draw(data, options);
      }
    </script>';

// ---------------------------------------------------------------------- my portfolio ----------------------- //

$sql = "SELECT typ, count(id) FROM `gk-geokrety` WHERE owner = '$userid' group by typ";

$result = mysqli_query($link, $sql);

$WYKRES4 = '';
while ($row = mysqli_fetch_array($result)) {
    $typ = $cotozakret[$row[0]];
    $WYKRES4 .= "['".$typ."',".$row[1]."],\n";
}

$OGON .= '   <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
        ["GK type", "count"],
'.$WYKRES4.'
        ]);
        var chart = new google.visualization.PieChart(document.getElementById("chart_div5"));
        chart.draw(data);
      }
    </script>';

// ---------------------------------------------------------- treść --------------------------------//

$TYTUL = _('User stats');

$TRESC = '<h1>'._('Statistics').'</h1>
<table style="width:400px;">
<tr>
<td></td>
<th>'._('Me').'</th>
<th>'._('All users').'</th>
</tr>
<tr>
<td>'._('Average move distance').'</td>
<td>'.$srednia.'km</td>
<td>'.$global_srednia.'km</td>
</tr>
<tr>
<td>'._('Median move distance').'</td>
<td>'.$mediana.'km</td>
<td>'.$global_mediana.'km</td>
</tr>
</table>

<h1>'._('Country stats').'</h1>
<h2>'._('Logs').'</h2>
<p><div id="chart_div" style="width: 700px; height: 500px;"></div></p>

<h1>'._('Logtypes').'</h1>
<p><div id="chart_div2" style="width: 600px; height: 400px;"></div></p>

<h1>'._('Year stats').'</h1>
<h2>'._('Moves').'</h2>
<p><div id="chart_div3" style="width: 600px; height: 400px;"></div></p>
<h2>'._('Geokrety created').'</h2>
<p><div id="chart_div4" style="width: 600px; height: 400px;"></div></p>

<h1>'._('Portfolio').'</h1>
<p><div id="chart_div5" style="width: 600px; height: 400px;"></div></p>

';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
