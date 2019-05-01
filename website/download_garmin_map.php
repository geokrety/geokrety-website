<?php

require_once '__sentry.php';

$smarty_cache_this_page = 43200; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Garmin map of caches');
$smarty->assign('content_template', 'download_garmin_map.tpl');

require_once 'smarty.php';
