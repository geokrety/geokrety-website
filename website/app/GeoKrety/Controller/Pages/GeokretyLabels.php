<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Labels\Pdf;
use GeoKrety\Service\Smarty;
use GeoKrety\Service\Validation\TrackingCode;

class GeokretyLabels extends Base {
    use \CurrentUserLoader;

    public array $languages = ['fr', 'de', 'pl', 'ru'];

    public function get(\Base $f3) {
        Smarty::render('pages/geokrety_labels.tpl');
    }

    public function pdf(\Base $f3) {
        $this->checkCsrf(function ($error) use ($f3) {
            \Flash::instance()->addMessage($error, 'danger');
            $f3->reroute('@geokrety_labels');
        });

        $tracking_codes = TrackingCode::split_tracking_codes($f3->get('POST.tracking_code'));

        $gk_list = array_map(['\GeoKrety\Model\Geokret', 'tracking_code_to_id'], $tracking_codes);
        $gk_list = array_filter($gk_list, 'strlen');

        $geokret = new Geokret();
        $geokret->addFilterHasTouchedInThePast($this->current_user, $gk_list);
        $geokrety = $geokret->find(['owner = ? AND gkid IN ?', $this->current_user->id, $gk_list], ['limit' => GK_LABELS_GENERATE_MAX]);

        if ($geokrety === false) {
            \Flash::instance()->addMessage(_('The list contains only unknown Tracking Codes or never touched GeoKrety'), 'danger');
            $f3->reroute('@geokrety_labels');
        }

        $pdf = new Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->addGeokrety(...(array) $geokrety);
        $pdf->setLanguages($this->languages);
        $pdf->render();
    }
}
