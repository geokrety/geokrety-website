<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
loginFirst();

$template = 'dialog/move_comment_delete.tpl';

$move_comment_id = $_GET['id'];
if (!ctype_digit($move_comment_id)) {
    echo _('Oops! Something went wrong.').' [#'.__LINE__.']';
    exit;
}
$smarty->assign('comment_id', $move_comment_id);

require_once 'smarty.php';
