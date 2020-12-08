<?php

namespace GeoKrety\Service;

class HTMLPurifier extends \Prefab {
    private $purifier;

    public static function getPurifier() {
        return HTMLPurifier::instance()->purifier;
    }

    public function __construct() {
        if (!file_exists(GK_HTMLPURIFIER_CACHE_DIR) && !mkdir(GK_HTMLPURIFIER_CACHE_DIR, 0750, true)) {
            exit(sprintf('Fail to create \'%s\' directory', GK_HTMLPURIFIER_CACHE_DIR));
        }

        $HTMLPurifierconfig_conf = \HTMLPurifier_Config::createDefault();
        $HTMLPurifierconfig_conf->set('Cache.SerializerPath', GK_HTMLPURIFIER_CACHE_DIR);
        $HTMLPurifierconfig_conf->set('HTML.Allowed', '');
        $HTMLPurifier = new \HTMLPurifier($HTMLPurifierconfig_conf);

        $this->purifier = $HTMLPurifier;
    }
}
