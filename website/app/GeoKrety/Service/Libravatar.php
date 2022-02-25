<?php

namespace GeoKrety\Service;

require_once 'Services/Libravatar.php';

class Libravatar extends \Prefab {
    private \Services_Libravatar $libravatar;

    public static function getUrl($identifier, $options = []) {
        if (GK_DEVEL) {
            return GK_AVATAR_DEFAULT_URL;
        }
        try {
            return self::instance()->libravatar->getUrl($identifier, $options);
        } catch (\Exception $e) {
            return GK_AVATAR_DEFAULT_URL;
        }
    }

    public function __construct() {
        $sla = new \Services_Libravatar();
        $sla->setSize(100)
            ->setDefault('identicon')
            ->setHttps(true);

        $this->libravatar = $sla;
    }
}
