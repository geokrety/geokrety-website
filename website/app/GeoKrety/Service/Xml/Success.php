<?php

namespace GeoKrety\Service\Xml;

use GeoKrety\Model\Move;

class Success extends Base {
    public function __construct(bool $streamXML = false, ?string $compress = null, $filename = 'out.xml') {
        parent::__construct($streamXML, $compress, $filename);
        $this->xml->startElement('geokrety');
    }

    public function end() {
        $this->xml->endElement();
        parent::end();
    }

    public function addMoves(array $moves) {
        foreach ($moves ?: [] as $move) {
            $this->addMove($move);
        }
    }

    public function addMove(Move $move) {
        $this->xml->startElement('geokret');
        $this->xml->writeAttribute('id', $move->geokret->gkid());
        $this->xml->endElement();
    }

    /**
     * Create an xml response from an array of geokrety.
     *
     * @param bool       $stream Prepare to render as xml output
     * @param array|Move $moves  The Move to format
     */
    public static function buildSuccess(bool $stream, $moves) {
        //public static function buildError(bool $stream, array|Move $moves) { // need php 8.0
        $moves = gettype($moves) === 'GeoKrety\Model\Move' ? [$moves] : $moves;
        $xml = new \GeoKrety\Service\Xml\Success($stream);
        $xml->addMoves($moves);
        $xml->end();
        $xml->finish(); // may return raw gzipped data
    }
}
