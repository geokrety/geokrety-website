<?php

// drawing timeline

function rysuj_timeline($kretid)
{
    include 'templates/konfig.php';
    $link = DBConnect();

    include 'tools/wykresy/jpgraph/jpgraph.php';
    include 'tools/wykresy/jpgraph/jpgraph_gantt.php';

    $NOW = date('Y-m-d');

    // A new graph with automatic size
    $graph = new GanttGraph(750, 130, 'auto');
    $graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH);
    $graph->SetMarginColor('silver@0.8');
    $graph->SetColor('white');

    $result0 = mysqli_query($link,
        "SELECT `data` , `logtype`
FROM `gk-ruchy`
WHERE `id` = '$kretid'
AND `logtype`
IN ('0', '1', '3', '5')
ORDER BY `data`"
    );

    $status = 'OUT'; // good morning

    while ($row = mysqli_fetch_array($result0)) {
        list($data, $logtype) = $row;
        if ($logtype == '0' or $logtype == '3') {
            $logtype = '0';
        } // unification

        if ($logtype == '0' and $status == 'OUT') {       // inserted after removal
            $start = $data;
            $status = 'IN';
        } elseif ($logtype == '1' and $status = 'IN') {     // removed after insertion
            $stop = $data;
            $status = 'OUT';
            $activity = new GanttBar(0, 'IN', $start, $stop);
            $graph->Add($activity);
        } elseif ($logtype == '0' and $status = 'IN') {     // inserted without logging removed
            $stop = $data;
            $activity = new GanttBar(0, 'IN', $start, $stop);
            $graph->Add($activity);
            $start = $data;
            $status = 'IN';
            $rysuj_wykres = 1;
        } else {
            $status = 'OUT';
        }
    }

    if ($status == 'IN') { // IN until today
        $activity = new GanttBar(0, 'IN', $start, $stop);
        $graph->Add($activity);
    }

    // Display the Gantt chart
    if (!empty($activity)) {
        $graph->Stroke("wykresy/$kretid-time.png");
    }
}
