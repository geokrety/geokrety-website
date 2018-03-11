<?php

// configuration file for races //

$conf_race_type['wpt'] = _('Race to destination');
$conf_race_type['maxDistance'] = _('Maximize distance');
$conf_race_type['targetDistance'] = _('Reach target distance');
$conf_race_type['maxCaches'] = _('Maximize number of visited caches');
$conf_race_type['targetCaches'] = _('Reach target number of caches');

$conf_race_status['0'] = _('This race hasn\'t started yet');
$conf_race_status['1'] = _('The race is running');
$conf_race_status['2'] = _('The race has ended');
//$conf_race_status['3'] = _('The race has ended') . ". "  . _('Waiting for pending logs');

$conf_race_status_icon['0'] = '<img src="'.CONFIG_CDN_ICONS.'/race_wait16.png" width="16" height="16" alt="icon" />';
$conf_race_status_icon['1'] = '<img src="'.CONFIG_CDN_ICONS.'/race_run16.png" width="16" height="16" alt="icon" />';
$conf_race_status_icon['2'] = '<img src="'.CONFIG_CDN_ICONS.'/race_finish16.png" width="16" height="16" alt="icon" />';
//$conf_race_status_icon['3'] = '<img src="'.CONFIG_CDN_ICONS.'/race_finish16.png" width="16" height="16" alt="icon" />' . ' ' . '<img src="'.CONFIG_CDN_ICONS.'/race_pending16.png" width="16" height="16" alt="icon" />';
