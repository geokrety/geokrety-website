<?php

namespace GeoKrety\Service;

require_once 'Services/Libravatar.php';

class Libravatar extends \Prefab {
    private $libravatar;

    public static function getUrl($identifier, $options = []) {
        return self::instance()->libravatar->getUrl($identifier, $options);
    }

    public function __construct() {
        $sla = new \Services_Libravatar();
        $sla->setSize(100)
            ->setDefault('identicon')
            ->setHttps(true);

        $this->libravatar = $sla;
    }
}
