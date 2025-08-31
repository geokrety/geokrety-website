<?php

namespace GeoKrety\Service;

use GeoKrety\Service\Redis as RedisService;
use PalePurple\RateLimit\Adapter\Redis;

class RedisAdapter extends \Prefab {
    private Redis $adapter;

    public function __construct() {
        $this->adapter = new Redis(RedisService::instance()->getRedis());
    }

    public function getAdapter(): Redis {
        return $this->adapter;
    }
}
