<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\GeokretWithDetails;
use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;
use GeoKrety\Service\Xml;

class HelpApi extends Base {
    public function get($f3) {
        $this->f3 = $f3;

        // Load specified GeoKrety on production else first two created
        [$geokret, $geokret2, $geokret3, $geokret4] = $this->loadGK(GK_HELP_GEOKRETY_EXAMPLE_LIST);
        Smarty::assign('gk_example_1', $geokret->gkid());
        Smarty::assign('gk_example_2', $geokret2->gkid());
        Smarty::assign('gk_example_3_tc', $geokret3->tracking_code);
        Smarty::assign('gk_example_4_tc', $geokret4->tracking_code);

        // Render ruchy saved
        $xml = new Xml\GeokretyRuchy();
        $xml->addGeokret($geokret);
        $xml->end();
        Smarty::assign('gk_xml_ruchy_saved', $xml->asXMLPretty());

        // Render export.php
        $xml = new Xml\GeokretyExport();
        $xml->addGeokret($geokret2);
        $move = new Move();
        $move->load(['_id = ?', $geokret->last_log]);
        $xml->addMove($move);
        $move->load(['_id = ?', $geokret2->last_log]);
        $xml->addMove($move);
        $xml->end();
        Smarty::assign('gk_xml_export', $xml->asXMLPretty());

        // Render export_oc.php
        $xml = new Xml\GeokretyExportOC();
        $xml->addGeokret($geokret);
        $xml->addGeokret($geokret2);
        $xml->end();
        Smarty::assign('gk_xml_export_oc', $xml->asXMLPretty());

        // Render export2.php
        $xml = new Xml\GeokretyExport2();
        $xml->addGeokret($geokret);
        $xml->addGeokret($geokret2);
        $xml->end();
        Smarty::assign('gk_xml_export2', $xml->asXMLPretty());

        // Render export2.php?details=1
        $xml = new Xml\GeokretyExport2Details();
        $xml->addGeokret($geokret2);
        $xml->end();
        Smarty::assign('gk_xml_export2_details', $xml->asXMLPretty());

        // Render ruchy error
        $xml = new Xml\Error();
        $xml->addError(_('Wrong secid'));
        $xml->addError(_('Wrong date or time'));
        $xml->end();
        Smarty::assign('gk_xml_ruchy_error', $xml->asXMLPretty());

        Smarty::assign('modified_since', date('YmdHis', time() - (1 * 60 * 60)));

        $xml = new Xml\RateLimits(false);
        foreach (GK_RATE_LIMITS_DEFAULT as $name => $values) {
            $xml->addLimit($name, $values[1]);
            $xml->addUsage('xxx', 0, $values[0], RATE_LIMIT_LEVEL_ANONYMOUS);
            $xml->endElement();
        }
        $xml->end();
        Smarty::assign('rate_limit_usage', $xml->asXMLPretty());

        // Render ratelimit error
        $xml = new Xml\Error();
        $xml->addError(_('Rate limit exceeded'));
        $xml->end();
        Smarty::assign('gk_xml_rate_limit_error', $xml->asXMLPretty());
        Smarty::assign('rate_limit_multipliers', RATE_LIMIT_LEVEL_MULTIPLIER);

        Smarty::render('pages/help_api.tpl');
    }

    /**
     * @param int[] $gkIds
     *
     * @return \GeoKrety\Model\Geokret[]
     */
    private function loadGK(array $gkIds): array {
        $geokrety = [];
        foreach ($gkIds as $gkid) {
            $geokret = new GeokretWithDetails();
            $geokret->load(['gkid = ?', $gkid], ttl: GK_HELP_GEOKRETY_EXAMPLE_TTL);
            $this->checkGK($geokret);
            $geokrety[] = $geokret;
        }

        return $geokrety;
    }

    private function checkGK($geokret) {
        if ($geokret->dry()) {
            if (GK_IS_PRODUCTION) {
                \Flash::instance()->addMessage('This page could not be loaded.', 'danger');
                $this->f3->reroute('@home');
            }
            \Flash::instance()->addMessage('This page could not be loaded as there is not enough existing GeoKrety in database.', 'danger');
            $this->f3->reroute('@home');
        }

        if (\is_null($geokret->last_log)) {
            \Flash::instance()->addMessage('GeoKrety for help page must have already moved.', 'danger');
            $this->f3->reroute('@home');
        }
    }
}
