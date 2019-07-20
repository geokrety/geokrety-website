<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('GK XML interface');

$smarty->assign('content_template', 'api.tpl');

require_once 'smarty.php';
