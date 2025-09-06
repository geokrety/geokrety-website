<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Label;
use GeoKrety\Service\Labels\Image;
use GeoKrety\Service\Labels\Pdf;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\RateLimit;
use GeoKrety\Service\Smarty;
use GeoKrety\Traits\GeokretLoader;

class GeokretLabel extends Base {
    use GeokretLoader { beforeRoute as protected beforeRouteGeoKret; }

    private ?array $langs = null;

    private function in(string $key): array|string|null {
        $f3 = \Base::instance();
        if ($f3->exists('POST.'.$key)) {
            return $f3->get('POST.'.$key);
        }
        $val = $f3->get('GET.'.$key);
        if (is_array($val)) {
            return array_values(array_unique($val, SORT_STRING));
        }

        return $val;
    }

    private function normLangs($val): ?array {
        if ($val === null || $val === '' || $val === 'null') {
            return null;
        }
        if (is_array($val)) {
            return $val;
        }

        return [$val];
    }

    public function beforeRoute(\Base $f3) {
        $this->beforeRouteGeoKret($f3);
        if (!$this->geokret->hasTouchedInThePast()) {
            \Flash::instance()->addMessage(_('Sorry you don\'t have the permission to print a label for this GeoKret as you never discovered it!'), 'danger');
            $f3->reroute(sprintf('@geokret_details(@gkid=%s)', $this->geokret->gkid));
        }
    }

    public function post(\Base $f3) {
        $this->checkCsrf();
        if ($f3->exists('POST.generateAsPng')) {
            $this->png();

            return;
        }
        if ($f3->exists('POST.generateAsSvg')) {
            $this->svg();

            return;
        }
        if ($f3->exists('POST.generateAsPdf')) {
            $this->pdf();

            return;
        }
        \Flash::instance()->addMessage(_('Please select an export type.'), 'danger');
        $this->get($f3);
    }

    public function get(\Base $f3) {
        $label = new Label();
        $templates = $label->find(null, ['order' => 'title'], GK_SITE_CACHE_TTL_LABELS_LIST);
        Smarty::assign('templates', $templates);

        $selectedLanguages = $this->geokret->label_languages;
        if (empty($selectedLanguages) && $f3->exists('REQUEST.label_languages')) {
            $selectedLanguages = $this->normLangs($this->in('label_languages')) ?: [];
        }
        if (empty($selectedLanguages) && $f3->exists('COOKIE.label_languages')) {
            $decoded = json_decode((string) $f3->get('COOKIE.label_languages'), true);
            $selectedLanguages = is_array($decoded) ? $decoded : [];
        }

        Smarty::assign('selectedLanguages', $selectedLanguages);
        Smarty::render('pages/geokret_label.tpl');
    }

    public function png() {
        $this->prepare_values();
        header('Content-Type: image/png');
        $image = new Image();
        $image->setLanguages($this->langs);
        echo $image->png($this->geokret);
    }

    public function svg() {
        $this->prepare_values();
        header('Content-Type: image/svg+xml');
        $image = new Image();
        $image->setLanguages($this->langs);
        echo $image->svg($this->geokret);
    }

    public function pdf() {
        $this->prepare_values();
        $pdf = new Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->addGeokrety($this->geokret);
        $pdf->setLanguages($this->langs);
        $pdf->render();
    }

    private function prepare_values(): void {
        $f3 = \Base::instance();
        $f3->get('DB')->begin();

        $tpl = $this->in('label_template') ?: 'default';
        $label = new Label();
        $label->load(['template = ?', $tpl], null, GK_SITE_CACHE_TTL_LABELS_LOOKUP);
        if ($label->dry()) {
            $label->load(['template = ?', 'default'], null, GK_SITE_CACHE_TTL_LABELS_LOOKUP);
            if ($label->dry()) {
                \Flash::instance()->addMessage(_('This label template does not exist.'), 'danger');
                $f3->reroute('@geokret_label');
            }
        }
        $this->geokret->label_template = $label;

        $langs = $this->normLangs($this->in('label_languages'));
        if (!empty($langs) && !LanguageService::areLanguageSupported($langs)) {
            \Flash::instance()->addMessage(_('Some chosen languages are invalid.'), 'danger');
            $this->get($f3);
            exit;
        }
        $this->langs = $langs ?: [];
        $f3->set('COOKIE.label_languages', json_encode($langs));
        $this->geokret->label_languages = $this->langs;

        // persist only on POST by owner (template + label_languages)
        if ($this->geokret->isOwner()) {
            try {
                if (!$this->geokret->validate()) {
                    $f3->get('DB')->rollback();
                    $this->get($f3);
                    exit;
                }
            } catch (\Exception $e) {
                \Flash::instance()->addMessage(_('Something went wrong while saving the GeoKret preferred label template.'), 'danger');
                $f3->get('DB')->rollback();
                $this->get($f3);
                exit;
            }
            $this->geokret->save();
            $f3->get('DB')->commit();
        } else {
            $f3->get('DB')->rollback();
        }

        $this->geokret->name = $this->in('name') ?: $this->geokret->name;
        $this->geokret->mission = $this->in('mission') ?: $this->geokret->mission;

        $this->sendRevalidateHeaders($this->geokret->etag());

        RateLimit::check_rate_limit_image('LABEL_GENERATOR', $this->current_user->id);
    }

    private function sendRevalidateHeaders(string $etag): void {
        header('ETag: "'.$etag.'"');
        header('Cache-Control: private, no-cache'); // cached, but must revalidate
        $f3 = \Base::instance();
        $inm = $f3->get('HEADERS.If-None-Match');
        if ($inm === null) {
            $inm = $f3->get('SERVER.HTTP_IF_NONE_MATCH') ?? null;
        }
        if ($inm && trim($inm) === '"'.$etag.'"') {
            http_response_code(304);
            exit;
        }
    }
}
