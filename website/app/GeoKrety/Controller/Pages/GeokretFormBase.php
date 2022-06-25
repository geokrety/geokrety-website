<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Label;
use GeoKrety\Service\Smarty;

class GeokretFormBase extends Base {
    protected Geokret $geokret;

    public function get(\Base $f3) {
        $this->loadTemplates($f3);
        Smarty::render('pages/geokret_create.tpl');
    }

    protected function loadTemplates(\Base $f3): void {
        $label = new Label();
        $templates = $label->find(null, ['order' => 'title'], GK_SITE_CACHE_TTL_LABELS_LIST);
        Smarty::assign('templates', $templates);

        if ($f3->exists('COOKIE.default_label_template')) {
            $this->geokret->label_template = json_decode($f3->get('COOKIE.default_label_template'));
        }
    }

    protected function loadSelectedTemplate(\Base $f3): void {
        // Load the selected template
        $label = new Label();
        echo $f3->get('POST.label_template');
        $label->load(['template = ?', $f3->get('POST.label_template')], null, GK_SITE_CACHE_TTL_LABELS_LOOKUP);
        if ($label->dry()) {
            Flash::instance()->addMessage(_('This label template does not exist.'), 'danger');
            $this->get($f3);
            exit();
        }
        $this->geokret->label_template = $label->id;
        $f3->set('COOKIE.default_label_template', strval($this->geokret->label_template->id));
    }
}
