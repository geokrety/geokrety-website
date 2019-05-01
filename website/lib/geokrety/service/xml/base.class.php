<?php

namespace Geokrety\Service\Xml;

abstract class Base {
    protected $xml;

    public function __construct() {
        $xml = new \SimpleXMLElement('<gkxml/>');
        $xml->addAttribute('version', '1.0');
        $xml->addAttribute('date', date('Y-m-d H:i:s'));
        $this->xml = $xml;
    }

    public function getXml() {
        return $this->xml;
    }

    public function asXML() {
        return $this->xml->asXML();
    }
}
