<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$link = DBConnect();

$TYTUL = 'just a test'; //_("just a test");

$visitorid = $longin_status['userid'];

import_request_variables('g', 'g_');
import_request_variables('p', 'p_');

$TRESC = '...';

require_once 'smarty.php';
