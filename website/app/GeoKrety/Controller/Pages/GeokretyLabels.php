<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\Labels\Pdf;
use GeoKrety\Service\Smarty;

class GeokretyLabels extends Base {
//    public function get() {
//        // Todo check if tracking code is known
//        Smarty::render('pages/geokret_label.tpl');
//    }
//
//    public function post() {
//        $this->get();
//    }

    public function pdf() {
        $gklist = ['GKD99B', 'GKB65C', 'GK10000', 'GK10001', 'GK10002', 'GK10003', '13256', 66184];
        for ($i = 66185; $i < 66207; ++$i) {
            $gklist[] = $i;
        }
        $gklist = array_map(['\GeoKrety\Model\Geokret', 'gkid2id'], $gklist);

        $pdf = new Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $geokret = new Geokret();
        $geokrety = $geokret->find(['gkid IN ?', $gklist]);
        // TODO filter owner or already touched
        $pdf->addGeokrety(...$geokrety);
        $pdf->render();
    }
}
