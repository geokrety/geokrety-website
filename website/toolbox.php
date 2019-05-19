<?php

require_once '__sentry.php';

$smarty_cache_this_page = 3800; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Geokrety Toolbox');
$smarty->assign('content_template', 'toolbox.tpl');

require_once 'smarty.php';
