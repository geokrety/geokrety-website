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
}
