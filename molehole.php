<?php

require_once '__sentry.php';

// molehole system

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 3200; // this page should be cached for n seconds
require_once 'smarty_start.php';

require 'waypoint_info.php';   // for the list of hotels and motels

$TYTUL = ('GK mole-holes and GK hotels/motels');

$TRESC = '

<h2>GK mole-hole-system</h2>

<p><strong>Draft for basic recommendations for the GK mole-hole-system:</strong><br />by Grimpel</p>';

$TRESC .= '<ol>
<li>The system is based on mole-to-mole (peer-to-peer) tunnels. A mole-hole-cache should have a single dedicated destination. A dedicated destination doesn´t mean a special cache - an arriving GK may appear in any cache within the destination area.</li>
<li>The tunnel should work in both directions. It takes two GK friends to run a tunnel. Each partner is running a mole-hole-cache, collecting the GK and sending them over to the other partner, who is spreading the incoming GK in his home zone.</li>
<li>The tunnel should cross a border or, if it is within a country, the distance between the mole-holes should be at least 500 km.</li>
<li>GeoKrets in a mole-hole are impatient, so no GK should wait longer than two months for transportation.</li>
<li>Each listing of a mole-hole-cache should include</li>
      <ul>
        <li>a title "Mole-hole (specific name where the cache is located)" e.g. "Mole-hole Pomeranczarnia"</li>
        <li>a subtitle "GeoKretExpress (departure area) - (destination area)" e.g. "GeoKretExpress Warszawa - Berlin"</li>
        <li>a standard text, explaining the mole-hole-system (and maybe a link to geokrety.org/mole-hole-system?)</li>
        <li>some information about the end of the tunnel (destination area, partner, partner-mole-hole-cache)</li>
        <li>And probably detailed information about the mole-hole-cache and the neighbourhood, how to get there, hint, spoiler and so on.</li>
      </ul>
</ol>';

$TRESC .= '<table>';

$lista_dziur[] = 'OP1891';
$lista_dziur[] = 'OC7DCB';
$lista_dziur[] = 'OK0085';
$lista_dziur[] = 'OP3373';
$lista_dziur[] = 'OCBEA7';
$lista_dziur[] = 'OP3B15';
$lista_dziur[] = 'OCCB8A';
$lista_dziur[] = 'OCC3EC';
$lista_dziur[] = 'OCC8B7';

//$lista_dziur[] = "";
//$lista_dziur[] = "";
//$lista_dziur[] = "";

foreach ($lista_dziur as $waypoint) {
    list(, , $name, $typ, $kraj, $linka, , $country, $status) = waypoint_info($waypoint);

    if ($status == 1) {
        $status_obr = '<img src="'.CONFIG_CDN_IMAGES.'/icons/ok.png" alt="OK!" title="Status: Ready for search" />';
    } else {
        $status_obr = '<img src="'.CONFIG_CDN_IMAGES.'/icons/error.png" alt="error" title="Status: not ready to search" />';
    }
    //var_dump(waypoint_info($waypoint));
    $TRESC .= "<tr><td><strong>$waypoint</strong></td><td>$status_obr<td><a href=\"$linka\">$name</a></td><td>$typ</td>
	<td><img src=\"".CONFIG_CDN_IMAGES."/country-codes/$country.png\" /> $kraj</td></tr>\n";
}

$TRESC .= '</table>

<p>This subject <a href="http://forum.opencaching.pl/viewtopic.php?t=3593">is being discussed here</a>.</p>

<h2>GK hotels / motels</h2>';

$TRESC .= '<p>'._('a GK hotel/motel is an easy-to-reach cache, close to an airport/motorway/railroad station, big enough to host some GK. Cachers may grab or drop GK').'</p>';

$TRESC .= '<p>'._('By now, on the OC system, we have registered following GK (or GK/TB) hotels/motels:').'</p><table>';

$lista_hoteli = array('OP0F87', 'OP2262',  'OP16B8', 'OC85FC', 'OK0085', 'OP1C23',
'OP115F',
'OP0F87',
'OP183D',
'OP187D',
'OP1990',
'OP0982',
'OP10F0',
'OC5895',
'OZ0140',
'OP1EAC',
'OP20A0',
'OP1D0E',
'OP229C',
'OP1E84',
'OP18F2',
'OP1B2C',
'OP230E',
'OP2017',
'OP239D',
'OP1B73',
'OP2387',
'OP183D',
'OP1328',
'OP1891',
'OP16B8',
'OP115F',
'OP2221',
'OP1217',
'OP2406',
'OP2423',
'OP2061',
'OP1FD6',
'OP2461',
'OP22E6',
'OP23CA',
'OP19E0',
'GR0433',
'OCBD6B',
'OCBC73',
'OC921F',
'OCA814',
'OU035B',
'OU036C',
'OU02CC',
'OU02D5',
'OU02DD',
'OU0349',
'OU02C3',
'OU01BD',
'OU035B',
'OU024C',
'OU0318',
'OB1466',
'TR17200',
);

// `lat`, `lon`, `name`, `typ`, `kraj`, `link`, `alt`, `country`, `status`
foreach ($lista_hoteli as $waypoint) {
    list(, , $name, $typ, $kraj, $linka, , $country, $status) = waypoint_info($waypoint);

    if ($status == 1) {
        $status_obr = '<img src="'.CONFIG_CDN_IMAGES.'/icons/ok.png" alt="OK!" title="Status: Ready for search" />';
    } else {
        $status_obr = '<img src="'.CONFIG_CDN_IMAGES.'/icons/error.png" alt="error" title="Status: not ready to search" />';
    }
    //var_dump(waypoint_info($waypoint));
    $TRESC .= "<tr><td><strong>$waypoint</strong></td><td>$status_obr<td><a href=\"$linka\">$name</a></td><td>$typ</td>
	<td><img src=\"".CONFIG_CDN_IMAGES."/country-codes/$country.png\" /> $kraj</td></tr>\n";
}

$TRESC .= '</table>';

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
