<?php

namespace GeoKrety\Service\Xml;

class RateLimits extends Base {
    public function __construct(bool $streamXML = false, ?string $compress = null, $filename = 'out.xml') {
        parent::__construct($streamXML, $compress, $filename);
        $this->xml->startElement('limits');
    }

    public function end() {
        $this->endElement();
        parent::end();
    }

    /**
     * @param string $name Limit name
     * @param int    $ttl  TTL for this limit
     *
     * @return void
     */
    public function addLimit(string $name, int $ttl) {
        $this->xml->startElement('limit');
        $this->xml->writeAttribute('name', $name);
        $this->xml->writeAttribute('ttl', $ttl);
    }

    public function addUsage(string $id, int $current, int $limit, string $tier) {
        $this->xml->startElement('usage');
        $this->xml->writeAttribute('id', $id);
        $this->xml->writeAttribute('limit', $limit);
        $this->xml->writeAttribute('tier', $tier);
        $this->xml->writeCdata($current);
        $this->xml->endElement();
    }
}
