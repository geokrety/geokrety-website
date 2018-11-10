<?php

require_once '__sentry.php';

try {
    // smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

    $TYTUL = _('Recent logs');

    $link = GKDB::getLink();

    // -------------------------------------- recent moves ------------------------------- //

    require_once 'recent_moves.php';

    $OGON .= '<script type="text/javascript" src="sorttable-1.min.js"></script>';
    $OGON .= '<script type="text/javascript" src="'.$config['ajaxtooltip.js'].'"></script>';

    $TRESC .= recent_moves('', 50, '', '', true);

    // --------------------------------------------------------------- SMARTY ---------------------------------------- //

    require_once 'smarty.php';
} catch (Exception $exc) {
    echo 'Service unavailable - '.$exc->getMessage();
}
