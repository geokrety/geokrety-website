<?php

namespace GeoKrety\Service\Xml;

class Errors extends Base {
    protected $xmlErrors;

    public function __construct($msg = null) {
        parent::__construct();
        $this->xmlErrors = $this->xml->addChild('errors');

        if (!is_null($msg)) {
            $this->xmlErrors->addChild('error', $msg);
        }
    }

    public function outputAsXML(bool $gzip = false, $filename = 'out.xml.gz') {
        http_response_code(400);
        parent::outputAsXML();
    }

    public function addError($msg) {
        $this->xmlErrors->addChild('error', $msg);
    }

    public function insertSessionErrors() {
        if (empty($_SESSION['alert_msgs'])) {
            return;
        }

        $alerts = array_filter($_SESSION['alert_msgs'], function ($k) {
            return $k['level'] == 'danger';
        });

        foreach ($alerts as $msg) {
            $this->xmlErrors->addChild('error', $msg['message']);
        }
    }
}
