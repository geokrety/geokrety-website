<?php

namespace GeoKrety\Service\Xml;

use GeoKrety\Model\AuditPost;

class Error extends Base {
    public function __construct(bool $streamXML = false, ?string $compress = null, $filename = 'out.xml') {
        parent::__construct($streamXML, $compress, $filename);
        $this->xml->startElement('errors');
    }

    public function end() {
        $this->xml->endElement();
        parent::end();
    }

    public function addError($msg) {
        $this->xml->startElement('error');
        $this->xml->writeCdata($msg);
        $this->xml->endElement();
    }

    /**
     * Create xml response from an array of errors.
     *
     * @param bool         $stream Prepare to render as xml output
     * @param array|string $errors The errors to format
     */
    public static function buildError(bool $stream, $errors) {
        // public static function buildError(bool $stream, array|string $errors) { // need php 8.0
        $errors = gettype($errors) === 'string' ? [$errors] : $errors;
        $xml = new \GeoKrety\Service\Xml\Error($stream);
        foreach ($errors as $err) {
            $xml->addError($err);
        }
        $xml->end();
        $xml->finish(); // may return raw gzipped data

        AuditPost::AmendAuditPostWithErrors($errors);
    }
}
