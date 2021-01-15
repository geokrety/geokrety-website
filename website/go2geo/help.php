<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">

<head>
	<meta http-equiv="Content-Type" content="text/xml; charset=UTF-8" />
	<title>go2geo :: resolve geocaching waypoints...</title>
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="/go2geo/go2geo.css" />

</head>
<body>

<h1>go2geo: resolve geocaching waypoints</h1>
<h2>How to use:</h2>

To be redirected to the apropriate page, just type: <b>https://geokrety.org/go2geo/</b> and <b>waypoint name</b>, eg:
<pre>https://geokrety.org/go2geo/op05e5</pre>


<h2>Supported waypoints:</h2>


<?php

$services['geocache'] = 'geocaching databases';
$services['games'] = 'other GPS games';
$services['trackable'] = 'trackable items';

$supported['geocache'][] = ['https://opencaching.pl/', 'Opencaching PL', 'OP', 'OP05E5'];
$supported['geocache'][] = ['https://www.opencaching.de/', 'Opencaching DE', 'OC', 'OC0531'];
$supported['geocache'][] = ['https://opencache.uk/', 'Opencaching UK', 'OK', 'OK0014'];
$supported['geocache'][] = ['http://www.opencaching.nl/', 'Opencaching NL', 'OB', 'OB1A8D'];
$supported['geocache'][] = ['http://www.opencaching.ro/', 'Opencaching RO', 'OR', 'OR00BD'];
$supported['geocache'][] = ['http://opencaching.cz/', 'Opencaching CZ', 'OZ', 'OZ0064'];
$supported['geocache'][] = ['http://www.opencaching.us/', 'Opencaching USA', 'OU', 'OU0004'];

$supported['geocache'][] = ['https://www.geocaching.com/', 'geocaching.com', 'GC', 'GC1X3Z0'];
$supported['geocache'][] = ['http://www.terracaching.com/', 'terracaching', 'TC', 'TCCWU'];
$supported['geocache'][] = ['http://navicache.com/', 'navicache', 'N', 'N00AB3'];
$supported['geocache'][] = ['http://www.gpsgames.org/index.php?option=com_wrapper&wrap=Geocaching', 'Geocaching @gpsgames.org', 'GE', 'GE0174'];
$supported['geocache'][] = ['http://geocaching.com.au/', 'Geocaching Australia', 'GA', 'GA0141'];
$supported['geocache'][] = ['http://www.geocaching.su/', 'GeoCaching Russia', 'GE/ VI/ MS/ TR/ EX/', 'TR/1470'];
$supported['geocache'][] = ['http://www.rejtekhely.ro/', 'Geocaching Transsylvania', 'RH', 'RH0004'];

//$supported['games'][] = Array('http://wpg.alleycat.pl/', 'WaypointGame', 'WPG', 'WPG1180');
$supported['games'][] = ['http://www.waymarking.com/', 'waymarking.com', 'WM', 'WM78XF'];
$supported['games'][] = ['http://www.gpsgames.org/index.php?option=com_wrapper&wrap=Shutterspot', 'ShutterSpot', 'SH', 'SH0030'];
$supported['games'][] = ['http://www.gpsgames.org/index.php?option=com_wrapper&wrap=Geodashing', 'Geodashing', 'GDnn-XXXX', 'GD96-YKIK'];
$supported['games'][] = ['http://trigpointinguk.com/', 'TrigpointingUK', 'TPXXXX', 'TP7379'];

$supported['trackable'][] = ['https://geokrety.org/', 'geokrety.org', 'GK', 'GK05E5'];
$supported['trackable'][] = ['http://www.geocaching.com/track/travelbugs.aspx', 'travelbugs', 'TB', 'TB2771P'];

//$supported[''][] = Array('', '', '', '');
//$supported[''][] = Array('', '', '', '');
//$supported[''][] = Array('', '', '', '');

foreach ($supported as $key => $gra) {
    echo "<p><b>$services[$key]</b></p><table>";
    foreach ($gra as $linia) {
        echo "<tr><td><a href=\"$linia[0]\">$linia[1]</a></td><td>$linia[2]</td><td><a href=\"https://geokrety.org/go2geo/index.php?wpt=$linia[3]\">try it with $linia[3]</a></td></tr>";
    }
    echo '</table>';
}

?>





</body>
</html>
