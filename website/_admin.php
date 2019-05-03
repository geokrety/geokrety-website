<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$userid = $longin_status['userid'];
if (!in_array($userid, $config['superusers'])) {
    exit;
}

$TYTUL = _('Admin');
$smarty->assign('content_template', 'admin/index.tpl');

// --------------------------------------------------------------- SMARTY ---------------------------------------- //

require_once 'smarty.php';
