<?php

namespace GeoKrety\Service;

use Erusev\Parsedown\Configurables\StrictMode;
use Erusev\Parsedown\Parsedown;
use Erusev\Parsedown\State;

class Markdown extends \Prefab {
    protected ?Parsedown $parser = null;

    public static function getParser() {
        return self::instance()->parser;
    }

    public function __construct() {
        $state = new State([
            new StrictMode(true),
        ]);

        $this->parser = new Parsedown($state);
    }

    public static function toText($string) {
        return strip_tags(self::toHtml($string));
    }

    public static function toHtml($string) {
        // Fix OTF import issue from GKv1 #1081
        $string = preg_replace('/\[(.*?)\]\(\[link\]\((https?:\/\/[^\)]+)\)[^\)]*\)/', '[$1]($2)', $string);

        $html = self::getParser()->toHtml($string);

        return HTMLPurifierSafe::getPurifier()->purify($html);
    }
}
