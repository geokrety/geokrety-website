<?php

require_once '__sentry.php';

$smarty_cache_this_page = 3800; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('Press corner');
$smarty->assign('content_template', 'press_corner.tpl');

require_once 'smarty.php';
