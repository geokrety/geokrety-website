<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$OGON = "<script type='text/javascript' src='https://www.google.com/jsapi'></script>
    <script type='text/javascript'>
     google.load('visualization', '1', {'packages': ['geochart']});
     google.setOnLoadCallback(drawRegionsMap);

      function drawRegionsMap() {
        var data = google.visualization.arrayToDataTable([
          ['Country', 'Geokrets'],
          ".file_get_contents($config['generated'].'country_stat_google.html')."
        ]);

        var options = {region: 'world', colors: ['orange', 'red']};

        var chart = new google.visualization.GeoChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    };
    </script>
";

$TYTUL = _('Stats by country');

$TRESC = '<p>'._('Where are GeoKrety now?').'</p>
<p><div id="chart_div" style="width: 700px; height: 500px;"></div></p>
<p><img src="'.CONFIG_CDN_IMAGES.'/wykresy/country.png" width="450" height="350" alt="stat by country" longdesc="stat by country" /></p>
';

$TRESC .= file_get_contents($config['generated'].'country_stat.html');

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
