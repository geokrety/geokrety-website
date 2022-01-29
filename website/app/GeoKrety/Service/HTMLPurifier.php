<?php

namespace GeoKrety\Service;

class HTMLPurifier extends \Prefab {
    private \HTMLPurifier $purifier;

    public static function getPurifier(): \HTMLPurifier {
        return HTMLPurifier::instance()->purifier;
    }

    public function __construct() {
        if (!file_exists(GK_HTMLPURIFIER_CACHE_DIR) && !mkdir(GK_HTMLPURIFIER_CACHE_DIR, 0750, true)) {
            exit(sprintf('Fail to create \'%s\' directory', GK_HTMLPURIFIER_CACHE_DIR));
        }

        $HTMLPurifierConfig_conf = \HTMLPurifier_Config::createDefault();
        $HTMLPurifierConfig_conf->set('Cache.SerializerPath', GK_HTMLPURIFIER_CACHE_DIR);
        $HTMLPurifierConfig_conf->set('HTML.Allowed', '');

        $HTMLPurifier = new \HTMLPurifier($HTMLPurifierConfig_conf);

        $this->purifier = $HTMLPurifier;
    }
}
