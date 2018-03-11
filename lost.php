<?php

require_once '__sentry.php';

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 1200; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Probably lost geokrets');

$TRESC = _("<p>Here is a list of geokrets, for which the last log is 'drop' to the cache, which then was made archived or temporary unavaliable. Those data are mainly avaliable for OpenCaching caches. The list may not be full or may be inacurate in other ways.</p>");

$TRESC .= "<p><a href='mapka_kretow.php?xml=files/lost.xml'>"._('The map of lost geokrets to organize the rescue mission').'</p>';
$TRESC .= '<p>'._('The list is generated daily.').'</p>';
$TRESC .= file_get_contents($config['generated'].'lost.html');

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
