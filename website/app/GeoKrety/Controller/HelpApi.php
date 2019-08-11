<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Service\Xml;
use GeoKrety\Model\Geokret;

class HelpApi extends Base {
    public function get($f3) {
        // Load Geokret 46464
        $geokret = new Geokret();
        $geokret->load(array('id = ?', 46684));
        // Load Geokret 46464
        $geokret2 = new Geokret();
        $geokret2->load(array('id = ?', 65536));

        // Render ruchy saved
        $xml = new Xml\GeokretyRuchy();
        $xml->addGeokret($geokret);
        Smarty::assign('gk_xml_ruchy_saved', $xml->asXMLPretty());

        // Render export.php
        $xml = new Xml\GeokretyExport();
        $xml->addGeokret($geokret2);
        $xml->addMove($geokret->last_log);
        $xml->addMove($geokret2->last_log);
        Smarty::assign('gk_xml_export', $xml->asXMLPretty());

        // Render export_oc.php
        $xml = new Xml\GeokretyExportOC();
        $xml->addGeokret($geokret);
        $xml->addGeokret($geokret2);
        Smarty::assign('gk_xml_export_oc', $xml->asXMLPretty());

        // Render export2.php
        $xml = new Xml\GeokretyExport2();
        $xml->addGeokret($geokret);
        $xml->addGeokret($geokret2);
        Smarty::assign('gk_xml_export2', $xml->asXMLPretty());

        // Render ruchy error
        $xml = new \Geokrety\Service\Xml\Errors();
        $xml->addError(_('Wrong secid'));
        $xml->addError(_('Wrong date or time'));
        Smarty::assign('gk_xml_ruchy_error', $xml->asXMLPretty());

        Smarty::render('pages/help_api.tpl');
    }
}
