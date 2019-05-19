<?php

require_once '__sentry.php';

$smarty_cache_this_page = 3200; // this page should be cached for n seconds
require_once 'smarty_start.php';

require 'waypoint_info.php';   // for the list of hotels and motels

$TYTUL = ('GK mole-holes and GK hotels/motels');
$smarty->assign('content_template', 'molehole.tpl');

$lista_dziur[] = 'OP1891';
$lista_dziur[] = 'OC7DCB';
$lista_dziur[] = 'OK0085';
$lista_dziur[] = 'OP3373';
$lista_dziur[] = 'OCBEA7';
$lista_dziur[] = 'OP3B15';
$lista_dziur[] = 'OCCB8A';
$lista_dziur[] = 'OCC3EC';
$lista_dziur[] = 'OCC8B7';

$moleholes = array();
foreach ($lista_dziur as $waypoint) {
    list(, , $name, $typ, $kraj, $linka, , $country, $status) = waypoint_info($waypoint);
    $moleholes[$waypoint] = array($name, $typ, $kraj, $linka, $country, $status);
}
$smarty->assign('moleholes', $moleholes);


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

$gkhotels = array();
foreach ($lista_hoteli as $waypoint) {
    list(, , $name, $typ, $kraj, $linka, , $country, $status) = waypoint_info($waypoint);
    $gkhotels[$waypoint] = array($name, $typ, $kraj, $linka, $country, $status);
}
$smarty->assign('gkhotels', $gkhotels);


// --------------------------------------------------------------- SMARTY ---------------------------------------- //
require_once 'smarty.php';
