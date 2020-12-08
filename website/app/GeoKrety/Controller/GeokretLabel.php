<?php

namespace GeoKrety\Controller;

use Exception;
use Flash;
use GeoKrety\Model\Label;
use GeoKrety\Service\Labels\Image;
use GeoKrety\Service\Labels\Pdf;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Smarty;
use GeoKrety\Traits\GeokretLoader;

class GeokretLabel extends Base {
    use GeokretLoader {
        beforeRoute as protected beforeRouteGeoKret;
    }

    public function beforeRoute(\Base $f3) {
        $this->beforeRouteGeoKret($f3);
        if (!$this->geokret->hasTouchedInThePast()) {
            Flash::instance()->addMessage(_('Sorry you don\'t have the permission to print a label for this GeoKret as you never discovered it!'), 'danger');
            $f3->reroute('@geokret_details');
        }
    }

    public function post(\Base $f3) {
        $f3->get('DB')->begin();

        // Load the selected template
        $label = new Label();
        $label->load(['template = ?', $f3->get('POST.labelTemplate')], null, GK_SITE_CACHE_TTL_LABELS_LOOKUP);
        if ($label->dry()) {
            Flash::instance()->addMessage(_('This label template does not exists.'), 'danger');
            $f3->reroute('@geokret_label');
        }

        $this->geokret->label_template = $label;

        // Save used template as default for this GeoKret if is_owner
        if ($this->geokret->isOwner()) {
            try {
                if (!$this->geokret->validate()) {
                    $f3->get('DB')->rollback();
                    $this->get($f3);
                    exit();
                }
            } catch (Exception $e) {
                Flash::instance()->addMessage(_('Something went wrong while saving the GeoKret preferred label template.'), 'danger');
                $f3->get('DB')->rollback();
                $this->get($f3);
                exit();
            }
            $this->geokret->save();
            $f3->get('DB')->commit();
        } else {
            $f3->get('DB')->rollback();
        }

        // Store form values
        $this->geokret->mission = $f3->get('POST.mission');

        if (!empty($f3->get('POST.helpLanguages')) && !LanguageService::areLanguageSupported($f3->get('POST.helpLanguages'))) {
            Flash::instance()->addMessage(_('Some chosen languages are invalid.'), 'danger');
            $this->get($f3);
            exit();
        }
        $f3->set('COOKIE.helpLanguages', json_encode($f3->get('POST.helpLanguages')));

        // Export type
        if ($f3->exists('POST.generateAsPng')) {
            $this->png();
            exit();
        } elseif ($f3->exists('POST.generateAsSvg')) {
            $this->svg();
            exit();
        } elseif ($f3->exists('POST.generateAsPdf')) {
            $this->pdf();
            exit();
        }

        Flash::instance()->addMessage(_('Please select an export type.'), 'danger');
        $this->get($f3);
    }

    public function get(\Base $f3) {
        $label = new Label();
        $templates = $label->find(null, ['order' => 'title'], GK_SITE_CACHE_TTL_LABELS_LIST);
        Smarty::assign('templates', $templates);

        $selectedLanguages = [];
        if ($f3->exists('COOKIE.helpLanguages')) {
            $selectedLanguages = json_decode($f3->get('COOKIE.helpLanguages'));
        } elseif ($f3->exists('POST.helpLanguages')) {
            $selectedLanguages = $f3->get('POST.helpLanguages');
        }
        Smarty::assign('selectedLanguages', $selectedLanguages);

        Smarty::render('pages/geokret_label.tpl');
    }

    public function png() {
        header('Content-Type: image/png');
        $image = new Image();
        $image->setLanguages(\Base::instance()->get('POST.helpLanguages'));
        echo $image->png($this->geokret);
    }

    public function svg() {
        header('Content-Type: image/svg+xml');
        $image = new Image();
        $image->setLanguages(\Base::instance()->get('POST.helpLanguages'));
        echo $image->svg($this->geokret);
    }

    public function pdf() {
        $pdf = new Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->addGeokrety($this->geokret);
        $pdf->setLanguages(\Base::instance()->get('POST.helpLanguages'));
        $pdf->render();
    }
}
