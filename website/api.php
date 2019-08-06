<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

$TYTUL = _('GK XML interface');

// Generate a GeoKret
$geokret = \Geokrety\Domain\Konkret::generate();
$geokret2 = \Geokrety\Domain\Konkret::generate();

// Render ruchy saved
$xml = new \Geokrety\Service\Xml\GeokretyRuchy();
$xml->addGeokret($geokret);
$smarty->assign('gk_xml_ruchy_saved', $xml->asXMLPretty());

// Render export.php
$xml = new \Geokrety\Service\Xml\GeokretyExport();
$xml->addGeokret($geokret);
$xml->addMove($geokret2->lastLog);
$smarty->assign('gk_xml_export', $xml->asXMLPretty());

// Render export_oc.php
$xml = new \Geokrety\Service\Xml\GeokretyExportOC();
$xml->addGeokret($geokret);
$xml->addGeokret($geokret2);
$smarty->assign('gk_xml_export_oc', $xml->asXMLPretty());

// Render export2.php
$xml = new \Geokrety\Service\Xml\GeokretyExport2();
$xml->addGeokret($geokret);
$xml->addGeokret($geokret2);
$smarty->assign('gk_xml_export2', $xml->asXMLPretty());

// Render ruchy error
$xml = new \Geokrety\Service\Xml\Errors();
$xml->addError(_('Wrong secid'));
$xml->addError(_('Wrong date or time'));
$smarty->assign('gk_xml_ruchy_error', $xml->asXMLPretty());

// Template
$smarty->assign('content_template', 'api.tpl');
$smarty->append('css', CDN_PRISM_CSS);
$smarty->append('javascript', CDN_PRISM_JS);
$smarty->append('javascript', CDN_PRISM_MARKUP_TEMPLATING_JS);
$smarty->append('javascript', CDN_PRISM_PHP_JS);

require_once 'smarty.php';
