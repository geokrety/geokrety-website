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
        $html = self::getParser()->toHtml($string);

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
