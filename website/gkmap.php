<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$smarty->assign('content_template', 'gkmap.tpl');
$smarty->append('javascript', CDN_LEAFLET_JS);
$smarty->append('css', CDN_LEAFLET_CSS);

$smarty->append('javascript', CDN_LEAFLET_MARKERCLUSTER_JS);
$smarty->append('css', CDN_LEAFLET_MARKERCLUSTER_CSS);
$smarty->append('css', CDN_LEAFLET_MARKERCLUSTER_DEFAULT_CSS);

$smarty->append('javascript', CDN_LEAFLET_GEOKRETYFILTER_JS);
$smarty->append('css', CDN_LEAFLET_GEOKRETYFILTER_CSS);

$smarty->append('javascript', CDN_LEAFLET_PLUGIN_BING_JS);

$smarty->append('javascript', CDN_LEAFLET_NOUISLIDER_JS);
$smarty->append('css', CDN_LEAFLET_NOUISLIDER_CSS);

$smarty->append('javascript', CDN_SPIN_JS);
$smarty->append('javascript', CDN_LEAFLET_SPIN_JS);

$smarty->append('javascript', CDN_LEAFLET_FULLSCREEN_JS);
$smarty->append('css', CDN_LEAFLET_FULLSCREEN_CSS);

$smarty->append('js_template', 'js/gkmap.tpl.js');

require_once 'smarty.php';
