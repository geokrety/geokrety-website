<?php

function days_ago($somedate)
{
    //how many days since somedate, a little more complicated to be able to use words like today & yesterday correctly.
    $gd_now = getdate(strtotime(date('Y-m-d')));
    $gd_last = getdate(strtotime($somedate));
    $d_now = mktime(12, 0, 0, $gd_now['mon'], $gd_now['mday'], $gd_now['year']);
    $d_last = mktime(12, 0, 0, $gd_last['mon'], $gd_last['mday'], $gd_last['year']);
    $dayssince = round(($d_now - $d_last) / 86400, 0);

    //formatting the phrase
    if ($dayssince >= 2) {
        $return['phrase'] = $dayssince.' '._('days ago');
    } elseif ($dayssince == 1) {
        $return['phrase'] = _('Yesterday');
    } else {
        $return['phrase'] = _('Today');
    } //so even if an Australian logs with a "tomorrow" (negative)  date, it will say Today

    $return['phrase'] = str_replace(' ', '&nbsp;', $return['phrase']);

    $return['number'] = $dayssince;

    return $return;
}
