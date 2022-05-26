<?php

namespace GeoKrety\Service\Xml;

use DateTime;
use XMLWriter;

abstract class Base {
    protected $xml;
    protected $stream;
    private $compress;

    public function __construct(bool $streamXML = false, ?string $compress = null, $filename = 'out.xml') {
        $this->stream = fopen('php://output', 'w');
        if ($streamXML === true) {
            // No output buffer while streaming
            ob_end_flush();
            ob_implicit_flush();
            if (strtolower($compress) === 'bzip2') {
                header('Content-Disposition: attachment; filename='.$filename.'.bz2');
                header('Content-type: application/x-bzip2');
                stream_filter_append($this->stream, 'bzip2.compress', STREAM_FILTER_WRITE);
            } elseif (strtolower($compress) === 'gzip') {
                // Unfortunately, gzip require header and trailer, which are not handled by stream-filter, so
                // we need to rely on temporary files :( The header is easy to implement, however, trailer require
                // checksum and length or the **original** data, which is not stored in the filter (implementing a
                // custom filter to compute those may be possible, but…)
                header('Content-Disposition: attachment; filename='.$filename.'.gz');
                header('Content-type: application/x-gzip');
                $this->stream = tmpfile();
                $this->compress = 'gzip';
            } else {
                header('Content-Type: application/xml; charset=UTF-8');
            }
        }
        $this->xml = self::getGeoKretyBaseXmlWriter();
    }

    public function end() {
        $this->xml->endElement();
    }

    public function asXMLPretty() {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $this->flush();
        $dom->loadXML(ob_get_contents());
        ob_clean();

        return $dom->saveXML();
    }

    public function flush($empty = true) {
        fwrite($this->stream, $this->xml->flush($empty));
    }

    public function finish() {
        $this->flush(true);
        if ($this->compress === 'gzip') {
            $tmpFile = tmpfile();
            copy(stream_get_meta_data($this->stream)['uri'], 'compress.zlib://'.stream_get_meta_data($tmpFile)['uri']);
            stream_copy_to_stream($tmpFile, fopen('php://output', 'w'));
        }
    }

    public static function getGeoKretyBaseXmlWriter() {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('gkxml');

        $xml->startAttribute('version');
        $xml->text('1.0');
        $xml->endAttribute();

        $xml->startAttribute('date');
        $xml->text(date('Y-m-d H:i:s'));
        $xml->endAttribute();

        $xml->startAttribute('date_Iso8601');
        $xml->text(date(DateTime::ATOM));
        $xml->endAttribute();

        return $xml;
    }
}
