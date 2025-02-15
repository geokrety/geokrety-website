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
        $this->assertEquals('<p>page Profil <a href="https://coord.info/PRCJYJB">ici</a></p>', $res);

        $res = $md->toHtml('page Profil <a href="https://coord.info/PRCJYJB">ici</a>');
        $this->assertEquals('<p>page Profil <a href="https://coord.info/PRCJYJB">ici</a></p>', $res);

        $res = $md->toHtml('Geloggt mit [c:geo - Android]([link](https://play.google.com/store/apps/details?id=cgeo.geocaching))');
        $this->assertEquals('<p>Geloggt mit <a href="https://play.google.com/store/apps/details?id=cgeo.geocaching">c:geo - Android</a></p>', $res);

        $res = $md->toHtml('[Geocaching Loisir]([link](http://geocaching-loisir.fr).)');
        $this->assertEquals('<p><a href="http://geocaching-loisir.fr">Geocaching Loisir</a></p>', $res);

        $res = $md->toHtml('[Geocaching Loisir]([link](http://geocaching-loisir.fr)foobar)');
        $this->assertEquals('<p><a href="http://geocaching-loisir.fr">Geocaching Loisir</a></p>', $res);
    }
}
