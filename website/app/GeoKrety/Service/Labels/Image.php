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

    public function png(Geokret $geokret) {
        return $this->fetch($geokret, 'png');
    }

    /**
     * @param string $type The output file type supported by geokrety-svg-to-png
     *                     Currently supported are svg or png
     *
     * @return string The generated image
     */
    private function fetch(Geokret $geokret, string $type = 'svg'): string {
        Smarty::assign('geokret', $geokret);

        $url = \Base::instance()->alias('move_create_short', '@tracking_code='.$geokret->tracking_code);
        $languages = array_diff($this->getLanguages(), ['en']);

        // Mark strings for gettext extraction
        _('User\'s manual:');
        _('Take this GeoKret.');
        _('Please note down his Tracking Code.');
        _('Hide in another cache.');
        _('Register the trip at %s');

        // Localized Help, use raw strings so they get translated
        $strings = [];
        $strings[] = $this->bold($this->translateMessage('User\'s manual:', $languages));
        $strings[] = $this->bold(' 1. ');
        $strings[] = $this->translateMessage('Take this GeoKret.', $languages);
        $strings[] = $this->bold(' 2. ');
        $strings[] = $this->translateMessage('Please note down his Tracking Code.', $languages);
        $strings[] = $this->bold(' 3. ');
        $strings[] = $this->translateMessage('Hide in another cache.', $languages);
        $strings[] = $this->bold(' 4. ');
        $strings[] = $this->translateMessage('Register the trip at %s', $languages, [GK_SITE_BASE_SERVER_URL.$url]);

        $translatedHelp = $this->concatByLang($strings);
        foreach ($translatedHelp as $key => $help) {
            Smarty::assign('help'.$key, $help);
        }

        $template = sprintf(
            'labels/%s.tpl.svg',
            is_null($geokret->label_template) ? 'default' : $geokret->label_template->template
        );
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

    /** Translate and inject placeholders for all requested languages. */
    private function translateMessage(string $msg, array $languages, array $placeholders = []): array {
        $strings = LanguageService::translate($msg, $languages);

        return array_map(
            fn (string $s): string => $placeholders ? vsprintf($s, $placeholders) : $s,
            $strings
        );
    }

    /** Wrap text in <tspan font-weight="bold">; supports arrays (applied element-wise). */
    private function bold(string|array $msg): string|array {
        if (is_array($msg)) {
            return array_map(fn ($m) => $this->bold($m), $msg);
        }

        return "<tspan font-weight=\"bold\">{$msg}</tspan>";
    }

    /** Concatenate a sequence of strings/arrays by language code. */
    private function concatByLang(array $items): array {
        $out = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                foreach ($item as $lang => $text) {
                    $out[$lang] = ($out[$lang] ?? '').$text;
                }
                continue;
            }
            // Append scalar to every existing lang bucket
            foreach (array_keys($out) as $lang) {
                $out[$lang] .= $item;
            }
        }

        return $out;
    }
}
