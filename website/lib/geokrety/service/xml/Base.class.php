<?php

namespace Geokrety\Service\Xml;

// https://stackoverflow.com/a/6260295/944936
class SimpleXMLExtended extends \SimpleXMLElement {
    /**
     * Adds a child with $value inside CDATA.
     *
     * @param unknown $name
     * @param unknown $value
     */
    public function addChildWithCDATA($name, $value = null) {
        $new_child = $this->addChild($name);

        if ($new_child !== null) {
            $node = dom_import_simplexml($new_child);
            $no = $node->ownerDocument;
            $node->appendChild($no->createCDATASection($value));
        }

        return $new_child;
    }
}

abstract class Base {
    protected $xml;

    public function __construct() {
        $xml = new SimpleXMLExtended('<gkxml/>');
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

    public function outputAsXML() {
        header('Content-Type: application/xml; charset=UTF-8');
        echo $this->xml->asXML();
    }

    public function asXMLPretty() {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->asXML());

        return $dom->saveXML();
    }
}
