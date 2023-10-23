<?php

namespace GeoKrety\Service\Xml;

class Generic extends Base {
    public function __construct(string $root, bool $streamXML = false, ?string $compress = null, $filename = 'out.xml') {
        parent::__construct($streamXML, $compress, $filename);
        $this->xml->startElement($root);
    }

    public function end() {
        $this->xml->endElement();
        parent::end();
    }

    public function addItem($key, $value) {
        $this->xml->startElement($key);
        $this->xml->writeCdata($value);
        $this->xml->endElement();
    }

    /**
     * Create xml response from an array of errors.
     *
     * @param bool   $stream Prepare to render as xml output
     * @param string $root   The XML root element name
     */
    public static function buildGeneric(bool $stream, string $root, array|string $data) {
        $data = gettype($data) === 'string' ? [$data] : $data;
        $xml = new \GeoKrety\Service\Xml\Generic($root, $stream);

        foreach ($data as $key => $value) {
            if (gettype($value) === 'array') {
                $xml->xml->startElement($key);
                foreach ($value as $k => $v) {
                    $xml->addItem($k, $v);
                }
                $xml->xml->endElement();
            } else {
                $xml->addItem($key, $value);
            }
        }
        $xml->end();
        $xml->finish(); // may return raw gzipped data
    }
}
