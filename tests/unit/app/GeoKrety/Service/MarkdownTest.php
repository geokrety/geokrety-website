<?php

namespace unit\app\GeoKrety\Service;

use GeoKrety\Service\Markdown;
use Mockery;

class MarkdownTest extends Mockery\Adapter\Phpunit\MockeryTestCase {
    public function testToHtml() {
        $md = new Markdown();

        $res = $md->toHtml('FOO');
        $this->assertEquals('<p>FOO</p>', $res);

        $res = $md->toHtml('page Profil [ici]([link](https://coord.info/PRCJYJB).)');
        $this->assertEquals('<p>page Profil <a href="https://coord.info/PRCJYJB.">ici</a></p>', $res);
    }

    public function testToFormattedMarkdown() {
        $md = new Markdown();

        $res = $md->toFormattedMarkdown('[ici](https://coord.info/PRCJYJB)');
        $this->assertEquals('[ici](https://coord.info/PRCJYJB)', $res);

        $res = $md->toFormattedMarkdown('page Profil <a href="https://coord.info/PRCJYJB">ici</a>');
        $this->assertEquals('page Profil ici', $res);

        $res = $md->toFormattedMarkdown('Geloggt mit [c:geo - Android]([link](https://play.google.com/store/apps/details?id=cgeo.geocaching))');
        $this->assertEquals('Geloggt mit [c:geo - Android](https://play.google.com/store/apps/details?id=cgeo.geocaching)', $res);

        $res = $md->toFormattedMarkdown('[Geocaching Loisir]([link](http://geocaching-loisir.fr).)');
        $this->assertEquals('[Geocaching Loisir](http://geocaching-loisir.fr)', $res);
    }
}
