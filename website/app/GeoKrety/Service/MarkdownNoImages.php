<?php

namespace GeoKrety\Service;

use Erusev\Parsedown\Components\Inlines\Image;
use Erusev\Parsedown\Configurables\InlineTypes;
use Erusev\Parsedown\Configurables\SafeMode;
use Erusev\Parsedown\Configurables\StrictMode;
use Erusev\Parsedown\Parsedown;
use Erusev\Parsedown\State;

class MarkdownNoImages extends Markdown {
    public function __construct() {
        $state = new State([
            new SafeMode(true),
            new StrictMode(true),
        ]);

        // Disable images
        $InlineTypes = $state->get(InlineTypes::class);
        $InlineTypes = $InlineTypes->removing([Image::class]);
        $state = $state->setting($InlineTypes);

        $this->parser = new Parsedown($state);
    }
}
