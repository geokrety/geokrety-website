<?php

namespace GeoKrety\Service\Labels;

use GeoKrety\Model\Geokret;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Smarty;

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

        $url = \Base::instance()->alias('move_create_short', '@tracking_code='.$geokret->tracking_code);
        $languages = array_diff($this->getLanguages(), ['en']);

        function translate(string $msg, array $languages, array $placeholders = []): array {
            $strings = LanguageService::translate($msg, $languages);

            return array_map(fn ($s) => vsprintf($s, $placeholders), $strings);
        }

        function bold(string|array $msg): string|array {
            if (is_array($msg)) {
                return array_map(
                    fn ($m) => bold($m), // recursive call
                    $msg
                );
            }

            return "<tspan font-weight=\"bold\">{$msg}</tspan>";
        }

        function concatByLang(array $items): array {
            $out = [];
            foreach ($items as $item) {
                if (is_array($item)) {
                    foreach ($item as $lang => $text) {
                        $out[$lang] = ($out[$lang] ?? '').$text;
                    }

                    continue;
                }
                foreach (array_keys($out) as $lang) {
                    $out[$lang] .= $item;
                }
            }

            return $out;
        }
        // This will be used to detect strings to translate
        _('User\'s manual:');
        _('Take this GeoKret.');
        _('Please note down his Tracking Code.');
        _('Hide in another cache.');
        _('Register the trip at %s');

        // Localized Help, use raw strings so they get translated
        $strings = [];
        $strings[] = bold(translate('User\'s manual:', $languages));
        $strings[] = bold(' 1. ');
        $strings[] = translate('Take this GeoKret.', $languages);
        $strings[] = bold(' 2. ');
        $strings[] = translate('Please note down his Tracking Code.', $languages);
        $strings[] = bold(' 3. ');
        $strings[] = translate('Hide in another cache.', $languages);
        $strings[] = bold(' 4. ');
        $strings[] = translate('Register the trip at %s', $languages, [GK_SITE_BASE_SERVER_URL.$url]);
        $translatedHelp = concatByLang($strings);

        foreach ($translatedHelp as $key => $help) {
            Smarty::assign('help'.$key, $help);
        }

        $template = sprintf('labels/%s.tpl.svg', is_null($geokret->label_template) ? 'default' : $geokret->label_template->template);
        $labelSVGData = Smarty::fetch($template);

        $stream = 'data://image/svg+xml;base64,'.base64_encode($labelSVGData);
        $url = GK_LABELS_SVG2PNG_URL.'?'.$type;

        $postVars = [
            'file' => new \CURLFile($stream, 'image/svg+xml', 'label.svg'),
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
        $result = \Web::instance()->request($url, $options);

        return $result['body'];
    }

    public function png(Geokret $geokret) {
        return $this->fetch($geokret, 'png');
    }
}
