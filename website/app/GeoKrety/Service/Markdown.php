<?php

namespace GeoKrety\Service;

class Markdown extends \Prefab {
    private $parser = null;

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
}
