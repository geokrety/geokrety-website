<?php

require_once '__sentry.php';

$smarty_cache_this_page = 3800; // this page should be cached for n seconds
require_once 'smarty_start.php';

$smarty->assign('content_template', 'terms_of_use.tpl');

$TYTUL = _('Terms of use');


require_once 'smarty.php';
