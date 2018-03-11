<?php

require_once '__sentry.php';

$gk = $_GET['gk'];
if ($gk == null || !is_numeric($gk)) {
    exit(1);
}

require 'templates/konfig.php';
$link = DBConnect();
foreach ($_GET as $key => $value) {
    $_GET[$key] = mysqli_real_escape_string($link, strip_tags($value));
}

require 'templates/jpgraph/jpgraph3.5/jpgraph.php';
require 'templates/jpgraph/jpgraph3.5/jpgraph_gantt.php';
require 'templates/jpgraph/jpgraph3.5/jpgraph_mgraph.php';
require 'templates/jpgraph/jpgraph3.5/jpgraph_pie.php';

//$NOW = date("Y-m-d");
$NOW = date('Y-M-d H:m:s');

// ------------------------------------------------ wykres gantta  --------------------------------- //

// A new graph with automatic size

$graph = new GanttGraph(750, 80);

$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH);
$graph->SetMarginColor('silver@0.8');
$graph->SetColor('white');
$graph->scale->month->SetBackgroundColor('lightyellow:1.4');
$graph->scale->year->SetBackgroundColor('lightyellow:1.0');

$graph->SetMargin(2, 2, 2, 2);

$graph->hgrid->Show();
//$graph->hgrid-> line->SetColor('lightblue');
//$graph->hgrid-> SetRowFillColor( 'darkblue@0.9');

$result0 = mysqli_query($link,
    "SELECT `data` , `logtype`
FROM `gk-ruchy`
WHERE `id` = '$gk'
AND `logtype`
IN (
'0', '1', '3'
)
ORDER BY `data`"
);

$status = 'OUT'; // good morning

while ($row = mysqli_fetch_array($result0)) {
    list($data, $logtype) = $row;

    //echo "poczatek: $data $logtype $status<br>";

    if ($logtype == '0' or $logtype == '3') {
        $logtype = '0';
    } // ujednolicenie

    if ($logtype == '5') { // dipped in
        $start = $data;
        $status = 'IN';
        $logtype = 1;
    }

    if (empty($urodziny)) {
        $urodziny = $data;
    }

    if ($logtype == '0' and $status == 'OUT') {       // inserted after removal
        $start = $data;
        $status = 'IN';
    } elseif ($logtype == '1' and $status == 'IN') {    // taken out after inserting
        $stop = $data;
        $status = 'OUT';
        $activity = new GanttBar(0, 'IN', $start, $stop);
        $graph->Add($activity);
        $czas_in = $czas_in + strtotime($stop) - strtotime($start); // czas spedzony w skrzynkach
    } elseif ($logtype == '0' and $status == 'IN') {    // loaded in without logging output
        $stop = $data;
        $activity = new GanttBar(0, 'IN', $start, $stop);
        $graph->Add($activity);
        $czas_in = $czas_in + strtotime($stop) - strtotime($start); // Time spent in boxes
        $start = $data;
        $status = 'IN';
        $rysuj_wykres = 1;
    } else {
        $status = 'OUT';
    }
    //echo "koniec: $data $logtype $status<br>";
}

if ($status == 'IN') { // IN until today
    $stop = $NOW;
    $activity = new GanttBar(0, 'IN', $start, $stop);
    $graph->Add($activity);
    $czas_in = $czas_in + strtotime($stop) - strtotime($start); // Time spent in boxes
}

$czas_total = strtotime($NOW) - strtotime($urodziny);
$czas_out = $czas_total - $czas_in;

if (!empty($activity)) {
    $graph->SetDateRange($urodziny, $NOW);
}

// ------------------------------------------------ wykres kołowy  --------------------------------- //

unset($data);

$graph2 = new PieGraph(400, 400, 'auto');

$czas_in_d = floor($czas_in / 86400);
$data[] = $czas_in;
$lbl[] = ("IN: $czas_in_d days (%.1f%%)");

$czas_in_d = floor($czas_out / 86400);
$data[] = $czas_out;
$lbl[] = ("OUT: $czas_in_d days (%.1f%%)");

// A new pie graph
$graph2 = new PieGraph(520, 400, 'auto');

// Setup title
$graph2->title->Set('GK IN/OUT stats');
$graph2->title->SetMargin(6); // Add a little bit more margin from the top

// Create the pie plot
$p1 = new PiePlot($data);

$p1->SetSliceColors(array('green', 'red', 'blue'));

// Set size of pie
$p1->SetSize(0.36);
$p1->SetCenter(0.5, 0.52);
$p1->SetLabels($lbl);

// Use percentage values in the legends values (This is also the default)
$p1->SetLabelType(PIE_VALUE_PER);

// Add plot to pie graph
$graph2->Add($p1);

// ------------------------------------------------ polaczenie wykresow --------------------------------- //

$mgraph = new MGraph();
$xpos1 = 3; $ypos1 = 3;
$xpos2 = 3; $ypos2 = 100;
$mgraph->Add($graph, $xpos1, $ypos1);
$mgraph->Add($graph2, $xpos2, $ypos2);
$mgraph->Stroke();
