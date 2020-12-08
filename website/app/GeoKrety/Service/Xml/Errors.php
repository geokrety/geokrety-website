<?php

namespace GeoKrety\Service\Xml;

class Errors extends Base {
    protected $xmlErrors;

    public function __construct($msg = null) {
        parent::__construct();
        $this->xml->startElement('errors');

        if (!is_null($msg)) {
            $this->addError($msg);
        }
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

    public function insertSessionErrors() {
        if (empty($_SESSION['alert_msgs'])) {
            return;
        }

        $alerts = array_filter($_SESSION['alert_msgs'], function ($k) {
            return $k['level'] == 'danger';
        });

        foreach ($alerts as $msg) {
            $this->addError($msg['message']);
        }
    }

    /**
     * Create an xml response from an array of errors
     *
     * @param array|string $errors The errors to format
     */
    public static function buildError($errors) {
    //public static function buildError(array|string $errors) { // need php 8.0
        $errors = gettype($errors) === 'string' ? [$errors] : $errors;
        $xml = new \GeoKrety\Service\Xml\Errors();
        foreach ($errors as $err) {
            $xml->addError($err);
        }
        $xml->end();
        $xml->finish(); // may return raw gzipped data
    }
}
