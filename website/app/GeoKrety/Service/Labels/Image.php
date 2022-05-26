<?php

namespace GeoKrety\Service\Labels;

use CURLFile;
use GeoKrety\Model\Geokret;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Smarty;
use Web;

class Image {
    use Traits\Languages;

    public function svg(Geokret $geokret) {
        return $this->fetch($geokret, 'SVG');
    }

    /**
     * @param string $type The output file type supported by geokrety-svg-to-png
     *                     Currently supported are svg or png
     *
     * @return string The generated image
     */
    private function fetch(Geokret $geokret, string $type = 'svg') {
        Smarty::assign('geokret', $geokret);

        // Localized Help
        // Both strings are necessary here as gettext cannot work with variable
        _('<tspan font-weight="bold">User\'s manual:</tspan> <tspan font-weight="bold">1.</tspan> Take this GeoKret. <tspan font-weight="bold">Please note down his Tracking Code.</tspan> <tspan font-weight="bold">2.</tspan> Hide in another cache. <tspan font-weight="bold">3.</tspan> Register the trip at <tspan font-weight="bold">https://geokrety.org</tspan>');
        $help = '<tspan font-weight="bold">User\'s manual:</tspan> <tspan font-weight="bold">1.</tspan> Take this GeoKret. <tspan font-weight="bold">Please note down his Tracking Code.</tspan> <tspan font-weight="bold">2.</tspan> Hide in another cache. <tspan font-weight="bold">3.</tspan> Register the trip at <tspan font-weight="bold">https://geokrety.org</tspan>';
        $translatedHelp = LanguageService::translate($help, array_diff($this->getLanguages(), ['en']));
        foreach ($translatedHelp as $key => $help) {
            Smarty::assign('help'.$key, $help);
        }

        $template = sprintf('labels/%s.tpl.svg', is_null($geokret->label_template) ? 'default' : $geokret->label_template->template);
        $labelSVGData = Smarty::fetch($template);

        $stream = 'data://image/svg+xml;base64,'.base64_encode($labelSVGData);
        $url = GK_LABELS_SVG2PNG_URL.'?'.$type;

        $postVars = [
            'file' => new CURLFile($stream, 'image/svg+xml', 'label.svg'),
            'qrcode' => sprintf(
                '%s%s?tracking_code=%s',
                GK_SITE_BASE_SERVER_URL,
                \Base::instance()->alias('move_create'),
                $geokret->tracking_code,
            ),
        ];

        $options = [
            'method' => 'POST',
            'content' => $postVars,
        ];
        $result = Web::instance()->request($url, $options);

        return $result['body'];
    }

    public function png(Geokret $geokret) {
        return $this->fetch($geokret, 'png');
    }
}
