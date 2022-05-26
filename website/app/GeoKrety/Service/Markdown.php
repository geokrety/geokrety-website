<?php

namespace GeoKrety\Service;

class Markdown extends \Prefab {
    private ?\Parsedown $parser = null;

    public static function getParser() {
        return Markdown::instance()->parser;
    }

    public function __construct() {
        $this->parser = new \Parsedown();
        $this->parser->setStrictMode(true);
    }

    public static function toText($string) {
        return strip_tags(self::toHtml($string));
    }

    public static function toHtml($string) {
        $html = self::getParser()->text($string);

        return HTMLPurifierSafe::getPurifier()->purify($html);
    }

    public static function toFormattedMarkdown($string) {
        // Workaround historical database modifications
        $string = str_replace('<br />', '  ', $string);
        $string = str_replace('[<a href=\'', '[link](', $string);
        $string = str_replace('\' rel=nofollow>Link</a>]', ')', $string);

        return HTMLPurifier::getPurifier()->purify($string);
    }
}
